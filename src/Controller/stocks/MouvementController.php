<?php

namespace App\Controller\stocks;

use App\Entity\stocks\Article;
use App\Entity\stocks\MouvementStock;
use App\Repository\stocks\CategorieRepository;
use App\Service\EmailService; // Import du service d'email
use App\Service\TelegramService; // Import du service Telegram
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use Knp\Component\Pager\PaginatorInterface;

class MouvementController extends AbstractController
{
    #[Route('/agriculteur/mouvements', name: 'app_mouvement_index')]
    public function historique(
        Request $request,
        EntityManagerInterface $em,
        PaginatorInterface $paginator
    ): Response {
        $user = $this->getUser();
        $articleId = $request->query->get('article');
        $dateDebut = $request->query->get('date_debut');
        $dateFin = $request->query->get('date_fin');

        $repo = $em->getRepository(MouvementStock::class);
        $qb = $repo->createQueryBuilder('m')
            ->join('m.article', 'a')
            ->where('a.user = :user')
            ->setParameter('user', $user);

        if (!empty($articleId)) {
            $qb->andWhere('a.id = :artId')
                ->setParameter('artId', (int)$articleId);
        }

        try {
            if ($dateDebut) {
                $qb->andWhere('m.dateMouvement >= :debut')
                    ->setParameter('debut', new \DateTime($dateDebut . ' 00:00:00'));
            }
            if ($dateFin) {
                $qb->andWhere('m.dateMouvement <= :fin')
                    ->setParameter('fin', new \DateTime($dateFin . ' 23:59:59'));
            }
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Format de date invalide.');
        }

        $qb->orderBy('m.dateMouvement', 'DESC');
        $query = $qb->getQuery();

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('stocks/article/mouvement/index.html.twig', [
            'mouvements'     => $pagination,
            'articles'       => $em->getRepository(Article::class)->findBy(['user' => $user]),
            'currentArticle' => $articleId,
            'currentDebut'   => $dateDebut,
            'currentFin'     => $dateFin
        ]);
    }

    #[Route('/agriculteur/mouvements/new/{id}', name: 'app_mouvement_new_alias', methods: ['GET', 'POST'])]
    public function gestionStockAlias(
        Article $article,
        Request $request,
        EntityManagerInterface $em,
        EmailService $emailService, // Injection du service d'email
        TelegramService $telegramService // Injection du service Telegram
    ): Response {
        if ($article->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $mouvement = new MouvementStock();
        $mouvement->setArticle($article);
        $mouvement->setDateMouvement(new \DateTimeImmutable());
        $mouvement->setUser($this->getUser());

        $type = $request->request->get('type');
        $quantite = floatval($request->request->get('quantite'));
        $motif = $request->request->get('motif');

        if ($quantite <= 0) {
            $this->addFlash('danger', 'La quantité doit être supérieure à 0.');
            return $this->redirectToRoute('agri_produits');
        }

        $stockActuel = $article->getQuantiteEnStock();

        if ($type === 'ENTREE') {
            $article->setQuantiteEnStock($stockActuel + $quantite);
        } elseif ($type === 'SORTIE') {
            if ($stockActuel < $quantite) {
                $this->addFlash('danger', 'Stock insuffisant pour ' . $article->getNom());
                return $this->redirectToRoute('agri_produits');
            }

            $nouveauStock = $stockActuel - $quantite;
            $article->setQuantiteEnStock($nouveauStock);

            if ($nouveauStock <= $article->getSeuilAlerte()) {
                $mailOk = $emailService->envoyerMailAlerte($article);
                if (!$mailOk) {
                    $this->addFlash('warning', 'Alerte créée, mais l\'envoi de l\'email a échoué.');
                }

                // Notification Telegram si configuré
                $telegramChatId = ($article->getUser() ? $article->getUser()->getTelegramChatId() : null) ?? $_ENV['TELEGRAM_CHAT_ID'] ?? null;
                if ($telegramChatId) {
                    $message = sprintf(
                        "Alerte de stock critique !\n\nArticle: %s\nStock actuel: %d %s\nSeuil d'alerte: %d %s",
                        $article->getNom(),
                        $nouveauStock,
                        $article->getUniteMesure() ?? 'unités',
                        $article->getSeuilAlerte(),
                        $article->getUniteMesure() ?? 'unités'
                    );
                    $telegramService->notifier($message, $telegramChatId);
                }
            }
        }

        $mouvement->setType($type);
        $mouvement->setQuantite($quantite);
        $mouvement->setMotif($motif);

        $em->persist($mouvement);
        $em->flush();

        $this->addFlash('success', 'Mouvement enregistré avec succès.');
        return $this->redirectToRoute('agri_produits');
    }

    #[Route('/agriculteur/mouvements/rotation', name: 'app_mouvement_rotation')]
    public function rotation(Request $request, CategorieRepository $catRepo, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $categories = $catRepo->findBy(['agriculteur' => $user]);

        $rotationData = [];
        foreach ($categories as $categorie) {
            $totalEntrees = $em->getRepository(MouvementStock::class)
                ->createQueryBuilder('m')
                ->join('m.article', 'a')
                ->where('a.categorie = :categorie')
                ->andWhere('a.user = :user')
                ->andWhere('m.type = :type')
                ->setParameter('categorie', $categorie)
                ->setParameter('user', $user)
                ->setParameter('type', 'ENTREE')
                ->select('SUM(m.quantite)')
                ->getQuery()
                ->getSingleScalarResult() ?? 0;

            $totalSorties = $em->getRepository(MouvementStock::class)
                ->createQueryBuilder('m')
                ->join('m.article', 'a')
                ->where('a.categorie = :categorie')
                ->andWhere('a.user = :user')
                ->andWhere('m.type = :type')
                ->setParameter('categorie', $categorie)
                ->setParameter('user', $user)
                ->setParameter('type', 'SORTIE')
                ->select('SUM(m.quantite)')
                ->getQuery()
                ->getSingleScalarResult() ?? 0;

            $rotationData[$categorie->getNom()] = [
                'entrees' => $totalEntrees,
                'sorties' => $totalSorties,
                'rotation' => $totalSorties > 0 ? round(($totalEntrees / $totalSorties) * 100, 2) : 0
            ];
        }

        return $this->render('stocks/article/mouvement/rotation.html.twig', [
            'rotationData' => $rotationData
        ]);
    }

    #[Route('/agriculteur/mouvements/pdf', name: 'app_mouvement_pdf')]
    public function exportPdf(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $articleId = $request->query->get('article');
        $dateDebut = $request->query->get('date_debut');
        $dateFin = $request->query->get('date_fin');

        $repo = $em->getRepository(MouvementStock::class);
        $qb = $repo->createQueryBuilder('m')
            ->join('m.article', 'a')
            ->where('a.user = :user')
            ->setParameter('user', $user);

        if (!empty($articleId)) {
            $qb->andWhere('a.id = :artId')
                ->setParameter('artId', (int)$articleId);
        }

        if ($dateDebut) {
            $qb->andWhere('m.dateMouvement >= :debut')
                ->setParameter('debut', new \DateTime($dateDebut . ' 00:00:00'));
        }
        if ($dateFin) {
            $qb->andWhere('m.dateMouvement <= :fin')
                ->setParameter('fin', new \DateTime($dateFin . ' 23:59:59'));
        }

        $qb->orderBy('m.dateMouvement', 'DESC');
        $mouvements = $qb->getQuery()->getResult();

        // Configuration Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);

        $html = $this->renderView('stocks/article/mouvement/pdf_export.html.twig', [
            'mouvements' => $mouvements,
            'user' => $user,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="mouvements_' . date('Y-m-d') . '.pdf"'
        ]);
    }
}
