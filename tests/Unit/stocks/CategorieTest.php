<?php

namespace App\Tests\Unit\stocks;

use App\Entity\stocks\Categorie;
use PHPUnit\Framework\TestCase;

class CategorieTest extends TestCase
{
    public function testCategorie()
    {
        $cat = new Categorie();
        $cat->setNom("Semences");
        $this->assertEquals("Semences", $cat->getNom());
    }
}
