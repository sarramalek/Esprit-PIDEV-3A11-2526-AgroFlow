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
    /**
     * Rapport de Rotation par Catégorie
     */
    #[Route('/agriculteur/mouvements/rotation', name: 'app_mouvement_rotation')]
    public function rotation(Request $request, CategorieRepository $catRepo, EntityManagerInterface $em): Response
    {
        // 1. Gestion des dates
        $dateDebutStr = $request->query->get('debut', (new \DateTime('-30 days'))->format('Y-m-d'));
        $dateFinStr = $request->query->get('fin', (new \DateTime())->format('Y-m-d'));

        $dateDebut = new \DateTime($dateDebutStr);
        $dateFin = new \DateTime($dateFinStr);

        $categories = $catRepo->findAll();
        $stats = [];

        foreach ($categories as $cat) {
            // 2. Calcul des SORTIES sur la période
            $totalSorties = $em->getRepository(MouvementStock::class)->createQueryBuilder('m')
                ->select('SUM(m.quantite)')
                ->join('m.article', 'art')
                ->where('art.categorie = :cat')
                ->andWhere('m.type = :type')
                ->andWhere('m.dateMouvement BETWEEN :debut AND :fin')
                ->setParameter('cat', $cat)
                ->setParameter('type', 'SORTIE')
                ->setParameter('debut', $dateDebut)
                ->setParameter('fin', $dateFin)
                ->getQuery()
                ->getSingleScalarResult() ?: 0;

            // 3. Calcul des ENTRÉES sur la période (AJOUTÉ ICI)
            $totalEntrees = $em->getRepository(MouvementStock::class)->createQueryBuilder('m')
                ->select('SUM(m.quantite)')
                ->join('m.article', 'art')
                ->where('art.categorie = :cat')
                ->andWhere('m.type = :type')
                ->andWhere('m.dateMouvement BETWEEN :debut AND :fin')
                ->setParameter('cat', $cat)
                ->setParameter('type', 'ENTREE') // On filtre sur les entrées
                ->setParameter('debut', $dateDebut)
                ->setParameter('fin', $dateFin)
                ->getQuery()
                ->getSingleScalarResult() ?: 0;

            // 4. Calcul du stock actuel pour l'indice
            $totalStock = 0;
            foreach ($cat->getArticles() as $article) {
                $totalStock += $article->getQuantiteEnStock();
            }

            $stats[] = [
                'categorie' => $cat,
                'entrees'   => (float)$totalEntrees, // Clé maintenant disponible pour Twig
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
    /**
     * Historique avec Filtrage Dynamique
     */
    #[Route('/agriculteur/mouvements', name: 'app_mouvement_index')]
    public function historique(Request $request, EntityManagerInterface $em): Response
    {
        // 1. Récupération des filtres depuis l'URL
        $articleId = $request->query->get('article');
        $dateDebut = $request->query->get('date_debut');
        $dateFin = $request->query->get('date_fin');

        $repo = $em->getRepository(MouvementStock::class);
        $qb = $repo->createQueryBuilder('m')
            ->leftJoin('m.article', 'a')
            ->addSelect('a');

        // 2. Application des filtres SI ils sont remplis
        if ($articleId) {
            $qb->andWhere('a.id = :artId')->setParameter('artId', $articleId);
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
            'articles'       => $em->getRepository(Article::class)->findAll(),
            'currentArticle' => $articleId, // Correction de l'erreur
            'currentDebut'   => $dateDebut,   // Correction de l'erreur
            'currentFin'     => $dateFin      // Correction de l'erreur
        ]);
    }

    /**
     * Export PDF
     */
    #[Route('/agriculteur/mouvements/export', name: 'app_mouvement_export_pdf')]
    public function exportPdf(Request $request, EntityManagerInterface $em): Response
    {
        $mouvements = $em->getRepository(MouvementStock::class)->findAll();
        $html = $this->renderView('stocks/article/mouvement/pdf_export.html.twig', ['mouvements' => $mouvements]);

        $dompdf = new Dompdf(new Options(['defaultFont' => 'Arial']));
        $dompdf->loadHtml($html);
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="export_mouvements.pdf"'
        ]);
    }
}
