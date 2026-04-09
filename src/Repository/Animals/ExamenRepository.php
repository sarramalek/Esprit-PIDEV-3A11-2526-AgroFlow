<?php

namespace App\Repository\Animals;

use App\Entity\Animals\Examen;
use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Examen>
 */
class ExamenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Examen::class);
    }

    /**
     * Recherche avancée d'examens, filtrée par utilisateur si nécessaire.
     */
    public function searchExamen(?string $term, ?string $sortBy = 'id', ?string $direction = 'DESC', ?string $type = null, ?User $user = null): array
    {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.animal', 'a')
            ->addSelect('a');

        if ($user) {
            $qb->andWhere('a.user = :user')
               ->setParameter('user', $user);
        }

        if ($term && trim($term) !== '') {
            $qb->andWhere('(e.diagnostic LIKE :term OR e.traitement LIKE :term OR a.nom LIKE :term OR e.type_examen LIKE :term)')
               ->setParameter('term', '%' . $term . '%');
        }

        if ($type && trim($type) !== '') {
            $qb->andWhere('e.type_examen = :type')
               ->setParameter('type', $type);
        }

        // Liste blanche des colonnes de tri
        $validSorts = ['id', 'date_examen', 'type_examen', 'diagnostic', 'traitement'];
        if (!in_array($sortBy, $validSorts)) {
            $sortBy = 'id';
        }

        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        return $qb->orderBy('e.' . $sortBy, $direction)->getQuery()->getResult();
    }

    /**
     * Recherche avancée d'examens pour l'admin (en filtrant par ID).
     */
    public function searchExamenAdmin(?string $term, ?string $sortBy = 'id', ?string $direction = 'DESC', ?string $type = null, array $ids = []): array
    {
        if (empty($ids)) {
            return [];
        }

        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.animal', 'a')
            ->addSelect('a')
            ->andWhere('e.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($term && trim($term) !== '') {
            $qb->andWhere('(e.diagnostic LIKE :term OR e.traitement LIKE :term OR a.nom LIKE :term OR e.type_examen LIKE :term)')
               ->setParameter('term', '%' . $term . '%');
        }

        if ($type && trim($type) !== '') {
            $qb->andWhere('e.type_examen = :type')
               ->setParameter('type', $type);
        }

        // Liste blanche des colonnes de tri
        $validSorts = ['id', 'date_examen', 'type_examen', 'diagnostic', 'traitement'];
        if (!in_array($sortBy, $validSorts)) {
            $sortBy = 'id';
        }

        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        return $qb->orderBy('e.' . $sortBy, $direction)->getQuery()->getResult();
    }

    /**
     * Statistiques par type d'examen, filtrées par utilisateur.
     */
    public function countByType(?User $user = null): array
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e.type_examen as type, COUNT(e.id) as total')
            ->groupBy('e.type_examen');

        if ($user) {
            $qb->leftJoin('e.animal', 'a')
               ->andWhere('a.user = :user')
               ->setParameter('user', $user);
        }

        return $qb->getQuery()->getResult();
    }
    /**
     * Analyse la fragilité par espèce pour un utilisateur (pourcentage d'animaux malades).
     */
    public function getFragilityData(User $user): array
    {
        // 1. Récupérer tous les animaux de l'utilisateur groupés par espèce
        $allAnimals = $this->getEntityManager()->getRepository(\App\Entity\Animals\Animaux::class)
            ->findBy(['user' => $user]);

        $speciesStats = [];
        foreach ($allAnimals as $animal) {
            $esp = $animal->getEspece() ?: 'Autre';
            if (!isset($speciesStats[$esp])) {
                $speciesStats[$esp] = ['total' => 0, 'problematic' => 0];
            }
            $speciesStats[$esp]['total']++;

            // Vérifier s'il a des examens "non sains"
            $hasIssues = false;
            foreach ($animal->getExamen() as $ex) {
                if ($ex->getDiagnostic() !== 'En bonne santé') {
                    $hasIssues = true;
                    break;
                }
            }
            if ($hasIssues) {
                $speciesStats[$esp]['problematic']++;
            }
        }

        // 2. Formater le résultat final
        $result = [];
        foreach ($speciesStats as $espece => $data) {
            $percent = $data['total'] > 0 ? round(($data['problematic'] / $data['total']) * 100) : 0;
            $result[] = [
                'espece' => $espece,
                'total' => $data['total'],
                'malades' => $data['problematic'],
                'percent' => $percent
            ];
        }

        return $result;
    }
}
