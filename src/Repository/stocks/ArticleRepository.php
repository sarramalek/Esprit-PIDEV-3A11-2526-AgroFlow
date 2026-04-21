<?php

namespace App\Repository\stocks;

use App\Entity\stocks\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * Recherche, filtre et trie les articles
     */
    public function findBySearchCriteria(?string $search, ?string $categoryId, $user, string $sortBy = 'nom', ?int $idAdmin = null): array
    {
        $qb = $this->createQueryBuilder('a');

        // Filtre par Admin spécifique (Nouvelle règle)
        if ($idAdmin) {
            $qb->andWhere('a.idAdmin = :idAdmin')
               ->setParameter('idAdmin', $idAdmin);
        }

        // 1. Filtrer par l'utilisateur (Optionnel pour l'Admin)
        if ($user) {
            $qb->andWhere('a.user = :user')
                ->setParameter('user', $user);
        }

        // 2. Filtre de recherche global (Nom, Categorie, Agriculteur)
        if ($search) {
            $qb->leftJoin('a.categorie', 'c')
               ->leftJoin('a.user', 'u')
               ->andWhere('a.nom LIKE :search OR c.nom LIKE :search OR u.nom LIKE :search OR u.prenom LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        // 3. Filtre par catégorie (Dropdown)
        if ($categoryId && $categoryId !== '') {
            if (!$search) { $qb->leftJoin('a.categorie', 'c'); } // Join si pas deja fait
            $qb->andWhere('a.categorie = :catId')
               ->setParameter('catId', $categoryId);
        }

        // 4. Gestion dynamique du Tri
        switch ($sortBy) {
            case 'prix_asc':
                $qb->orderBy('a.prixUnitaire', 'ASC');
                break;
            case 'prix_desc':
                $qb->orderBy('a.prixUnitaire', 'DESC');
                break;
            case 'nom_desc':
                $qb->orderBy('a.nom', 'DESC');
                break;
            case 'nom':
            default:
                $qb->orderBy('a.nom', 'ASC');
                break;
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Trouve les articles créés par des utilisateurs ayant un rôle spécifique
     */
    public function findByUserRole(int $role): array
    {
        return $this->createQueryBuilder('a')
            ->join('a.user', 'u')
            ->where('u.role = :role')
            ->setParameter('role', $role)
            ->orderBy('a.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByAdminCin($admin): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.user = :user')
            ->setParameter('user', $admin)
            ->orderBy('a.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
