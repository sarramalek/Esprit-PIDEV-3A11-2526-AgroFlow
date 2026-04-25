<?php

namespace App\Repository\Materiels;

use App\Entity\Materiels\Machine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MachineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Machine::class);
    }

    // =========================================================================
    // Recherche filtrée — retourne un array (sans paginator)
    // =========================================================================
    public function search(array $filters = []): array
    {
        $cin = isset($filters['cin']) ? (int) $filters['cin'] : 0;

        if ($cin <= 0) {
            return [];
        }

        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.agriculteur', 'u')
            ->addSelect('u')
            ->andWhere('u.cin = :cin')
            ->setParameter('cin', $cin);

        // Recherche textuelle
        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $like = '%' . mb_strtolower($search) . '%';
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('LOWER(m.nom)',         ':search'),
                    $qb->expr()->like('LOWER(m.marque)',      ':search'),
                    $qb->expr()->like('LOWER(m.modele)',      ':search'),
                    $qb->expr()->like('LOWER(m.numeroSerie)', ':search')
                )
            )->setParameter('search', $like);
        }

        // Filtre état
        $etat = trim((string) ($filters['etat'] ?? ''));
        if ($etat !== '') {
            $qb->andWhere('m.etatM = :etat')
               ->setParameter('etat', $etat);
        }

        // Tri
        $allowed = ['nom', 'marque', 'modele', 'etatM', 'dateAchat', 'kilometrage', 'id'];
        $sortBy  = in_array($filters['sortBy'] ?? '', $allowed, true)
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
    // Statistiques globales
    // =========================================================================
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
    // Machines dont la maintenance arrive bientôt
    // =========================================================================
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