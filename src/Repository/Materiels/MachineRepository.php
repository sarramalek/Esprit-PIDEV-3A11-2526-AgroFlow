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

        // Filtre par CIN (obligatoire)
        if (!empty($filters['cin'])) {
            $qb->andWhere('m.cin = :cin')
               ->setParameter('cin', (int) $filters['cin']);
        }

        // Recherche textuelle (nom, marque, modèle, numéro série)
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('m.nom', ':search'),
                    $qb->expr()->like('m.marque', ':search'),
                    $qb->expr()->like('m.modele', ':search'),
                    $qb->expr()->like('m.numeroSerie', ':search')
                )
            )->setParameter('search', $search);
        }

        // Filtre par état
        if (!empty($filters['etat']) && $filters['etat'] !== '') {
            $qb->andWhere('m.etatM = :etat')
               ->setParameter('etat', $filters['etat']);
        }

        // Tri
        $allowedSortFields = ['nom', 'marque', 'modele', 'etatM', 'dateAchat', 'id'];
        $sortBy = in_array($filters['sortBy'] ?? 'dateAchat', $allowedSortFields, true) 
                  ? $filters['sortBy'] 
                  : 'dateAchat';
        
        $sortDir = strtoupper($filters['sortDir'] ?? 'DESC') === 'DESC' ? 'DESC' : 'ASC';
        
        if ($sortBy === 'dateAchat') {
            $qb->addOrderBy('m.dateAchat', $sortDir);
            $qb->addOrderBy('m.id', $sortDir);
        } else {
            $qb->orderBy('m.' . $sortBy, $sortDir);
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
            ->andWhere('m.cin = :cin')
            ->setParameter('cin', $cin)
            ->orderBy('m.dateAchat', 'DESC')
            ->getQuery()
            ->getResult();
    }
}