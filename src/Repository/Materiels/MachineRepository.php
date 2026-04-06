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

    /**
     * @return Machine[]
     */
    public function search(array $filters = []): array
    {
        $qb = $this->createQueryBuilder('m');

        // ── Recherche full-text ──
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('m.nom',         ':search'),
                    $qb->expr()->like('m.marque',      ':search'),
                    $qb->expr()->like('m.modele',      ':search'),
                    $qb->expr()->like('m.numeroSerie', ':search'),
                )
            )->setParameter('search', $search);
        }

        // ── Filtre par état ──
        if (!empty($filters['etat'])) {
            $qb->andWhere('m.etatM = :etat')
               ->setParameter('etat', $filters['etat']);
        }

        // ── Tri sécurisé ──
        $allowedSortFields = ['nom', 'marque', 'modele', 'etatM', 'dateAchat'];
        $sortBy  = in_array($filters['sortBy'] ?? '', $allowedSortFields, true)
                    ? $filters['sortBy'] : 'dateAchat';
        $sortDir = strtoupper($filters['sortDir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';

        $qb->orderBy('m.' . $sortBy, $sortDir);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array{total: int, parEtat: array, parMarque: array, achatsParAnnee: array}
     */
    public function getStatistiques(): array
    {
        // ── Total ──
        $total = (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // ── Répartition par état ──
        $rawEtat = $this->createQueryBuilder('m')
            ->select('m.etatM AS etat, COUNT(m.id) AS nb')
            ->groupBy('m.etatM')
            ->orderBy('COUNT(m.id)', 'DESC') // ✅ expression DQL, pas l'alias
            ->getQuery()
            ->getArrayResult();

        $parEtat = [];
        foreach ($rawEtat as $row) {
            $parEtat[$row['etat']] = (int) $row['nb'];
        }

        // ── Répartition par marque ──
        $rawMarque = $this->createQueryBuilder('m')
            ->select('m.marque AS marque, COUNT(m.id) AS nb')
            ->groupBy('m.marque')
            ->orderBy('COUNT(m.id)', 'DESC') // ✅ correction alias invalide
            ->getQuery()
            ->getArrayResult();

        $parMarque = [];
        foreach ($rawMarque as $row) {
            $parMarque[$row['marque']] = (int) $row['nb'];
        }

        // ── Achats par année ──
        // On récupère les dates en PHP pour éviter YEAR() non portable en Doctrine
        $rows = $this->createQueryBuilder('m')
            ->select('m.dateAchat')
            ->where('m.dateAchat IS NOT NULL')
            ->getQuery()
            ->getArrayResult();

        $achatsParAnnee = [];
        foreach ($rows as $row) {
            // dateAchat peut être un \DateTimeInterface ou une string selon le mapping
            $date  = $row['dateAchat'];
            $annee = $date instanceof \DateTimeInterface
                ? $date->format('Y')
                : (new \DateTime($date))->format('Y');

            $achatsParAnnee[$annee] = ($achatsParAnnee[$annee] ?? 0) + 1;
        }
        ksort($achatsParAnnee); // tri chronologique

        return compact('total', 'parEtat', 'parMarque', 'achatsParAnnee');
    }
}