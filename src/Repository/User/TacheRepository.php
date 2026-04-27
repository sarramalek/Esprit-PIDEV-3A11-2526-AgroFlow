<?php

namespace App\Repository\User;

use App\Entity\User\Tache;
use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tache>
 */
class TacheRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tache::class);
    }

    // ==================== FIND BY ASSIGNEE ====================
    /**
     * @return array<int, Tache>
     */
    public function findByAssignee(User $user): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.assignee = :user')
            ->setParameter('user', $user)
            ->orderBy('t.dateEcheancee', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // ==================== FIND BY ETAT ====================
    /**
     * @return array<int, Tache>
     */
    public function findByEtat(string $etat): array
    {
        return $this->findBy(['etat' => $etat]);
    }

    // ==================== FIND BY PRIORITE ====================
    /**
     * @return array<int, Tache>
     */
    public function findByPriorite(string $priorite): array
    {
        return $this->findBy(['priorite' => $priorite]);
    }

    // ==================== FIND BY ASSIGNEE AND ETAT ====================
    /**
     * @return array<int, Tache>
     */
    public function findByAssigneeAndEtat(User $user, string $etat): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.assignee = :user')
            ->andWhere('t.etat = :etat')
            ->setParameter('user', $user)
            ->setParameter('etat', $etat)
            ->orderBy('t.dateEcheancee', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // ==================== FIND TACHES EN RETARD ====================
    /**
     * @return array<int, Tache>
     */
    public function findTachesEnRetard(): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.dateEcheancee < :today')
            ->andWhere('t.etat != :done')
            ->setParameter('today', new \DateTime())
            ->setParameter('done', 'terminée')
            ->orderBy('t.dateEcheancee', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // ==================== FIND TACHES EN RETARD BY ASSIGNEE ====================
    /**
     * @return array<int, Tache>
     */
    public function findTachesEnRetardByAssignee(User $user): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.assignee = :user')
            ->andWhere('t.dateEcheancee < :today')
            ->andWhere('t.etat != :done')
            ->setParameter('user', $user)
            ->setParameter('today', new \DateTime())
            ->setParameter('done', 'terminée')
            ->orderBy('t.dateEcheancee', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // ==================== SEARCH ====================
    /**
     * @return array<int, Tache>
     */
    public function search(string $query): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.nomTache LIKE :q OR t.description LIKE :q')
            ->setParameter('q', '%' . $query . '%')
            ->orderBy('t.nomTache', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // ==================== COUNT BY ETAT ====================
    public function countByEtat(string $etat): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.idTache)')
            ->where('t.etat = :etat')
            ->setParameter('etat', $etat)
            ->getQuery()
            ->getSingleScalarResult();
    }

    // ==================== COUNT BY PRIORITE ====================
    public function countByPriorite(string $priorite): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.idTache)')
            ->where('t.priorite = :priorite')
            ->setParameter('priorite', $priorite)
            ->getQuery()
            ->getSingleScalarResult();
    }

    // ==================== COUNT BY ASSIGNEE ====================
    public function countByAssignee(User $user): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.idTache)')
            ->where('t.assignee = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    // ==================== SAVE ====================
    public function save(Tache $tache, bool $flush = false): void
    {
        $this->getEntityManager()->persist($tache);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    // ==================== REMOVE ====================
    public function remove(Tache $tache, bool $flush = false): void
    {
        $this->getEntityManager()->remove($tache);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    // ==================== SEARCH AND SORT ====================
    /**
     * @return array<int, Tache>
     */
    public function searchAndSort(string $search, string $sort, string $direction): array
{
    $qb = $this->createQueryBuilder('t')
        ->leftJoin('t.assignee', 'u');

    if ($search) {
        $qb->where(
            $qb->expr()->orX(
                $qb->expr()->like('t.nomTache', ':q'),
                $qb->expr()->like('t.description', ':q'),
                $qb->expr()->like('t.etat', ':q'),
                $qb->expr()->like('t.priorite', ':q'),
                $qb->expr()->like('u.nom', ':q'),
                $qb->expr()->like('u.prenom', ':q')
            )
        )->setParameter('q', '%' . $search . '%');
    }

    $qb->orderBy('t.' . $sort, $direction);

    return $qb->getQuery()->getResult();
}
// Tâches sans assignee ou à l'état "à faire"
    /**
     * @return array<int, Tache>
     */
public function findTachesAAssigner(): array
{
    return $this->createQueryBuilder('t')
        ->where('t.assignee IS NULL')
        ->orWhere('t.etat = :etat')
        ->setParameter('etat', 'à faire')
        ->andWhere('t.etat != :terminee')
        ->setParameter('terminee', 'terminée')
        ->getQuery()
        ->getResult();
}

// Tâches en conflit pour un ouvrier sur une période
    /**
     * @param mixed $ouvrier
     * @return array<int, Tache>
     */
public function findTachesConflict($ouvrier, \DateTime $debut, \DateTime $fin): array
{
    return $this->createQueryBuilder('t')
        ->where('t.assignee = :ouvrier')
        ->andWhere('t.etat NOT IN (:etats)')
        ->andWhere('t.dateEcheancee >= :debut')
        ->andWhere('t.dateEcheancee <= :fin')
        ->setParameter('ouvrier', $ouvrier)
        ->setParameter('etats', ['terminée', 'annulée'])
        ->setParameter('debut', $debut)
        ->setParameter('fin', $fin)
        ->getQuery()
        ->getResult();
}
// src/Repository/User/TacheRepository.php

// Compte les tâches actives (non terminées) d'un ouvrier
public function countTachesActives(User $ouvrier): int
{
    return (int) $this->createQueryBuilder('t')
        ->select('COUNT(t.idTache)')
        ->andWhere('t.assignee = :ouvrier')
        ->andWhere('t.etat != :done')
        ->setParameter('ouvrier', $ouvrier)
        ->setParameter('done', 'terminée')
        ->getQuery()
        ->getSingleScalarResult();
}

// Vérifie si un ouvrier a une tâche qui chevauche la date donnée
public function hasConflitDate(User $ouvrier, \DateTime $date): bool
{
    $count = (int) $this->createQueryBuilder('t')
        ->select('COUNT(t.idTache)')
        ->andWhere('t.assignee = :ouvrier')
        ->andWhere('t.dateEcheancee = :date')
        ->andWhere('t.etat != :done')
        ->setParameter('ouvrier', $ouvrier)
        ->setParameter('date', $date->format('Y-m-d'))
        ->setParameter('done', 'terminée')
        ->getQuery()
        ->getSingleScalarResult();

    return $count > 0;
}
}