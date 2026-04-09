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
    public function findBySearchCriteria(?string $search, ?string $categoryId, $user, string $sortBy = 'nom'): array
    {
        $qb = $this->createQueryBuilder('a');

        // 1. Filtrer par l'utilisateur connecté (Sécurité & Isolation des données)
        $qb->andWhere('a.user = :user')
            ->setParameter('user', $user);

        // 2. Filtre de recherche par nom
        if ($search) {
            $qb->andWhere('a.nom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // 3. Filtre par catégorie
        if ($categoryId && $categoryId !== '') {
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

    public function findByAdminCin(int $adminCin): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.idAdmin = :cin')
            ->setParameter('cin', $adminCin)
            ->orderBy('a.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
