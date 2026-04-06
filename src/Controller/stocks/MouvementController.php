<?php

namespace App\Controller\stocks;

use App\Entity\stocks\Article;
use App\Entity\stocks\MouvementStock;
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

    #[Route('/agriculteur/mouvements/export', name: 'app_mouvement_export_pdf')]
    public function exportPdf(Request $request, EntityManagerInterface $em): Response
    {
        $repo = $em->getRepository(MouvementStock::class);

        // 1. Récupération des filtres depuis la requête
        $articleId = $request->query->get('article');
        $dateDebut = $request->query->get('date_debut');
        $dateFin = $request->query->get('date_fin');

        // 2. Construction de la requête filtrée
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

        $mouvements = $queryBuilder->getQuery()->getResult();

        // 3. Génération du HTML
        $html = $this->renderView('stocks/article/mouvement/pdf_export.html.twig', [
            'mouvements' => $mouvements,
            'dateExport' => new \DateTime(),
        ]);

        // 4. Configuration Dompdf
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
