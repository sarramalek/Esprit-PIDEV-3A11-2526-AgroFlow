<?php

namespace App\Repository\Materiels;

use App\Entity\Materiels\Maintenance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Maintenance>
 */
class MaintenanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Maintenance::class);
    }

    /**
     * @param array<string,mixed> $row
     */
    private function hydrate(array $row): Maintenance
    {
        $m = new Maintenance();
        $idMain = $row['idMain'] ?? null;
        $m->setIdMain(is_numeric($idMain) ? (int) $idMain : 0);
        $typePanne = $row['typePanne'] ?? null;
        $m->setTypePanne(is_string($typePanne) ? $typePanne : '');
        $cout = $row['cout'] ?? null;
        $m->setCout(is_numeric($cout) ? (float) $cout : 0.0);
        $dateMain = $row['dateMain'] ?? null;
        $m->setDateMain(is_string($dateMain) && $dateMain !== '' ? new \DateTime($dateMain) : null);
        $description = $row['description'] ?? null;
        $m->setDescription(is_string($description) ? $description : null);
        $idM = $row['idM'] ?? null;
        $m->setIdM(is_numeric($idM) ? (int) $idM : null);
        $statut = $row['statut'] ?? null;
        $m->setStatut(is_string($statut) && $statut !== '' ? $statut : 'planifie');
        $recommandation = $row['recommandation'] ?? null;
        $m->setRecommandation(is_string($recommandation) ? $recommandation : null);
        $priorite = $row['priorite'] ?? null;
        $m->setPriorite(is_string($priorite) && $priorite !== '' ? $priorite : 'moyenne');
        $kilometrage = $row['kilometrage'] ?? null;
        $m->setKilometrage(is_numeric($kilometrage) ? (int) $kilometrage : null);
        $nom = $row['nom'] ?? null;
        $m->setNom(is_string($nom) ? $nom : null);
        return $m;
    }

    /** @return list<array{id:int,nom:string}> */
    public function getAllMachines(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql  = "SELECT idM as id, nom FROM machine ORDER BY nom ASC";
        $rows = $conn->executeQuery($sql)->fetchAllAssociative();
        return array_map(
            static fn (array $row): array => [
                'id' => isset($row['id']) && is_numeric($row['id']) ? (int) $row['id'] : 0,
                'nom' => isset($row['nom']) && is_string($row['nom']) ? $row['nom'] : '',
            ],
            $rows
        );
    }

    /**
     * @return list<Maintenance>
     */
    public function searchWithMaterielName(
    string $search = '',
    string $type   = '',
    string $sort   = 'dateMain',
    string $dir    = 'DESC',
    string $idM    = '',
    string $statut = '',      // ✅ paramètre ajouté
    string $priorite = ''     // ✅ paramètre ajouté
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

    // ✅ Filtres ajoutés
    if ($statut !== '') {
        $sql .= " AND m.statut = :statut";
        $params['statut'] = $statut;
    }

    if ($priorite !== '') {
        $sql .= " AND m.priorite = :priorite";
        $params['priorite'] = $priorite;
    }

    $sql .= " ORDER BY m.$sort $dir";

    /** @var list<array<string,mixed>> $results */
    $results = $conn->executeQuery($sql, $params)->fetchAllAssociative();
    return array_map(fn($row) => $this->hydrate($row), $results);
}

    /** @return list<Maintenance> */
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

    /** @return list<array{type:string,total:int}> */
    public function countByTypePanne(): array
    {
        $rows = $this->createQueryBuilder('m')
            ->select('m.typePanne AS type, COUNT(m.idMain) AS total')
            ->groupBy('m.typePanne')
            ->orderBy('COUNT(m.idMain)', 'DESC')
            ->getQuery()
            ->getScalarResult();

        return array_values(array_map(static fn (mixed $row): array => [
            'type'  => is_array($row) && isset($row['type']) && is_string($row['type']) ? $row['type'] : '',
            'total' => is_array($row) && isset($row['total']) && is_numeric($row['total']) ? (int) $row['total'] : 0,
        ], $rows));
    }

    /** @return list<array{statut:string,total:int}> */
    public function countByStatut(): array
    {
        $rows = $this->createQueryBuilder('m')
            ->select('m.statut AS statut, COUNT(m.idMain) AS total')
            ->groupBy('m.statut')
            ->getQuery()
            ->getScalarResult();

        return array_values(array_map(static fn (mixed $row): array => [
            'statut' => is_array($row) && isset($row['statut']) && is_string($row['statut']) ? $row['statut'] : '',
            'total'  => is_array($row) && isset($row['total']) && is_numeric($row['total']) ? (int) $row['total'] : 0,
        ], $rows));
    }

    /** @return list<array{priorite:string,total:int}> */
    public function countByPriorite(): array
    {
        $rows = $this->createQueryBuilder('m')
            ->select('m.priorite AS priorite, COUNT(m.idMain) AS total')
            ->groupBy('m.priorite')
            ->getQuery()
            ->getScalarResult();

        return array_values(array_map(static fn (mixed $row): array => [
            'priorite' => is_array($row) && isset($row['priorite']) && is_string($row['priorite']) ? $row['priorite'] : '',
            'total'    => is_array($row) && isset($row['total']) && is_numeric($row['total']) ? (int) $row['total'] : 0,
        ], $rows));
    }

    /** @return list<array{mois:string,total:float}> */
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
            $moisRaw = is_string($row['moisRaw']) ? $row['moisRaw'] : '';
            if ($moisRaw === '') {
                continue;
            }
            [$annee, $mois] = explode('-', $moisRaw);
            $result[] = [
                'mois'  => ($moisFr[$mois] ?? $mois) . ' ' . $annee,
                'total' => round(isset($row['total']) && is_numeric($row['total']) ? (float) $row['total'] : 0.0, 2),
            ];
        }
        return $result;
    }
    public function saveAll(array $maintenances): void
{
    foreach ($maintenances as $maintenance) {
        $this->getEntityManager()->persist($maintenance);
    }
    $this->getEntityManager()->flush();
}

public function findByAgriculteurCin(string $cin): array
{
    return $this->createQueryBuilder('m')
        ->join('m.machine', 'mac')
        ->join('mac.agriculteur', 'u')
        ->where('u.cin = :cin')
        ->setParameter('cin', $cin)
        ->getQuery()
        ->getResult();
}
}