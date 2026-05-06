<?php

namespace App\Tests\Unit\Entity\Terrain;

use App\Entity\Terrain\Terrain;
use App\Entity\User\User;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class TerrainTest extends TestCase
{
    private Terrain $terrain;

    protected function setUp(): void
    {
        $this->terrain = new Terrain();
    }

    public function testIdEstNullParDefaut(): void
    {
        self::assertNull($this->terrain->getId());
    }

    public function testCollectionsSontInitialisees(): void
    {
        self::assertInstanceOf(Collection::class, $this->terrain->getRotations());
        self::assertInstanceOf(Collection::class, $this->terrain->getOuvriers());
        self::assertCount(0, $this->terrain->getRotations());
        self::assertCount(0, $this->terrain->getOuvriers());
    }

    public function testSetAndGetNomTerrain(): void
    {
        $this->terrain->setNomTerrain('Parcelle A');
        self::assertSame('Parcelle A', $this->terrain->getNomTerrain());
    }

    public function testSetAndGetSurface(): void
    {
        $this->terrain->setSurface(12.5);
        self::assertSame(12.5, $this->terrain->getSurface());
    }

    public function testSetAndGetTypeSol(): void
    {
        $this->terrain->setTypeSol('Argileux');
        self::assertSame('Argileux', $this->terrain->getTypeSol());
    }

    public function testSetAndGetLocalisation(): void
    {
        $this->terrain->setLocalisation('Tunis');
        self::assertSame('Tunis', $this->terrain->getLocalisation());
    }

    public function testSetAndGetPH(): void
    {
        $this->terrain->setPH(6.8);
        self::assertSame(6.8, $this->terrain->getPH());
    }

    public function testSetAndGetCin(): void
    {
        $this->terrain->setCin(12345678);
        self::assertSame(12345678, $this->terrain->getCin());
    }

    public function testAddOuvrierAjouteEtSetTerrain(): void
    {
        $ouvrier = new User();

        $this->terrain->addOuvrier($ouvrier);

        self::assertCount(1, $this->terrain->getOuvriers());
        self::assertTrue($this->terrain->getOuvriers()->contains($ouvrier));
        self::assertSame($this->terrain, $ouvrier->getTerrain());
    }

    public function testAddOuvrierNeDupliquePas(): void
    {
        $ouvrier = new User();

        $this->terrain->addOuvrier($ouvrier);
        $this->terrain->addOuvrier($ouvrier);

        self::assertCount(1, $this->terrain->getOuvriers());
    }

    public function testRemoveOuvrierSupprimeEtNullifieTerrain(): void
    {
        $ouvrier = new User();
        $this->terrain->addOuvrier($ouvrier);

        $this->terrain->removeOuvrier($ouvrier);

        self::assertCount(0, $this->terrain->getOuvriers());
        self::assertNull($ouvrier->getTerrain());
    }
}

