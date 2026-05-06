<?php

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
    // Recherche filtrée — retourne un array (sans paginator)
    // =========================================================================
    /**
     * @param array{cin?:mixed,search?:mixed,etat?:mixed,sortBy?:mixed,sortDir?:mixed} $filters
     * @return list<Machine>
     */
    public function search(array $filters = []): array
    {
        $cinRaw = $filters['cin'] ?? null;
        $cin = is_numeric($cinRaw) ? (int) $cinRaw : 0;

        if ($cin <= 0) {
            return [];
        }

        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.agriculteur', 'u')
            ->addSelect('u')
            ->andWhere('u.cin = :cin')
            ->setParameter('cin', $cin);

        // Recherche textuelle
        $searchRaw = $filters['search'] ?? '';
        $search = trim(is_string($searchRaw) ? $searchRaw : '');
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
        $etatRaw = $filters['etat'] ?? '';
        $etat = trim(is_string($etatRaw) ? $etatRaw : '');
        if ($etat !== '') {
            $qb->andWhere('m.etatM = :etat')
               ->setParameter('etat', $etat);
        }

        // Tri
        $allowed = ['nom', 'marque', 'modele', 'etatM', 'dateAchat', 'kilometrage', 'id'];
        $sortByInput = $filters['sortBy'] ?? '';
        $sortBy  = is_string($sortByInput) && in_array($sortByInput, $allowed, true)
                     ? $sortByInput
                     : 'dateAchat';
        $sortDirInput = $filters['sortDir'] ?? 'DESC';
        $sortDir = is_string($sortDirInput) && strtoupper($sortDirInput) === 'ASC' ? 'ASC' : 'DESC';

        $qb->orderBy('m.' . $sortBy, $sortDir);
        if ($sortBy !== 'id') {
            $qb->addOrderBy('m.id', 'DESC');
        }

        /** @var list<Machine> $results */
        $results = $qb->getQuery()->getResult();
        return $results;
    }

    // =========================================================================
    // Statistiques globales
    // =========================================================================
    /**
     * @return array{
     *   total:int,
     *   parEtat:array<string,int>,
     *   parMarque:array<string,int>,
     *   statsKm:array<string,mixed>
     * }
     */
    public function getStatistiques(): array
    {
        $total = (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->getQuery()
            ->getSingleScalarResult();

        /** @var list<array<string,mixed>> $rawEtat */
        $rawEtat = $this->createQueryBuilder('m')
            ->select('m.etatM AS etat, COUNT(m.id) AS nb')
            ->groupBy('m.etatM')
            ->orderBy('nb', 'DESC')
            ->getQuery()
            ->getArrayResult();

        $parEtat = [];
        foreach ($rawEtat as $row) {
            $etat = isset($row['etat']) && is_string($row['etat']) ? $row['etat'] : 'inconnu';
            $nb = $row['nb'] ?? 0;
            $parEtat[$etat] = is_numeric($nb) ? (int) $nb : 0;
        }

        /** @var list<array<string,mixed>> $rawMarque */
        $rawMarque = $this->createQueryBuilder('m')
            ->select('m.marque AS marque, COUNT(m.id) AS nb')
            ->groupBy('m.marque')
            ->orderBy('nb', 'DESC')
            ->getQuery()
            ->getArrayResult();

        $parMarque = [];
        foreach ($rawMarque as $row) {
            $marque = isset($row['marque']) && is_string($row['marque']) ? $row['marque'] : 'inconnue';
            $nb = $row['nb'] ?? 0;
            $parMarque[$marque] = is_numeric($nb) ? (int) $nb : 0;
        }

        /** @var array{avgKm:mixed,maxKm:mixed,minKm:mixed} $statsKm */
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
    /** @return list<Machine> */
    public function findByCin(int $cin): array
    {
        /** @var list<Machine> $results */
        $results = $this->createQueryBuilder('m')
            ->leftJoin('m.agriculteur', 'u')
            ->addSelect('u')
            ->andWhere('u.cin = :cin')
            ->setParameter('cin', $cin)
            ->orderBy('m.dateAchat', 'DESC')
            ->addOrderBy('m.id', 'DESC')
            ->getQuery()
            ->getResult();
        return $results;
    }

    // =========================================================================
    // Machines dont la maintenance arrive bientôt
    // =========================================================================
    /** @return list<Machine> */
    public function findMachinesWithMaintenanceSoon(\DateTimeInterface $dateLimit): array
    {
        /** @var list<Machine> $results */
        $results = $this->createQueryBuilder('m')
            ->leftJoin('m.agriculteur', 'u')
            ->addSelect('u')
            ->where('m.prochaineMaintenance IS NOT NULL')
            ->andWhere('m.prochaineMaintenance <= :dateLimit')
            ->setParameter('dateLimit', $dateLimit)
            ->orderBy('m.prochaineMaintenance', 'ASC')
            ->getQuery()
            ->getResult();
        return $results;
    }
}