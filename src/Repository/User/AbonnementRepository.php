<?php

namespace App\Repository\User;

use App\Entity\User\Abonnement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Abonnement>
 */
class AbonnementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Abonnement::class);
    }

    /**
     * Retourne tous les abonnements actifs (situation = 'actif')
     */
    /**
     * @return array<int, Abonnement>
     */
    public function findActifs(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.situation = :situation')
            ->setParameter('situation', 'actif')
            ->orderBy('a.dateInscription', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne les abonnements d'un utilisateur par son CIN
     */
    /**
     * @return array<int, Abonnement>
     */
    public function findByCin(int $cin): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.cin = :cin')
            ->setParameter('cin', $cin)
            ->orderBy('a.dateInscription', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne les abonnements liés à une offre
     */
    /**
     * @return array<int, Abonnement>
     */
    public function findByOffre(int $idOffre): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.idOffre = :idOffre')
            ->setParameter('idOffre', $idOffre)
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne les abonnements expirés (date_expiration < aujourd'hui)
     */
    /**
     * @return array<int, Abonnement>
     */
    public function findExpires(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.dateExpiration < :today')
            ->setParameter('today', new \DateTime())
            ->getQuery()
            ->getResult();
    }
}