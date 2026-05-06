<?php

namespace App\Tests\Unit;

use App\Entity\stocks\Article;
use App\Entity\stocks\MouvementStock;
use PHPUnit\Framework\TestCase;

class ArticleTest extends TestCase
{
    public function testValeurTotaleStock(): void
    {
        $article = new Article();
        $article->setQuantiteEnStock(10);
        $article->setPrixUnitaire(5.5);

        $this->assertEquals(55.0, $article->getValeurTotaleStock());
    }

    public function testIsStockCritique(): void
    {
        $article = new Article();
        $article->setQuantiteEnStock(5);
        $article->setSeuilAlerte(10);

        $this->assertTrue($article->isStockCritique());

        $article->setQuantiteEnStock(15);
        $this->assertFalse($article->isStockCritique());
    }

    public function testAddAndRemoveMouvement(): void
    {
        $article = new Article();
        $mouvement = new MouvementStock();

        $article->addMouvement($mouvement);
        $this->assertCount(1, $article->getMouvements());
        $this->assertSame($article, $mouvement->getArticle());

        $article->removeMouvement($mouvement);
        $this->assertCount(0, $article->getMouvements());
$this->assertFalse($article->getMouvements()->contains($mouvement));
    }
}
