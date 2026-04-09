<?php

namespace App\Repository\Evenements;

use App\Entity\Evenements\categorieevenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CategorieEvenement>
 */
class categorieevenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, categorieevenement::class);
    }

    // ================= CRUD DE BASE (équivalent au service JavaFX) =================

    /**
     * Récupère toutes les catégories — équivalent de recuperer()
     */
    public function findAll(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.nomCategorie', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche par nom — équivalent du filtre de recherche JavaFX
     */
    public function findByNom(string $terme): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('LOWER(c.nomCategorie) LIKE LOWER(:terme)')
            ->setParameter('terme', '%' . $terme . '%')
            ->orderBy('c.nomCategorie', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère une catégorie par son ID — équivalent de getCategorieById()
     */
    public function findById(int $id): ?categorieevenement
    {
        return $this->find($id);
    }

    /**
     * Récupère le nom d'une catégorie par son ID — équivalent de getNomCategorieById()
     */
    public function getNomById(int $id): ?string
    {
        $categorie = $this->find($id);
        return $categorie?->getNomCategorie() ?? 'Non défini';
    }

    /**
     * Sauvegarde (ajouter ou modifier) — équivalent de ajouter() + modifier()
     */
    public function save(categorieevenement $categorie, bool $flush = true): void
    {
        $this->getEntityManager()->persist($categorie);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Supprime une catégorie — équivalent de supprimer()
     */
    public function remove(categorieevenement $categorie, bool $flush = true): void
    {
        $this->getEntityManager()->remove($categorie);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Compte toutes les catégories
     */
    public function countAll(): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.idCategorie)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}