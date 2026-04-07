<?php
namespace App\Repository\Terrain;

use App\Entity\Terrain\Rotation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RotationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rotation::class);
    }

    // ── ADMIN ──────────────────────────────────────────

    public function search(string $q): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.terrain', 't')
            ->join('r.plante', 'p')
            ->where('t.nomTerrain LIKE :q OR p.nomP LIKE :q')
            ->setParameter('q', '%' . $q . '%')
            ->orderBy('r.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countByStatus(int $status): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.status = :s')
            ->setParameter('s', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findAllWithRelations(): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.terrain', 't')
            ->join('r.plante', 'p')
            ->addSelect('t', 'p')
            ->orderBy('r.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // ── AGRICULTEUR (par CIN) ──────────────────────────

    public function findByUserCin($cin): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.terrain', 't')
            ->join('r.plante', 'p')
            ->addSelect('t', 'p')
            ->where('t.cin = :cin')
            ->setParameter('cin', $cin)
            ->orderBy('r.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function searchByUserCin(string $q, $cin): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.terrain', 't')
            ->join('r.plante', 'p')
            ->addSelect('t', 'p')
            ->where('t.cin = :cin')
            ->andWhere('t.nomTerrain LIKE :q OR p.nomP LIKE :q')
            ->setParameter('cin', $cin)
            ->setParameter('q', '%' . $q . '%')
            ->orderBy('r.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countByStatusAndCin(int $status, $cin): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->join('r.terrain', 't')
            ->where('r.status = :s')
            ->andWhere('t.cin = :cin')
            ->setParameter('s', $status)
            ->setParameter('cin', $cin)
            ->getQuery()
            ->getSingleScalarResult();
    }
}