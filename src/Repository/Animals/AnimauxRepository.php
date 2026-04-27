<?php

namespace App\Repository\Animals;

use App\Entity\Animals\Animaux;
use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Animaux>
 */
class AnimauxRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Animaux::class);
    }

    /**
     * Recherche les animaux par nom, espèce ou sexe avec option de tri et filtrage par utilisateur.
     * 
     * @return array<int, Animaux>
     */
    public function searchDashboard(?string $term, ?string $sortBy = 'id', ?string $direction = 'DESC', ?User $user = null): array
    {
        $qb = $this->createQueryBuilder('a');

        if ($user) {
            $qb->andWhere('a.user = :user')
               ->setParameter('user', $user);
        }

        if ($term && trim($term) !== '') {
            $qb->andWhere('(a.nom LIKE :term OR a.espece LIKE :term OR a.sexe LIKE :term)')
               ->setParameter('term', '%' . $term . '%');
        }

        // Liste blanche des colonnes de tri
        $validSorts = ['id', 'nom', 'espece', 'sexe', 'poids', 'date_naissance'];
        if (!in_array($sortBy, $validSorts)) {
            $sortBy = 'id';
        }
        
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        return $qb->orderBy('a.' . $sortBy, $direction)->getQuery()->getResult();
    }

    /**
     * Créer un QueryBuilder pour la pagination des animaux
     * 
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createQueryBuilderForSearch(?string $term, ?string $sortBy = 'id', ?string $direction = 'DESC', ?User $user = null)
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a');

        if ($user) {
            $qb->andWhere('a.user = :user')
               ->setParameter('user', $user);
        }

        if ($term && trim($term) !== '') {
            $qb->andWhere('(a.nom LIKE :term OR a.espece LIKE :term OR a.sexe LIKE :term)')
               ->setParameter('term', '%' . $term . '%');
        }

        // Liste blanche des colonnes de tri
        $validSorts = ['id', 'nom', 'espece', 'sexe', 'poids', 'date_naissance'];
        if (!in_array($sortBy, $validSorts)) {
            $sortBy = 'id';
        }
        
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        return $qb->orderBy('a.' . $sortBy, $direction);
    }

    /**
     * Recherche les animaux par nom, espèce ou sexe pour l'admin (en filtrant par ID).
     * 
     * @param int[] $ids
     * @return array<int, Animaux>
     */
    public function searchDashboardAdmin(?string $term, ?string $sortBy = 'id', ?string $direction = 'DESC', array $ids = []): array
    {
        if (empty($ids)) {
            return [];
        }

        $qb = $this->createQueryBuilder('a')
                   ->andWhere('a.id IN (:ids)')
                   ->setParameter('ids', $ids);

        if ($term && trim($term) !== '') {
            $qb->andWhere('(a.nom LIKE :term OR a.espece LIKE :term OR a.sexe LIKE :term)')
               ->setParameter('term', '%' . $term . '%');
        }

        // Liste blanche des colonnes de tri
        $validSorts = ['id', 'nom', 'espece', 'sexe', 'poids', 'date_naissance'];
        if (!in_array($sortBy, $validSorts)) {
            $sortBy = 'id';
        }
        
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        return $qb->orderBy('a.' . $sortBy, $direction)->getQuery()->getResult();
    }

    /**
     * Statistiques par espèce, filtrées par utilisateur si nécessaire.
     * 
     * @return array<int, array{espece: string, total: int}>
     */
    public function countByEspece(?User $user = null): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a.espece as espece, COUNT(a.id) as total')
            ->groupBy('a.espece');

        if ($user) {
            $qb->andWhere('a.user = :user')
               ->setParameter('user', $user);
        }

        return $qb->getQuery()->getResult();
    }
    /**
     * Trouve des partenaires potentiels (même espèce, sexe opposé).
     * 
     * @return array<int, Animaux>
     */
    public function findPotentialPartners(Animaux $animal): array
    {
        $oppositeSex = ($animal->getSexe() === 'MALE') ? 'FEMELLE' : 'MALE';

        return $this->createQueryBuilder('a')
            ->andWhere('a.espece = :espece')
            ->andWhere('a.sexe = :sexe')
            ->andWhere('a.id != :id')
            ->andWhere('a.user IS NOT NULL')
            ->setParameter('espece', $animal->getEspece())
            ->setParameter('sexe', $oppositeSex)
            ->setParameter('id', $animal->getId())
            ->orderBy('a.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
    /**
     * Calcule le poids moyen par espèce pour l'ensemble des animaux.
     * 
     * @return array<string, float>
     */
    public function getAverageWeightsBySpecies(): array
    {
        $results = $this->createQueryBuilder('a')
            ->select('a.espece as espece, AVG(a.poids) as moyenne')
            ->where('a.poids IS NOT NULL')
            ->groupBy('a.espece')
            ->getQuery()
            ->getResult();

        $averages = [];
        foreach ($results as $row) {
            $averages[strtolower($row['espece'])] = (float) $row['moyenne'];
        }

        return $averages;
    }
}
