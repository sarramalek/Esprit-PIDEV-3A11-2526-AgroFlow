<?php
// src/Repository/Materiels/MachineRepository.php

namespace App\Repository\Materiels;

use App\Entity\Materiels\Machine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Machine>
 */
class MachineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Machine::class);
    }

    // =========================================================================
    // Recherche filtrée pour l'index
    // =========================================================================
    /**
     * @param array<string, mixed> $filters
     * @return array<int, Machine>
     */
    public function search(array $filters = []): array
    {
        $cin = isset($filters['cin']) ? (int) $filters['cin'] : 0;

        // Sans CIN valide on ne retourne rien (sécurité)
        if ($cin <= 0) {
            return [];
        }

        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.agriculteur', 'u')
            ->addSelect('u')
            ->andWhere('u.cin = :cin')
            ->setParameter('cin', $cin);

        // Recherche textuelle (nom, marque, modèle, numéro de série)
        if (!empty($filters['search'])) {
            $search = '%' . mb_strtolower(trim($filters['search'])) . '%';
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('LOWER(m.nom)', ':search'),
                    $qb->expr()->like('LOWER(m.marque)', ':search'),
                    $qb->expr()->like('LOWER(m.modele)', ':search'),
                    $qb->expr()->like('LOWER(m.numeroSerie)', ':search')
                )
            )->setParameter('search', $search);
        }

        // Filtre par état
        if (!empty($filters['etat'])) {
            $qb->andWhere('m.etatM = :etat')
               ->setParameter('etat', $filters['etat']);
        }

        // Tri
        $allowedSortFields = ['nom', 'marque', 'modele', 'etatM', 'dateAchat', 'kilometrage', 'id'];
        $sortBy  = in_array($filters['sortBy'] ?? '', $allowedSortFields, true)
                     ? $filters['sortBy']
                     : 'dateAchat';
        $sortDir = strtoupper($filters['sortDir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

        $qb->orderBy('m.' . $sortBy, $sortDir);
        if ($sortBy !== 'id') {
            $qb->addOrderBy('m.id', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }

    // =========================================================================
    // Statistiques globales (admin)
    // =========================================================================
    /**
     * @return array{total:int, parEtat:array<string,int>, parMarque:array<string,int>, statsKm:mixed}
     */
    public function getStatistiques(): array
    {
        $total = (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $rawEtat = $this->createQueryBuilder('m')
            ->select('m.etatM AS etat, COUNT(m.id) AS nb')
            ->groupBy('m.etatM')
            ->orderBy('nb', 'DESC')
            ->getQuery()
            ->getArrayResult();

        $parEtat = [];
        foreach ($rawEtat as $row) {
            $parEtat[$row['etat']] = (int) $row['nb'];
        }

        $rawMarque = $this->createQueryBuilder('m')
            ->select('m.marque AS marque, COUNT(m.id) AS nb')
            ->groupBy('m.marque')
            ->orderBy('nb', 'DESC')
            ->getQuery()
            ->getArrayResult();

        $parMarque = [];
        foreach ($rawMarque as $row) {
            $parMarque[$row['marque']] = (int) $row['nb'];
        }

        // Statistiques kilométrage
        $statsKm = $this->createQueryBuilder('m')
            ->select(
                'AVG(m.kilometrage) AS avgKm',
                'MAX(m.kilometrage) AS maxKm',
                'MIN(m.kilometrage) AS minKm'
            )
            ->getQuery()
            ->getSingleResult();

        return compact('total', 'parEtat', 'parMarque', 'statsKm');
    }

    // =========================================================================
    // Machines d'un agriculteur par CIN
    // =========================================================================
    /**
     * @return array<int, Machine>
     */
    public function findByCin(int $cin): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.agriculteur', 'u')
            ->addSelect('u')
            ->andWhere('u.cin = :cin')
            ->setParameter('cin', $cin)
            ->orderBy('m.dateAchat', 'DESC')
            ->addOrderBy('m.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // =========================================================================
    // Machines dont la prochaine maintenance arrive bientôt
    // =========================================================================
    /**
     * @return array<int, Machine>
     */
    public function findMachinesWithMaintenanceSoon(\DateTimeInterface $dateLimit): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.agriculteur', 'u')
            ->addSelect('u')
            ->where('m.prochaineMaintenance IS NOT NULL')
            ->andWhere('m.prochaineMaintenance <= :dateLimit')
            ->setParameter('dateLimit', $dateLimit)
            ->orderBy('m.prochaineMaintenance', 'ASC')
            ->getQuery()
            ->getResult();
    }
}