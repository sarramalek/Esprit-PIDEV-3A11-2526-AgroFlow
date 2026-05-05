<?php

namespace App\Tests\Unit\stocks;

use App\Entity\stocks\Article;
use PHPUnit\Framework\TestCase;

class ArticleTest extends TestCase
{
    public function testArticleData()
    {
        $article = new Article();
        $article->setNom("Urée 46");
        $article->setPrixAchatDevise(55.5);

        // On vérifie que le prix enregistré est bien 55.5
        $this->assertEquals(55.5, $article->getPrixAchatDevise());
    }
}
