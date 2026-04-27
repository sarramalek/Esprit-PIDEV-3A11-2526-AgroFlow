<?php

namespace App\Repository\User;

use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    // ==================== FIND BY EMAIL ====================
    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    // ==================== FIND BY ROLE ====================
    /**
     * @return array<int, User>
     */
    public function findByRole(int $role): array
    {
        return $this->findBy(['role' => $role]);
    }

    // ==================== FIND ALL OUVRIERS ====================
    /**
     * @return array<int, User>
     */
    public function findAllOuvriers(): array
    {
        return $this->findBy(['role' => 1]);
    }

    // ==================== FIND ALL AGRICULTEURS ====================
    /**
     * @return array<int, User>
     */
    public function findAllAgriculteurs(): array
    {
        return $this->findBy(['role' => 2]);
    }

    // ==================== FIND ALL ADMINS ====================
    /**
     * @return array<int, User>
     */
    public function findAllAdmins(): array
    {
        return $this->findBy(['role' => 3]);
    }

    // ==================== FIND BY CIN ====================
    public function findByCin(int $cin): ?User
    {
        return $this->findOneBy(['cin' => $cin]);
    }

    // ==================== FIND BY VILLE ====================
    /**
     * @return array<int, User>
     */
    public function findByVille(string $ville): array
    {
        return $this->findBy(['ville' => $ville]);
    }

    // ==================== SEARCH (nom, prenom, email) ====================
    /**
     * @return array<int, User>
     */
    public function search(string $query): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.nom LIKE :q OR u.prenom LIKE :q OR u.email LIKE :q')
            ->setParameter('q', '%' . $query . '%')
            ->orderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // ==================== FIND RECENT USERS ====================
    /**
     * @return array<int, User>
     */
    public function findRecentUsers(int $limit = 10): array
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.dateCreationcpt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    // ==================== COUNT BY ROLE ====================
    public function countByRole(int $role): int
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.cin)')
            ->where('u.role = :role')
            ->setParameter('role', $role)
            ->getQuery()
            ->getSingleScalarResult();
    }

    // ==================== SAVE ====================
    public function save(User $user, bool $flush = false): void
    {
        $this->getEntityManager()->persist($user);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    // ==================== REMOVE ====================
    public function remove(User $user, bool $flush = false): void
    {
        $this->getEntityManager()->remove($user);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    // ==================== COUNT BY ROLE AND MONTH ====================
public function countByRoleAndMonth(int $role, \DateTime $date): int
{
    $start = (clone $date)->modify('first day of this month')->setTime(0, 0, 0);
    $end   = (clone $date)->modify('last day of this month')->setTime(23, 59, 59);

    return (int) $this->createQueryBuilder('u')
        ->select('COUNT(u.cin)')
        ->andWhere('u.role = :role')
        ->andWhere('u.dateCreationcpt BETWEEN :start AND :end')
        ->setParameter('role', $role)
        ->setParameter('start', $start)
        ->setParameter('end', $end)
        ->getQuery()
        ->getSingleScalarResult();
}

public function countByMonth(\DateTime $date): int
{
    $start = (clone $date)->modify('first day of this month')->setTime(0, 0, 0);
    $end   = (clone $date)->modify('last day of this month')->setTime(23, 59, 59);

    return (int) $this->createQueryBuilder('u')
        ->select('COUNT(u.cin)')
        ->andWhere('u.dateCreationcpt BETWEEN :start AND :end')
        ->setParameter('start', $start)
        ->setParameter('end', $end)
        ->getQuery()
        ->getSingleScalarResult();
}
// src/Repository/UserRepository.php (ou le nom de votre repo User)

    /**
     * @return array<int, array{cin: int, nom: string, prenom: string}>
     */
public function findAllForSelect(): array
{
    return $this->createQueryBuilder('u')
        ->select('u.cin, u.nom, u.prenom')
        ->where('u.cin IS NOT NULL')
        ->orderBy('u.nom', 'ASC')
        ->getQuery()
        ->getResult();
}

 
 
    /**
     * Ouvriers qui travaillent sur les terrains d'un agriculteur donné.
     * Ce sont les ouvriers que l'agriculteur "possède".
     *
     * @return array<int, User>
     */
    public function findOuvriersByAgriculteur(int $cinAgriculteur): array
    {
        return $this->createQueryBuilder('u')
            ->join('u.terrain', 't')
            ->where('u.role = 1')
            ->andWhere('t.cin = :cin')
            ->setParameter('cin', $cinAgriculteur)
            ->orderBy('t.nomTerrain', 'ASC')
            ->addOrderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
 
    /**
     * Ouvriers assignés à un terrain précis.
     *
     * @return array<int, User>
     */
    public function findOuvriersByTerrain(int $idTerrain): array
    {
        return $this->createQueryBuilder('u')
            ->join('u.terrain', 't')
            ->where('u.role = 1')
            ->andWhere('t.id = :id')
            ->setParameter('id', $idTerrain)
            ->orderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
 
    /**
     * Ouvriers sans terrain (disponibles pour être assignés).
     *
     * @return array<int, User>
     */
    public function findOuvriersDisponibles(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.role = 1')
            ->andWhere('u.terrain IS NULL')
            ->orderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}