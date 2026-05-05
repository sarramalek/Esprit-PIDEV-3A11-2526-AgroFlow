<?php

namespace App\Tests\Service;

use App\Entity\stocks\Article;
use App\Service\StockManager;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class StockManagerTest extends TestCase
{
    private StockManager $stockManager;

    protected function setUp(): void
    {
        $this->stockManager = new StockManager();
    }

    public function testValidateArticleRejetteQuantiteNegative(): void
    {
        $article = new Article();
        $article->setQuantiteEnStock(-10);
        $article->setPrixUnitaire(15.0);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Stop, c'est impossible ! La quantité ne peut pas être négative.");

        $this->stockManager->validateArticle($article);
    }

    public function testValidateArticleRejettePrixZero(): void
    {
        $article = new Article();
        $article->setQuantiteEnStock(50);
        $article->setPrixUnitaire(0.0);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Erreur, un article doit avoir un prix supérieur à 0 DT.");

        $this->stockManager->validateArticle($article);
    }

    public function testValidateArticleAccepteArticleValide(): void
    {
        $article = new Article();
        $article->setQuantiteEnStock(100);
        $article->setPrixUnitaire(25.5);

        // Si aucune exception n'est lancée, le test passe.
        $this->stockManager->validateArticle($article);
        $this->assertTrue(true);
    }
}
