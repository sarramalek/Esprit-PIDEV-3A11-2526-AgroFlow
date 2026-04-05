<?php
namespace App\Repository\Terrain;

use App\Entity\Terrain\Terrain;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TerrainRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Terrain::class);
    }

    public function findWithStats(): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.rotations', 'r')
            ->addSelect('COUNT(r.id) as rotationCount')
            ->groupBy('t.id')
            ->getQuery()
            ->getResult();
    }
}