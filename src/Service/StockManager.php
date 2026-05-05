<?php

namespace App\Service;

use App\Entity\stocks\Article;
use InvalidArgumentException;

class StockManager
{
    /**
     * Valide qu'un article respecte les règles métier sur la quantité et le prix.
     *
     * @param Article $article
     * @throws InvalidArgumentException Si une des règles métier n'est pas respectée
     */
    public function validateArticle(Article $article): void
    {
        if ($article->getQuantiteEnStock() !== null && $article->getQuantiteEnStock() < 0) {
            throw new InvalidArgumentException("Stop, c'est impossible ! La quantité ne peut pas être négative.");
        }

        if ($article->getPrixUnitaire() !== null && $article->getPrixUnitaire() == 0) {
            throw new InvalidArgumentException("Erreur, un article doit avoir un prix supérieur à 0 DT.");
        }
    }
}
