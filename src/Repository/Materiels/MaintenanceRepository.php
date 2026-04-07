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

    // ── Recherche + filtre + tri — index ────────────────────────────────────
    public function search(
        string $search     = '',
        string $type       = '',
        string $sort       = 'dateMain',
        string $dir        = 'DESC',
        string $coutFilter = ''   // ← géré directement ici
    ): array {
        $allowedSorts = ['typePanne', 'cout', 'dateMain', 'description'];
        $allowedDirs  = ['ASC', 'DESC'];

        // Le filtre coût prend la priorité sur sort/dir
        if ($coutFilter === 'asc') {
            $sort = 'cout';
            $dir  = 'ASC';
        } elseif ($coutFilter === 'desc') {
            $sort = 'cout';
            $dir  = 'DESC';
        }

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

    // ── Toutes les maintenances triées par date DESC ─────────────────────────
    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('m')
            ->orderBy('m.dateMain', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // ── Coût total global ───────────────────────────────────────────────────
    // CORRIGÉ : ?? appliqué AVANT le cast (float) pour éviter (float)null = 0
    public function getTotalCout(): float
    {
        $result = $this->createQueryBuilder('m')
            ->select('SUM(m.cout)')
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0.0);
    }

    // ── Nombre de maintenances par type — retourne [['type'=>..,'total'=>..]]
    // CORRIGÉ : orderBy sur l'expression COUNT et non sur l'alias
    public function countByTypePanne(): array
    {
        $rows = $this->createQueryBuilder('m')
            ->select('m.typePanne AS type, COUNT(m.idMain) AS total')
            ->groupBy('m.typePanne')
            ->orderBy('COUNT(m.idMain)', 'DESC')
            ->getQuery()
            ->getScalarResult();

        // Forcer total en int pour que JSON encode correctement
        return array_map(fn($row) => [
            'type'  => $row['type'],
            'total' => (int) $row['total'],
        ], $rows);
    }

    // ── Coût total par mois — SQL natif ────────────────────────────────────
    // CORRIGÉ : noms de colonnes/table entre backticks via sprintf()
    public function getCoutByMonth(): array
    {
        $conn      = $this->getEntityManager()->getConnection();
        $tableName = $this->getClassMetadata()->getTableName();
        $colDate   = $this->getClassMetadata()->getColumnName('dateMain');
        $colCout   = $this->getClassMetadata()->getColumnName('cout');

        $sql = sprintf(
            "SELECT
                DATE_FORMAT(`%s`, '%%Y-%%m') AS moisRaw,
                SUM(`%s`)                    AS total
             FROM `%s`
             WHERE `%s` IS NOT NULL
             GROUP BY moisRaw
             ORDER BY moisRaw ASC",
            $colDate, $colCout, $tableName, $colDate
        );

        $rows = $conn->executeQuery($sql)->fetchAllAssociative();

        $moisFr = [
            '01' => 'Jan', '02' => 'Fév', '03' => 'Mar',
            '04' => 'Avr', '05' => 'Mai', '06' => 'Jun',
            '07' => 'Jul', '08' => 'Aoû', '09' => 'Sep',
            '10' => 'Oct', '11' => 'Nov', '12' => 'Déc',
        ];

        $result = [];
        foreach ($rows as $row) {
            if (empty($row['moisRaw'])) {
                continue;
            }
            [$annee, $mois] = explode('-', $row['moisRaw']);
            $result[] = [
                'mois'  => ($moisFr[$mois] ?? $mois) . ' ' . $annee,
                'total' => round((float) $row['total'], 2),
            ];
        }

        return $result;
    }

    // ── Coût total pour un matériel donné ───────────────────────────────────
    // CORRIGÉ : ?? appliqué AVANT le cast (float)
    public function getTotalCoutByMateriel(int $idM): float
    {
        $result = $this->createQueryBuilder('m')
            ->select('SUM(m.cout)')
            ->andWhere('m.idM = :idM')
            ->setParameter('idM', $idM)
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0.0);
    }

    // ── Maintenances par matériel ────────────────────────────────────────────
    public function findByIdMateriel(int $idM): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.idM = :idM')
            ->setParameter('idM', $idM)
            ->orderBy('m.dateMain', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // ── Dernière maintenance d'un matériel ──────────────────────────────────
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

    // ── Maintenances entre deux dates ───────────────────────────────────────
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

    // ── Recherche par type de panne ─────────────────────────────────────────
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