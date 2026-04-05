<?php

namespace App\Repository\stocks;

use App\Entity\stocks\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * @return Article[]
     */
    public function findBySearchCriteria(?string $search, ?string $categoryId): array
    {
        $qb = $this->createQueryBuilder('a');

        // Recherche par nom de produit
        if ($search) {
            $qb->andWhere('a.nom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Filtrage par catégorie
        if ($categoryId && $categoryId !== '') {
            // On fait une jointure avec la catégorie pour être plus performant
            $qb->join('a.categorie', 'c')
                ->andWhere('c.id = :catId')
                ->setParameter('catId', $categoryId);
        }

        // On peut ajouter un tri par défaut (ex: les plus récents en premier)
        $qb->orderBy('a.nom', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
