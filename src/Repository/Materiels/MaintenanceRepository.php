<?php

namespace App\Repository\Materiels;

use App\Entity\Materiels\Maintenance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MaintenanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Maintenance::class);
    }

    /**
     * Recherche + filtre + tri — utilisé par l'index
     */
    public function search(
        string $search = '',
        string $type   = '',
        string $sort   = 'dateMain',
        string $dir    = 'DESC'
    ): array {
        $allowedSorts = ['typePanne', 'cout', 'dateMain', 'idM'];
        $allowedDirs  = ['ASC', 'DESC'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'dateMain';
        $dir  = in_array(strtoupper($dir), $allowedDirs, true) ? strtoupper($dir) : 'DESC';

        $qb = $this->createQueryBuilder('m');

        if ($search !== '') {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('m.typePanne',   ':s'),
                    $qb->expr()->like('m.description', ':s')
                )
            )->setParameter('s', '%' . $search . '%');
        }

        if ($type !== '') {
            $qb->andWhere('m.typePanne = :type')
               ->setParameter('type', $type);
        }

        return $qb->orderBy('m.' . $sort, $dir)
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Toutes les maintenances triées par date DESC
     */
    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('m')
            ->orderBy('m.dateMain', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Coût total global
     */
    public function getTotalCout(): float
    {
        return (float) ($this->createQueryBuilder('m')
            ->select('SUM(m.cout)')
            ->getQuery()
            ->getSingleScalarResult() ?? 0.0);
    }

    /**
     * Nombre de maintenances par type de panne
     */
    public function countByTypePanne(): array
    {
        return $this->createQueryBuilder('m')
            ->select('m.typePanne AS type, COUNT(m.idMain) AS total')
            ->groupBy('m.typePanne')
            ->orderBy('total', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Coût total par mois — utilise dateMain (nom exact de la colonne)
     */
    public function getCoutByMonth(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT DATE_FORMAT(dateMain, '%Y-%m') AS mois,
                       SUM(cout) AS total
                FROM maintenance
                GROUP BY mois
                ORDER BY mois ASC
                LIMIT 12";

        return $conn->fetchAllAssociative($sql);
    }

    /**
     * Coût total pour un matériel donné
     */
    public function getTotalCoutByMateriel(int $idM): float
    {
        return (float) ($this->createQueryBuilder('m')
            ->select('SUM(m.cout)')
            ->andWhere('m.idM = :idM')
            ->setParameter('idM', $idM)
            ->getQuery()
            ->getSingleScalarResult() ?? 0.0);
    }

    /**
     * Maintenances par matériel
     */
    public function findByIdMateriel(int $idM): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.idM = :idM')
            ->setParameter('idM', $idM)
            ->orderBy('m.dateMain', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Dernière maintenance d'un matériel
     */
    public function findLastByMateriel(int $idM): ?Maintenance
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.idM = :idM')
            ->setParameter('idM', $idM)
            ->orderBy('m.dateMain', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Maintenances entre deux dates
     */
    public function findBetweenDates(\DateTime $start, \DateTime $end): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.dateMain BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('m.dateMain', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche par type de panne
     */
    public function findByTypePanne(string $typePanne): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.typePanne = :type')
            ->setParameter('type', $typePanne)
            ->orderBy('m.dateMain', 'DESC')
            ->getQuery()
            ->getResult();
    }
}