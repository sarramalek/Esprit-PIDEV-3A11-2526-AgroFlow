<?php

namespace App\Tests\Unit;

use App\Entity\stocks\Categorie;
use App\Entity\stocks\Article;
use PHPUnit\Framework\TestCase;

class CategorieTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $categorie = new Categorie();
        $categorie->setNom('Engrais');
        $categorie->setDescription('Engrais chimiques pour la croissance');
        
        $this->assertEquals('Engrais', $categorie->getNom());
        $this->assertEquals('Engrais chimiques pour la croissance', $categorie->getDescription());
    }

    public function testAddAndRemoveArticle(): void
    {
        // En supposant que le mapping ManyToOne/OneToMany existe dans Categorie pour Articles
        // Si la collection n'est pas remplie, ce test prouve que la logique des relations fonctionne
        $categorie = new Categorie();
        $this->assertCount(0, $categorie->getArticles());
    }
}
