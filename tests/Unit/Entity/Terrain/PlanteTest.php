<?php

namespace App\Tests\Unit\Entity\Terrain;

use App\Entity\Terrain\Plante;
use PHPUnit\Framework\TestCase;

class PlanteTest extends TestCase
{
    private Plante $plante;

    protected function setUp(): void
    {
        $this->plante = new Plante();
    }

    public function testIdEstNullParDefaut(): void
    {
        self::assertNull($this->plante->getId());
    }

    public function testSetAndGetNomP(): void
    {
        $this->plante->setNomP('Tomate');
        self::assertSame('Tomate', $this->plante->getNomP());
    }

    public function testNomPEstNullParDefaut(): void
    {
        self::assertNull($this->plante->getNomP());
    }

    public function testSetNomPRetourneStaticPourChaining(): void
    {
        $result = $this->plante->setNomP('Blé');
        self::assertInstanceOf(Plante::class, $result);
    }

    public function testSetAndGetVariete(): void
    {
        $this->plante->setVariete('Roma');
        self::assertSame('Roma', $this->plante->getVariete());
    }

    public function testVarieteEstNullParDefaut(): void
    {
        self::assertNull($this->plante->getVariete());
    }

    public function testSetVarieteAccepteNull(): void
    {
        $this->plante->setVariete('Roma');
        $this->plante->setVariete(null);
        self::assertNull($this->plante->getVariete());
    }

    public function testSetVarieteRetourneStaticPourChaining(): void
    {
        $result = $this->plante->setVariete('Cerise');
        self::assertInstanceOf(Plante::class, $result);
    }

    public function testSetAndGetBesoinEau(): void
    {
        $this->plante->setBesoinEau(5.5);
        self::assertSame(5.5, $this->plante->getBesoinEau());
    }

    public function testBesoinEauEstNullParDefaut(): void
    {
        self::assertNull($this->plante->getBesoinEau());
    }

    public function testBesoinEauAccepteNull(): void
    {
        $this->plante->setBesoinEau(5.5);
        $this->plante->setBesoinEau(null);
        self::assertNull($this->plante->getBesoinEau());
    }

    public function testSetBesoinEauRetourneStaticPourChaining(): void
    {
        $result = $this->plante->setBesoinEau(2.0);
        self::assertInstanceOf(Plante::class, $result);
    }

    public function testSetAndGetCycleJours(): void
    {
        $this->plante->setCycleJours(90);
        self::assertSame(90, $this->plante->getCycleJours());
    }

    public function testCycleJoursEstNullParDefaut(): void
    {
        self::assertNull($this->plante->getCycleJours());
    }

    public function testCycleJoursAccepteNull(): void
    {
        $this->plante->setCycleJours(90);
        $this->plante->setCycleJours(null);
        self::assertNull($this->plante->getCycleJours());
    }

    public function testSetCycleJoursRetourneStaticPourChaining(): void
    {
        $result = $this->plante->setCycleJours(60);
        self::assertInstanceOf(Plante::class, $result);
    }

    public function testToStringRetourneNomP(): void
    {
        $this->plante->setNomP('Carotte');
        self::assertSame('Carotte', (string) $this->plante);
    }

    public function testToStringRetourneChaineVideSiNomPNull(): void
    {
        self::assertSame('', (string) $this->plante);
    }

    public function testChainingDesSetters(): void
    {
        $result = $this->plante
            ->setNomP('Blé')
            ->setVariete('Tendre')
            ->setBesoinEau(3.5)
            ->setCycleJours(180);

        self::assertInstanceOf(Plante::class, $result);
        self::assertSame('Blé', $this->plante->getNomP());
        self::assertSame(180, $this->plante->getCycleJours());
    }
}

