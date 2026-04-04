<?php

namespace App\Repository\User;

use App\Entity\User\Offre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class OffreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Offre::class);
    }

    // ==================== SEARCH AND SORT ====================
    public function searchAndSort(string $search, string $sort, string $direction): array
    {
        $qb = $this->createQueryBuilder('o');

        if ($search) {
            $qb->where(
                $qb->expr()->orX(
                    $qb->expr()->like('o.nomOffre', ':q'),
                    $qb->expr()->like('o.description', ':q')
                )
            )->setParameter('q', '%' . $search . '%');
        }

        $qb->orderBy('o.' . $sort, $direction);

        return $qb->getQuery()->getResult();
    }

    // ==================== FIND BY PRIX RANGE ====================
    public function findByPrixRange(float $min, float $max): array
    {
        return $this->createQueryBuilder('o')
            ->where('o.prix BETWEEN :min AND :max')
            ->setParameter('min', $min)
            ->setParameter('max', $max)
            ->orderBy('o.prix', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // ==================== FIND MOINS CHER ====================
    public function findMoinsCher(int $limit = 3): array
    {
        return $this->createQueryBuilder('o')
            ->orderBy('o.prix', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    // ==================== FIND PLUS LONG ====================
    public function findPlusLong(int $limit = 3): array
    {
        return $this->createQueryBuilder('o')
            ->orderBy('o.dureeOffre', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    // ==================== COUNT ====================
    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('o')
            ->select('COUNT(o.idOffres)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    // ==================== AVG PRIX ====================
    public function avgPrix(): float
    {
        return (float) $this->createQueryBuilder('o')
            ->select('AVG(o.prix)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    // ==================== SAVE ====================
    public function save(Offre $offre, bool $flush = false): void
    {
        $this->getEntityManager()->persist($offre);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    // ==================== REMOVE ====================
    public function remove(Offre $offre, bool $flush = false): void
    {
        $this->getEntityManager()->remove($offre);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}