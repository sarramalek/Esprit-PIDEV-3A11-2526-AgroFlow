<?php

namespace App\Controller\stocks;

use App\Entity\stocks\Article;
use App\Entity\stocks\Categorie;
use App\Entity\stocks\MouvementStock;
use App\Repository\stocks\MouvementStockRepository;
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
        $repo = $em->getRepository(MouvementStock::class);

        $articleId = $request->query->get('article');
        $dateDebut = $request->query->get('date_debut');
        $dateFin = $request->query->get('date_fin');

        $queryBuilder = $repo->createQueryBuilder('m')
            ->leftJoin('m.article', 'a')
            ->addSelect('a')
            ->orderBy('m.dateMouvement', 'DESC');

        if ($articleId) {
            $queryBuilder->andWhere('a.id = :articleId')
                ->setParameter('articleId', $articleId);
        }

        if ($dateDebut) {
            try {
                $queryBuilder->andWhere('m.dateMouvement >= :debut')
                    ->setParameter('debut', new \DateTime($dateDebut . ' 00:00:00'));
            } catch (\Exception $e) {
            }
        }

        if ($dateFin) {
            try {
                $queryBuilder->andWhere('m.dateMouvement <= :fin')
                    ->setParameter('fin', new \DateTime($dateFin . ' 23:59:59'));
            } catch (\Exception $e) {
            }
        }

        return $this->render('stocks/article/mouvement/index.html.twig', [
            'mouvements'     => $queryBuilder->getQuery()->getResult(),
            'articles'       => $em->getRepository(Article::class)->findAll(),
            'currentArticle' => $articleId,
            'currentDebut'   => $dateDebut,
            'currentFin'     => $dateFin,
        ]);
    }

    /**
     * NOUVELLE MÉTHODE : Rapport de Rotation par Catégorie
     */
    #[Route('/agriculteur/mouvements/rotation', name: 'app_mouvement_rotation')]
    public function rapportRotation(Request $request, EntityManagerInterface $em): Response
    {
        // 1. Récupération des dates (Défaut : mois en cours)
        $dateDebutStr = $request->query->get('date_debut', (new \DateTime('first day of this month'))->format('Y-m-d'));
        $dateFinStr = $request->query->get('date_fin', (new \DateTime('last day of this month'))->format('Y-m-d'));

        $dateDebut = new \DateTime($dateDebutStr . ' 00:00:00');
        $dateFin = new \DateTime($dateFinStr . ' 23:59:59');

        $categories = $em->getRepository(Categorie::class)->findAll();
        $statsData = [];

        foreach ($categories as $cat) {
            // Calcul de la somme des SORTIES pour cette catégorie sur la période
            $totalSorties = $em->getRepository(MouvementStock::class)->createQueryBuilder('m')
                ->select('SUM(m.quantite)')
                ->join('m.article', 'a')
                ->where('a.categorie = :cat')
                ->andWhere('m.type = :type')
                ->andWhere('m.dateMouvement BETWEEN :debut AND :fin')
                ->setParameter('cat', $cat)
                ->setParameter('type', 'SORTIE')
                ->setParameter('debut', $dateDebut)
                ->setParameter('fin', $dateFin)
                ->getQuery()
                ->getSingleScalarResult() ?: 0;

            // Calcul du stock actuel de la catégorie
            $stockActuel = 0;
            foreach ($cat->getArticles() as $article) {
                $stockActuel += $article->getQuantiteEnStock();
            }

            // Calcul de l'indice (Sorties / Stock Actuel)
            $indice = ($stockActuel > 0) ? ($totalSorties / $stockActuel) : ($totalSorties > 0 ? 1 : 0);

            $statsData[] = [
                'categorie' => $cat,
                'sorties'   => $totalSorties,
                'stock'     => $stockActuel,
                'indice'    => round($indice, 2)
            ];
        }

        return $this->render('stocks/article/mouvement/rotation.html.twig', [
            'stats' => $statsData,
            'currentDebut' => $dateDebutStr,
            'currentFin' => $dateFinStr,
        ]);
    }

    #[Route('/agriculteur/mouvements/export', name: 'app_mouvement_export_pdf')]
    public function exportPdf(Request $request, EntityManagerInterface $em): Response
    {
        $repo = $em->getRepository(MouvementStock::class);
        $articleId = $request->query->get('article');
        $dateDebut = $request->query->get('date_debut');
        $dateFin = $request->query->get('date_fin');

        $queryBuilder = $repo->createQueryBuilder('m')
            ->leftJoin('m.article', 'a')
            ->addSelect('a')
            ->orderBy('m.dateMouvement', 'DESC');

        if ($articleId) {
            $queryBuilder->andWhere('a.id = :articleId')->setParameter('articleId', $articleId);
        }

        if ($dateDebut) {
            try {
                $queryBuilder->andWhere('m.dateMouvement >= :debut')->setParameter('debut', new \DateTime($dateDebut . ' 00:00:00'));
            } catch (\Exception $e) {
            }
        }

        if ($dateFin) {
            try {
                $queryBuilder->andWhere('m.dateMouvement <= :fin')->setParameter('fin', new \DateTime($dateFin . ' 23:59:59'));
            } catch (\Exception $e) {
            }
        }

        $mouvements = $queryBuilder->getQuery()->getResult();

        $html = $this->renderView('stocks/article/mouvement/pdf_export.html.twig', [
            'mouvements' => $mouvements,
            'dateExport' => new \DateTime(),
        ]);

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="export_mouvements_agroflow.pdf"'
        ]);
    }
}
