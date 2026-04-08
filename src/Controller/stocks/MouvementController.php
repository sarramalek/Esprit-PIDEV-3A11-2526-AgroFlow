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
            ->where('m.user = :user')
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

    #[Route('/agriculteur/mouvements/rotation', name: 'app_mouvement_rotation')]
    public function rotation(Request $request, CategorieRepository $catRepo, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $dateDebutStr = $request->query->get('debut', (new \DateTime('-30 days'))->format('Y-m-d'));
        $dateFinStr = $request->query->get('fin', (new \DateTime())->format('Y-m-d'));

        $dateDebut = new \DateTime($dateDebutStr);
        $dateFin = new \DateTime($dateFinStr);

        $categories = $catRepo->findBy(['agriculteur' => $user]);
        $stats = [];

        foreach ($categories as $cat) {
            $totalSorties = $em->getRepository(MouvementStock::class)->createQueryBuilder('m')
                ->select('SUM(m.quantite)')
                ->join('m.article', 'art')
                ->where('art.categorie = :cat')
                ->andWhere('m.type = :type')
                ->andWhere('m.user = :user')
                ->andWhere('m.dateMouvement BETWEEN :debut AND :fin')
                ->setParameter('cat', $cat)
                ->setParameter('type', 'SORTIE')
                ->setParameter('user', $user)
                ->setParameter('debut', $dateDebut)
                ->setParameter('fin', $dateFin)
                ->getQuery()
                ->getSingleScalarResult() ?: 0;

            $totalEntrees = $em->getRepository(MouvementStock::class)->createQueryBuilder('m')
                ->select('SUM(m.quantite)')
                ->join('m.article', 'art')
                ->where('art.categorie = :cat')
                ->andWhere('m.type = :type')
                ->andWhere('m.user = :user')
                ->andWhere('m.dateMouvement BETWEEN :debut AND :fin')
                ->setParameter('cat', $cat)
                ->setParameter('type', 'ENTREE')
                ->setParameter('user', $user)
                ->setParameter('debut', $dateDebut)
                ->setParameter('fin', $dateFin)
                ->getQuery()
                ->getSingleScalarResult() ?: 0;

            $totalStock = 0;
            foreach ($cat->getArticles() as $article) {
                $totalStock += $article->getQuantiteEnStock();
            }

            $stats[] = [
                'categorie' => $cat,
                'entrees'   => (float)$totalEntrees,
                'sorties'   => (float)$totalSorties,
                'indice'    => ($totalStock > 0) ? round($totalSorties / $totalStock, 2) : 0
            ];
        }

        return $this->render('stocks/article/mouvement/rotation.html.twig', [
            'stats' => $stats,
            'dateDebut' => $dateDebutStr,
            'dateFin' => $dateFinStr
        ]);
    }

    #[Route('/agriculteur/mouvements/export', name: 'app_mouvement_export_pdf')]
    public function exportPdf(EntityManagerInterface $em): Response
    {
        $mouvements = $em->getRepository(MouvementStock::class)->findBy(['user' => $this->getUser()]);
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
