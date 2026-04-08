<?php

namespace App\Repository\stocks;

use App\Entity\stocks\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function findBySearchCriteria(?string $search, ?string $categoryId, $user): array
    {
        $qb = $this->createQueryBuilder('a');

        // Filtrer par l'utilisateur connecté (sécurité)
        $qb->andWhere('a.user = :user')
            ->setParameter('user', $user);

        if ($search) {
            $qb->andWhere('a.nom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($categoryId && $categoryId !== '') {
            // Utiliser 'a.categorie' directement pour que Doctrine gère 
            // lui-même la correspondance avec 'id_categorie'
            $qb->andWhere('a.categorie = :catId')
                ->setParameter('catId', $categoryId);
        }

        $qb->orderBy('a.nom', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
