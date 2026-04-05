<?php

namespace App\Controller\stocks;

use App\Entity\stocks\Article;
use App\Entity\stocks\MouvementStock;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MouvementController extends AbstractController
{
    #[Route('/agriculteur/mouvements', name: 'app_mouvement_index')]
    public function historique(Request $request, EntityManagerInterface $em): Response
    {
        $repo = $em->getRepository(MouvementStock::class);

        // 1. Récupération des filtres
        $articleId = $request->query->get('article');
        $dateDebut = $request->query->get('date_debut');
        $dateFin = $request->query->get('date_fin');

        // 2. Construction de la requête QueryBuilder
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

        // 3. Rendu (CORRIGÉ selon ton arborescence de dossiers)
        return $this->render('stocks/article/mouvement/index.html.twig', [
            'mouvements'     => $queryBuilder->getQuery()->getResult(),
            'articles'       => $em->getRepository(Article::class)->findAll(),
            'currentArticle' => $articleId,
            'currentDebut'   => $dateDebut,
            'currentFin'     => $dateFin,
        ]);
    }
}
