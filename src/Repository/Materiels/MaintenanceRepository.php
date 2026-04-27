<?php

namespace App\Repository\Materiels;

use App\Entity\Materiels\Maintenance;
use App\Entity\Materiels\Machine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MaintenanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Maintenance::class);
    }

    private function hydrate(array $row): Maintenance
    {
        $m = new Maintenance();
        $m->setIdMain($row['idMain']);
        $m->setTypePanne($row['typePanne']);
        $m->setCout((float) $row['cout']);
        $m->setDateMain($row['dateMain'] ? new \DateTime($row['dateMain']) : null);
        $m->setDescription($row['description']);
        $m->setIdM($row['idM'] ? (int)$row['idM'] : null);
        $m->setStatut($row['statut'] ?? 'planifie');
        $m->setRecommandation($row['recommandation'] ?? null);
        $m->setPriorite($row['priorite'] ?? 'moyenne');
        $m->setKilometrage(isset($row['kilometrage']) && $row['kilometrage'] !== null ? (int)$row['kilometrage'] : null);
        $m->setNom($row['nom'] ?? null);
        return $m;
    }

    public function getAllMachines(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql  = "SELECT idM as id, nom FROM machine ORDER BY nom ASC";
        return $conn->executeQuery($sql)->fetchAllAssociative();
    }

    public function searchWithMaterielName(
        string $search = '',
        string $type   = '',
        string $sort   = 'dateMain',
        string $dir    = 'DESC',
        string $idM    = ''
    ): array {
        $conn = $this->getEntityManager()->getConnection();

        $allowedSorts = ['typePanne', 'cout', 'dateMain', 'statut', 'priorite', 'kilometrage'];
        $allowedDirs  = ['ASC', 'DESC'];

        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'dateMain';
        $dir  = in_array(strtoupper($dir), $allowedDirs, true) ? strtoupper($dir) : 'DESC';

        $sql = "
            SELECT 
                m.idMain, m.typePanne, m.cout, m.dateMain,
                m.description, m.idM,
                m.statut, m.recommandation, m.priorite, m.kilometrage,
                mac.nom
            FROM maintenance m
            LEFT JOIN machine mac ON mac.idM = m.idM
            WHERE 1=1
        ";

        $params = [];

        if ($search !== '') {
            $sql .= " AND (LOWER(m.typePanne) LIKE :search OR LOWER(m.description) LIKE :search OR LOWER(mac.nom) LIKE :search OR LOWER(m.recommandation) LIKE :search)";
            $params['search'] = '%' . strtolower($search) . '%';
        }

        if ($type !== '') {
            $sql .= " AND LOWER(m.typePanne) = :type";
            $params['type'] = strtolower($type);
        }

        if ($idM !== '') {
            $sql .= " AND m.idM = :idM";
            $params['idM'] = (int) $idM;
        }

        $sql .= " ORDER BY m.$sort $dir";

        $results = $conn->executeQuery($sql, $params)->fetchAllAssociative();
        return array_map(fn($row) => $this->hydrate($row), $results);
    }

    public function findAllOrderedByDate(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT 
                m.idMain, m.typePanne, m.cout, m.dateMain,
                m.description, m.idM,
                m.statut, m.recommandation, m.priorite, m.kilometrage,
                mac.nom
            FROM maintenance m
            LEFT JOIN machine mac ON mac.idM = m.idM
            ORDER BY m.dateMain DESC
        ";

        return array_map(fn($row) => $this->hydrate($row), $conn->executeQuery($sql)->fetchAllAssociative());
    }

    public function findOneWithMaterielName(int $id): ?Maintenance
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT 
                m.idMain, m.typePanne, m.cout, m.dateMain,
                m.description, m.idM,
                m.statut, m.recommandation, m.priorite, m.kilometrage,
                mac.nom
            FROM maintenance m
            LEFT JOIN machine mac ON mac.idM = m.idM
            WHERE m.idMain = :id
        ";

        $row = $conn->executeQuery($sql, ['id' => $id])->fetchAssociative();
        return $row ? $this->hydrate($row) : null;
    }

    public function getTotalCout(): float
    {
        $result = $this->createQueryBuilder('m')
            ->select('SUM(m.cout)')
            ->getQuery()
            ->getSingleScalarResult();
        return (float) ($result ?? 0.0);
    }

    public function countByTypePanne(): array
    {
        $rows = $this->createQueryBuilder('m')
            ->select('m.typePanne AS type, COUNT(m.idMain) AS total')
            ->groupBy('m.typePanne')
            ->orderBy('COUNT(m.idMain)', 'DESC')
            ->getQuery()
            ->getScalarResult();

        return array_map(fn($row) => [
            'type'  => $row['type'],
            'total' => (int) $row['total'],
        ], $rows);
    }

    public function countByStatut(): array
    {
        $rows = $this->createQueryBuilder('m')
            ->select('m.statut AS statut, COUNT(m.idMain) AS total')
            ->groupBy('m.statut')
            ->getQuery()
            ->getScalarResult();

        return array_map(fn($row) => [
            'statut' => $row['statut'],
            'total'  => (int) $row['total'],
        ], $rows);
    }

    public function countByPriorite(): array
    {
        $rows = $this->createQueryBuilder('m')
            ->select('m.priorite AS priorite, COUNT(m.idMain) AS total')
            ->groupBy('m.priorite')
            ->getQuery()
            ->getScalarResult();

        return array_map(fn($row) => [
            'priorite' => $row['priorite'],
            'total'    => (int) $row['total'],
        ], $rows);
    }

    public function getCoutByMonth(): array
    {
        $conn      = $this->getEntityManager()->getConnection();
        $tableName = $this->getClassMetadata()->getTableName();
        $colDate   = $this->getClassMetadata()->getColumnName('dateMain');
        $colCout   = $this->getClassMetadata()->getColumnName('cout');

        $sql = sprintf(
            "SELECT DATE_FORMAT(`%s`, '%%Y-%%m') AS moisRaw, SUM(`%s`) AS total
             FROM `%s` WHERE `%s` IS NOT NULL
             GROUP BY moisRaw ORDER BY moisRaw ASC",
            $colDate, $colCout, $tableName, $colDate
        );

        $moisFr = [
            '01'=>'Jan','02'=>'Fév','03'=>'Mar','04'=>'Avr','05'=>'Mai','06'=>'Jun',
            '07'=>'Jul','08'=>'Aoû','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Déc',
        ];

        $result = [];
        foreach ($conn->executeQuery($sql)->fetchAllAssociative() as $row) {
            if (empty($row['moisRaw'])) continue;
            [$annee, $mois] = explode('-', $row['moisRaw']);
            $result[] = [
                'mois'  => ($moisFr[$mois] ?? $mois) . ' ' . $annee,
                'total' => round((float) $row['total'], 2),
            ];
        }
        return $result;
    }
}