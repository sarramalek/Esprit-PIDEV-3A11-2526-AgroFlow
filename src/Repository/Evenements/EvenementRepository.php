<?php

namespace App\Repository\Evenements;

use App\Entity\Evenements\Evenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Evenement>
 */
class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }

    /**
     * Récupère tous les événements triés par date de début décroissante
     */
    public function findAll(): array
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche/filtre les événements selon les critères (équivalent du filtre JavaFX)
     */
    public function findByFilters(
        ?string $search = null,
        ?string $dateDebut = null,
        ?string $dateFin = null,
        ?string $lieu = null,
        ?string $statut = null,
        ?int $categorieId = null
    ): array {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.categorie', 'c')
            ->addSelect('c');

        // Recherche par titre
        if (!empty($search)) {
            $qb->andWhere('LOWER(e.titre) LIKE LOWER(:search)')
               ->setParameter('search', '%' . $search . '%');
        }

        // Date de début exacte
        if (!empty($dateDebut)) {
            $qb->andWhere('e.dateDebut = :dateDebut')
               ->setParameter('dateDebut', new \DateTime($dateDebut));
        }

        // Date de fin exacte
        if (!empty($dateFin)) {
            $qb->andWhere('e.dateFin = :dateFin')
               ->setParameter('dateFin', new \DateTime($dateFin));
        }

        // Lieu (contient)
        if (!empty($lieu)) {
            $qb->andWhere('LOWER(e.lieu) LIKE LOWER(:lieu)')
               ->setParameter('lieu', '%' . $lieu . '%');
        }

        // Statut
        if (!empty($statut) && $statut !== 'Tous les statuts') {
            $qb->andWhere('e.statut = :statut')
               ->setParameter('statut', $statut);
        }

        // Catégorie
        if (!empty($categorieId)) {
            $qb->andWhere('c.idCategorie = :categorieId')
               ->setParameter('categorieId', $categorieId);
        }

        return $qb->orderBy('e.dateDebut', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Récupère un événement par son ID
     */
    public function findById(int $id): ?Evenement
    {
        return $this->find($id);
    }

    /**
     * Récupère le titre d'un événement par son ID
     */
    public function getTitreById(int $id): string
    {
        $evenement = $this->find($id);
        return $evenement?->getTitre() ?? 'Non défini';
    }

    /**
     * Sauvegarde (ajout ou modification)
     */
    public function save(Evenement $evenement, bool $flush = true): void
    {
        $this->getEntityManager()->persist($evenement);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Supprime un événement
     */
    public function remove(Evenement $evenement, bool $flush = true): void
    {
        $this->getEntityManager()->remove($evenement);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Compte tous les événements
     */
    public function countAll(): int
    {
        return $this->createQueryBuilder('e')
            ->select('COUNT(e.idEvenement)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}