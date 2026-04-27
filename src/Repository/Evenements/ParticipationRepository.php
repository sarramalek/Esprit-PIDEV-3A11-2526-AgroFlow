<?php

namespace App\Repository\Evenements;

use App\Entity\Evenements\Participation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Participation>
 */
class ParticipationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participation::class);
    }

    /**
     * Récupère toutes les participations avec les relations
     * 
     * @return array<int, Participation>
     */
    public function findAll(): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.evenement', 'e')
            ->leftJoin('p.utilisateur', 'u')
            ->addSelect('e', 'u')
            ->orderBy('p.dateInscription', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Filtre les participations selon les critères
     * 
     * @return array<int, Participation>
     */
    public function findByFilters(
        ?string $search = null,
        ?string $dateInscription = null,
        ?string $statut = null,
        ?string $presence = null,
        ?string $userId = null
    ): array {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.evenement', 'e')
            ->leftJoin('p.utilisateur', 'u')
            ->addSelect('e', 'u');

        if (!empty($search)) {
            $qb->andWhere('LOWER(e.titre) LIKE LOWER(:search)')
               ->setParameter('search', '%' . $search . '%');
        }

        if (!empty($dateInscription)) {
            $qb->andWhere('p.dateInscription = :date')
               ->setParameter('date', new \DateTime($dateInscription));
        }

        if (!empty($statut) && $statut !== 'Tous les statuts') {
            $qb->andWhere('p.statutParticipation = :statut')
               ->setParameter('statut', $statut);
        }

        if (!empty($presence) && $presence !== 'Toutes') {
            $qb->andWhere('p.presence = :presence')
               ->setParameter('presence', $presence === 'Oui');
        }

        if (!empty($userId)) {
            if (ctype_digit($userId)) {
                $qb->andWhere('u.cin = :cin')
                   ->setParameter('cin', (int)$userId);
            } else {
                $qb->andWhere('LOWER(u.nom) LIKE LOWER(:nom) OR LOWER(u.prenom) LIKE LOWER(:prenom)')
                   ->setParameter('nom', '%' . $userId . '%')
                   ->setParameter('prenom', '%' . $userId . '%');
            }
        }

        return $qb->orderBy('p.dateInscription', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

    public function save(Participation $participation, bool $flush = true): void
    {
        $this->getEntityManager()->persist($participation);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Participation $participation, bool $flush = true): void
    {
        $this->getEntityManager()->remove($participation);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.idParticipation)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}