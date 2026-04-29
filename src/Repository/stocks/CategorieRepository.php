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
     * 
     * @param mixed $user
     * @return array<int, Categorie>
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

    /**
     * Trouve les catégories créées par des utilisateurs ayant un rôle spécifique
     * 
     * @return array<int, Categorie>
     */
    public function findByUserRole(int $role): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.agriculteur', 'u')
            ->where('u.role = :role')
            ->setParameter('role', $role)
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param mixed $admin
     * @return array<int, Categorie>
     */
    public function findByAdminCin($admin): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.agriculteur = :user')
            ->setParameter('user', $admin)
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
