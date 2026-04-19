<?php

namespace App\Controller\stocks;

use App\Entity\stocks\Article;
use App\Entity\stocks\MouvementStock;
use App\Repository\stocks\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class MouvementController extends AbstractController
{
    #[Route('/agriculteur/mouvements', name: 'app_mouvement_index')]
    public function historique(Request $request, EntityManagerInterface $em): Response
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
            $qb->andWhere('a.id = :artId') // On utilise l'alias 'a' du join
                ->setParameter('artId', (int)$articleId);
        }
        if ($dateDebut) {
            $qb->andWhere('m.dateMouvement >= :debut')->setParameter('debut', new \DateTime($dateDebut . ' 00:00:00'));
        }
        if ($dateFin) {
            $qb->andWhere('m.dateMouvement <= :fin')->setParameter('fin', new \DateTime($dateFin . ' 23:59:59'));
        }

        $qb->orderBy('m.dateMouvement', 'DESC');

        return $this->render('stocks/article/mouvement/index.html.twig', [
            'mouvements'     => $qb->getQuery()->getResult(),
            'articles'       => $em->getRepository(Article::class)->findBy(['user' => $user]),
            'currentArticle' => $articleId,
            'currentDebut'   => $dateDebut,
            'currentFin'     => $dateFin
        ]);
    }

    #[Route('/agriculteur/mouvements/new/{id}', name: 'app_mouvement_new_alias', methods: ['GET', 'POST'])]
    public function gestionStockAlias(Article $article, Request $request, EntityManagerInterface $em): Response
    {
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
            $this->addFlash('danger', 'La quantit� doit �tre sup�rieure � 0.');
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
            $article->setQuantiteEnStock($stockActuel - $quantite);
        }

        $mouvement->setType($type);
        $mouvement->setQuantite($quantite);
        $mouvement->setMotif($motif);

        $em->persist($mouvement);
        $em->flush();

        $this->addFlash('success', 'Mouvement enregistr� avec succ�s.');
        return $this->redirectToRoute('agri_produits');
    }
    #[Route('/agriculteur/mouvements/rotation', name: 'app_mouvement_rotation')]
    public function rotation(Request $request, CategorieRepository $catRepo, EntityManagerInterface $em, ChartBuilderInterface $chartBuilder): Response
    {
        $user = $this->getUser();

        // Récupération des dates (on force le format pour éviter les erreurs de calcul)
        $dateDebutStr = $request->query->get('debut', (new \DateTime('-30 days'))->format('Y-m-d'));
        $dateFinStr = $request->query->get('fin', (new \DateTime())->format('Y-m-d'));

        $dateDebut = new \DateTime($dateDebutStr . ' 00:00:00');
        $dateFin = new \DateTime($dateFinStr . ' 23:59:59');

        $categories = $catRepo->findBy(['agriculteur' => $user]);
        $stats = [];

        foreach ($categories as $cat) {
            // On récupère TOUS les mouvements de la catégorie d'un coup pour gagner en performance
            $mouvements = $em->getRepository(MouvementStock::class)->createQueryBuilder('m')
                ->select('m.type, SUM(m.quantite) as total')
                ->join('m.article', 'art')
                ->where('art.categorie = :cat')
                ->andWhere('art.user = :user')
                ->andWhere('m.dateMouvement BETWEEN :debut AND :fin')
                ->setParameter('cat', $cat)
                ->setParameter('user', $user)
                ->setParameter('debut', $dateDebut)
                ->setParameter('fin', $dateFin)
                ->groupBy('m.type')
                ->getQuery()
                ->getResult();

            $totalEntrees = 0;
            $totalSorties = 0;

            foreach ($mouvements as $mov) {
                // On compare en ignorant la casse et les espaces
                $type = trim(strtoupper($mov['type']));
                if ($type === 'ENTREE') {
                    $totalEntrees = (float)$mov['total'];
                } elseif ($type === 'SORTIE') {
                    $totalSorties = (float)$mov['total'];
                }
            }

            // Calcul du stock actuel de la catégorie
            $totalStock = 0;
            foreach ($cat->getArticles() as $article) {
                $totalStock += $article->getQuantiteEnStock();
            }

            $stats[] = [
                'categorie' => $cat,
                'entrees'   => $totalEntrees,
                'sorties'   => $totalSorties,
                'indice'    => ($totalStock > 0) ? round($totalSorties / $totalStock, 2) : 0
            ];
        }

        $labels = array_map(fn(array $stat) => $stat['categorie']->getNom(), $stats);
        $entreesData = array_map(fn(array $stat) => $stat['entrees'], $stats);
        $sortiesData = array_map(fn(array $stat) => $stat['sorties'], $stats);

        $rotationChart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $rotationChart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Entrées',
                    'data' => $entreesData,
                    'backgroundColor' => '#A8D5BA',
                    'borderRadius' => 6,
                    'barThickness' => 20,
                ],
                [
                    'label' => 'Sorties',
                    'data' => $sortiesData,
                    'backgroundColor' => '#2D5A27',
                    'borderRadius' => 6,
                    'barThickness' => 20,
                ],
            ],
        ]);
        $rotationChart->setOptions([
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => [
                    'grid' => ['display' => false],
                    'beginAtZero' => true,
                ],
                'x' => [
                    'grid' => ['display' => false],
                    'ticks' => ['font' => ['size' => 10, 'weight' => '700']],
                ],
            ],
        ]);
        $rotationChart->setAttributes(['style' => 'max-width:100%; height:300px;']);

        return $this->render('stocks/article/mouvement/rotation.html.twig', [
            'stats' => $stats,
            'dateDebut' => $dateDebutStr,
            'dateFin' => $dateFinStr,
            'chart' => $rotationChart,
        ]);
    }

    #[Route('/agriculteur/mouvements/export', name: 'app_mouvement_export_pdf')]
    public function exportPdf(EntityManagerInterface $em): Response
    {
        $mouvements = $em->getRepository(MouvementStock::class)->createQueryBuilder('m')
            ->join('m.article', 'a')
            ->where('a.user = :user')
            ->setParameter('user', $this->getUser())
            ->orderBy('m.dateMouvement', 'DESC')
            ->getQuery()
            ->getResult();
        $html = $this->renderView('stocks/article/mouvement/pdf_export.html.twig', ['mouvements' => $mouvements]);

        $dompdf = new Dompdf(new Options(['defaultFont' => 'Arial']));
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="mes_mouvements.pdf"'
        ]);
    }
}
