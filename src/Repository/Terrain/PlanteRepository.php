<?php
namespace App\Repository\Terrain;

use App\Entity\Terrain\Plante;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PlanteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Plante::class);
    }

    public function search(string $q): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.nomP LIKE :q OR p.variete LIKE :q')
            ->setParameter('q', '%' . $q . '%')
            ->orderBy('p.nomP', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function avgBesoinEau(): ?float
    {
        $result = $this->createQueryBuilder('p')
            ->select('AVG(p.besoinEau)')
            ->where('p.besoinEau IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();
        return $result ? round($result, 2) : null;
    }

    public function avgCycleJours(): ?float
    {
        $result = $this->createQueryBuilder('p')
            ->select('AVG(p.cycleJours)')
            ->where('p.cycleJours IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();
        return $result ? round($result) : null;
    }
}