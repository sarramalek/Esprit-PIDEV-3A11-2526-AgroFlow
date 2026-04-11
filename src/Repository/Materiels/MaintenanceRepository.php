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

    // Récupérer toutes les machines avec la bonne propriété
    public function getAllMachines(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT idM as id, nom FROM machine ORDER BY nom ASC";
        $stmt = $conn->executeQuery($sql);
        return $stmt->fetchAllAssociative();
    }

    // Recherche avec filtre et tri
    public function searchWithMaterielName(
        string $search = '',
        string $type   = '',
        string $sort   = 'dateMain',
        string $dir    = 'DESC',
        string $idM    = ''
    ): array {
        $conn = $this->getEntityManager()->getConnection();
        
        $allowedSorts = ['typePanne', 'cout', 'dateMain'];
        $allowedDirs  = ['ASC', 'DESC'];
        
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'dateMain';
        $dir  = in_array(strtoupper($dir), $allowedDirs, true) ? strtoupper($dir) : 'DESC';
        
        $sql = "
            SELECT 
                m.idMain,
                m.typePanne,
                m.cout,
                m.dateMain,
                m.description,
                m.idM,
                mac.nom
            FROM maintenance m
            LEFT JOIN machine mac ON mac.idM = m.idM
            WHERE 1=1
        ";
        
        $params = [];
        
        if ($search !== '') {
            $sql .= " AND (LOWER(m.typePanne) LIKE :search OR LOWER(m.description) LIKE :search OR LOWER(mac.nom) LIKE :search)";
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
        
        $stmt = $conn->executeQuery($sql, $params);
        $results = $stmt->fetchAllAssociative();
        
        $maintenances = [];
        foreach ($results as $row) {
            $m = new Maintenance();
            $m->setIdMain($row['idMain']);
            $m->setTypePanne($row['typePanne']);
            $m->setCout((float) $row['cout']);
            $m->setDateMain($row['dateMain'] ? new \DateTime($row['dateMain']) : null);
            $m->setDescription($row['description']);
            $m->setIdM($row['idM']);
            $m->setNom($row['nom']);
            $maintenances[] = $m;
        }
        
        return $maintenances;
    }

    // Toutes les maintenances
    public function findAllOrderedByDate(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = "
            SELECT 
                m.idMain,
                m.typePanne,
                m.cout,
                m.dateMain,
                m.description,
                m.idM,
                mac.nom
            FROM maintenance m
            LEFT JOIN machine mac ON mac.idM = m.idM
            ORDER BY m.dateMain DESC
        ";
        
        $stmt = $conn->executeQuery($sql);
        $results = $stmt->fetchAllAssociative();
        
        $maintenances = [];
        foreach ($results as $row) {
            $m = new Maintenance();
            $m->setIdMain($row['idMain']);
            $m->setTypePanne($row['typePanne']);
            $m->setCout((float) $row['cout']);
            $m->setDateMain($row['dateMain'] ? new \DateTime($row['dateMain']) : null);
            $m->setDescription($row['description']);
            $m->setIdM($row['idM']);
            $m->setNom($row['nom']);
            $maintenances[] = $m;
        }
        
        return $maintenances;
    }

    // Trouver par ID
    public function findOneWithMaterielName(int $id): ?Maintenance
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = "
            SELECT 
                m.idMain,
                m.typePanne,
                m.cout,
                m.dateMain,
                m.description,
                m.idM,
                mac.nom
            FROM maintenance m
            LEFT JOIN machine mac ON mac.idM = m.idM
            WHERE m.idMain = :id
        ";
        
        $stmt = $conn->executeQuery($sql, ['id' => $id]);
        $row = $stmt->fetchAssociative();
        
        if (!$row) {
            return null;
        }
        
        $m = new Maintenance();
        $m->setIdMain($row['idMain']);
        $m->setTypePanne($row['typePanne']);
        $m->setCout((float) $row['cout']);
        $m->setDateMain($row['dateMain'] ? new \DateTime($row['dateMain']) : null);
        $m->setDescription($row['description']);
        $m->setIdM($row['idM']);
        $m->setNom($row['nom']);
        
        return $m;
    }

    // Coût total
    public function getTotalCout(): float
    {
        $result = $this->createQueryBuilder('m')
            ->select('SUM(m.cout)')
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0.0);
    }

    // Nombre par type
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

    // Coût par mois
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
}