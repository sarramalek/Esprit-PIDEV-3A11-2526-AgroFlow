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

    public function search(array $filters = []): array
    {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.agriculteur', 'u')
            ->addSelect('u');

        // ── Filtre par CIN ──────────────────────────────────────────────────
        // ✅ CORRIGÉ : on vérifie que cin est un entier valide > 0
        // Si cin est null/0/vide → on ne retourne RIEN (sécurité : jamais afficher
        // toutes les machines de tous les agriculteurs)
        $cin = isset($filters['cin']) ? (int) $filters['cin'] : 0;

        if ($cin > 0) {
            $qb->andWhere('u.cin = :cin')
               ->setParameter('cin', $cin);
        } else {
            // ✅ Sécurité : si pas de CIN valide, retourner tableau vide
            return [];
        }

        // ── Recherche textuelle (insensible à la casse) ─────────────────────
        if (!empty($filters['search'])) {
            $search = '%' . mb_strtolower(trim($filters['search'])) . '%';
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('LOWER(m.nom)',         ':search'),
                    $qb->expr()->like('LOWER(m.marque)',      ':search'),
                    $qb->expr()->like('LOWER(m.modele)',      ':search'),
                    $qb->expr()->like('LOWER(m.numeroSerie)', ':search')
                )
            )->setParameter('search', $search);
        }

        // ── Filtre par état ─────────────────────────────────────────────────
        if (!empty($filters['etat'])) {
            $qb->andWhere('m.etatM = :etat')
               ->setParameter('etat', $filters['etat']);
        }

        // ── Tri ─────────────────────────────────────────────────────────────
        $allowedSortFields = ['nom', 'marque', 'modele', 'etatM', 'dateAchat', 'id'];
        $sortBy  = in_array($filters['sortBy'] ?? 'dateAchat', $allowedSortFields, true)
                   ? ($filters['sortBy'] ?? 'dateAchat')
                   : 'dateAchat';
        $sortDir = strtoupper($filters['sortDir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

        $qb->orderBy('m.' . $sortBy, $sortDir);
        if ($sortBy !== 'id') {
            $qb->addOrderBy('m.id', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }

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

        return compact('total', 'parEtat', 'parMarque');
    }

    public function findByCin(int $cin): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.agriculteur', 'u')
            ->andWhere('u.cin = :cin')
            ->setParameter('cin', $cin)
            ->orderBy('m.dateAchat', 'DESC')
            ->getQuery()
            ->getResult();
    }
}