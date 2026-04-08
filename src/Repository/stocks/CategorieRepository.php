<?php

namespace App\Repository\stocks;

use App\Entity\stocks\Categorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Categorie>
 */
class CategorieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Categorie::class);
    }

    /**
     * Récupère les catégories d'un agriculteur spécifique
     */
    public function findByAgriculteur($user)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.agriculteur = :user')
            ->setParameter('user', $user)
            ->orderBy('c.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
