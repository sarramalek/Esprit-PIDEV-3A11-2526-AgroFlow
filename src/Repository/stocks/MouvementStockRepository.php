<?php

namespace App\Repository\stocks;

use App\Entity\stocks\MouvementStock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MouvementStock>
 */
class MouvementStockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MouvementStock::class);
    }

    public function findByAdminSearch(?string $search, ?int $idAdmin = null): array
    {
        $qb = $this->createQueryBuilder('m')
            ->join('m.article', 'a')
            ->leftJoin('a.user', 'u');

        if ($idAdmin) {
            $qb->andWhere('m.idAdmin = :idAdmin')
               ->setParameter('idAdmin', $idAdmin);
        }

        if ($search) {
            $qb->andWhere('a.nom LIKE :search OR u.nom LIKE :search OR u.prenom LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        return $qb->orderBy('m.dateMouvement', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
