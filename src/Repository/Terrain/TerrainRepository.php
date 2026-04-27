<?php
namespace App\Repository\Terrain;

use App\Entity\Terrain\Terrain;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Terrain>
 */
class TerrainRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Terrain::class);
    }

    /**
     * @return array<int, mixed>
     */
    public function findWithStats(): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.rotations', 'r')
            ->addSelect('COUNT(r.id) as rotationCount')
            ->groupBy('t.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<int, Terrain>
     */
     public function findByAgriculteur(int $cinAgriculteur): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.cin = :cin')
            ->setParameter('cin', $cinAgriculteur)
            ->orderBy('t.nomTerrain', 'ASC')
            ->getQuery()
            ->getResult();
    }
 
    /**
     * Terrains d'un agriculteur avec leurs ouvriers chargés en une seule requête.
     *
     * @return Terrain[]
     */
    public function findByAgriculteurWithOuvriers(int $cinAgriculteur): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.ouvriers', 'o')
            ->addSelect('o')
            ->where('t.cin = :cin')
            ->setParameter('cin', $cinAgriculteur)
            ->orderBy('t.nomTerrain', 'ASC')
            ->getQuery()
            ->getResult();
    }
 // src/Repository/Terrain/TerrainRepository.php

/**
 * Retourne tous les ouvriers (User) appartenant aux terrains de l'agriculteur
 * @return array<int, \App\Entity\User\User>
 */
public function findOuvriersDeAgriculteur(int $cinAgriculteur): array
{
    return $this->getEntityManager()
        ->getRepository(\App\Entity\User\User::class)
        ->createQueryBuilder('u')
        ->join('u.terrain', 't')
        ->andWhere('t.cin = :cin')
        ->andWhere('u.role = 1')
        ->setParameter('cin', $cinAgriculteur)
        ->getQuery()
        ->getResult();
}
    /**
     * Retourne les CINs de tous les ouvriers des terrains d'un agriculteur.
     * Utilisé pour vérifier qu'un ouvrier "appartient" à cet agriculteur,
     * même s'il n'a pas encore de terrain assigné.
     *
     * @return int[]
     */
    public function findCinsOuvriersAgriculteur(int $cinAgriculteur): array
    {
        $rows = $this->createQueryBuilder('t')
            ->select('o.cin')
            ->join('t.ouvriers', 'o')
            ->where('t.cin = :cin')
            ->setParameter('cin', $cinAgriculteur)
            ->getQuery()
            ->getScalarResult();
 
        return array_column($rows, 'cin');
    }
}