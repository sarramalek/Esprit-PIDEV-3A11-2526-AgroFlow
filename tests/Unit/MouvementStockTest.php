<?php

namespace App\Tests\Unit;

use App\Entity\stocks\MouvementStock;
use App\Entity\stocks\Article;
use PHPUnit\Framework\TestCase;

class MouvementStockTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $mouvement = new MouvementStock();
        $mouvement->setType('ENTREE');
        $mouvement->setQuantite(50);
        $mouvement->setMotif('Achat fournisseur');
        
        $date = new \DateTimeImmutable();
        $mouvement->setDateMouvement($date);

        $this->assertEquals('ENTREE', $mouvement->getType());
        $this->assertEquals(50, $mouvement->getQuantite());
        $this->assertEquals('Achat fournisseur', $mouvement->getMotif());
        $this->assertEquals($date, $mouvement->getDateMouvement());
    }

    public function testRelationAvecArticle(): void
    {
        $mouvement = new MouvementStock();
        $article = new Article();
        $article->setNom('Tracteur');
        
        $mouvement->setArticle($article);
        
        $this->assertInstanceOf(Article::class, $mouvement->getArticle());
        $this->assertEquals('Tracteur', $mouvement->getArticle()->getNom());
    }
}
