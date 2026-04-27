<?php

namespace App\Tests\Unit\Entity\Terrain;

use App\Entity\Terrain\Plante;
use App\Entity\Terrain\Rotation;
use App\Entity\Terrain\Terrain;
use PHPUnit\Framework\TestCase;

class RotationTest extends TestCase
{
    private Rotation $rotation;

    protected function setUp(): void
    {
        $this->rotation = new Rotation();
    }

    public function testIdEstNullParDefaut(): void
    {
        self::assertNull($this->rotation->getId());
    }

    public function testStatusParDefautEst1(): void
    {
        self::assertSame(1, $this->rotation->getStatus());
    }

    public function testSetAndGetStatus(): void
    {
        $this->rotation->setStatus(2);
        self::assertSame(2, $this->rotation->getStatus());
    }

    public function testSetStatusRetourneStaticPourChaining(): void
    {
        $result = $this->rotation->setStatus(3);
        self::assertInstanceOf(Rotation::class, $result);
    }

    public function testTerrainEstNullParDefaut(): void
    {
        self::assertNull($this->rotation->getTerrain());
    }

    public function testSetAndGetTerrain(): void
    {
        $terrain = new Terrain();
        $this->rotation->setTerrain($terrain);
        self::assertSame($terrain, $this->rotation->getTerrain());
    }

    public function testSetTerrainAccepteNull(): void
    {
        $this->rotation->setTerrain(new Terrain());
        $this->rotation->setTerrain(null);
        self::assertNull($this->rotation->getTerrain());
    }

    public function testPlanteEstNullParDefaut(): void
    {
        self::assertNull($this->rotation->getPlante());
    }

    public function testSetAndGetPlante(): void
    {
        $plante = (new Plante())->setNomP('Tomate');
        $this->rotation->setPlante($plante);
        self::assertSame($plante, $this->rotation->getPlante());
    }

    public function testSetPlanteAccepteNull(): void
    {
        $this->rotation->setPlante(new Plante());
        $this->rotation->setPlante(null);
        self::assertNull($this->rotation->getPlante());
    }

    public function testDatesSontNullParDefaut(): void
    {
        self::assertNull($this->rotation->getDateDebut());
        self::assertNull($this->rotation->getDateFin());
    }

    public function testSetAndGetDates(): void
    {
        $debut = new \DateTimeImmutable('2026-01-10');
        $fin = new \DateTimeImmutable('2026-02-10');

        $this->rotation->setDateDebut($debut);
        $this->rotation->setDateFin($fin);

        self::assertSame($debut, $this->rotation->getDateDebut());
        self::assertSame($fin, $this->rotation->getDateFin());
    }

    public function testChainingDesSetters(): void
    {
        $terrain = new Terrain();
        $plante = (new Plante())->setNomP('Carotte');
        $debut = new \DateTimeImmutable('2026-03-01');
        $fin = new \DateTimeImmutable('2026-04-01');

        $result = $this->rotation
            ->setTerrain($terrain)
            ->setPlante($plante)
            ->setDateDebut($debut)
            ->setDateFin($fin)
            ->setStatus(2);

        self::assertInstanceOf(Rotation::class, $result);
        self::assertSame($terrain, $this->rotation->getTerrain());
        self::assertSame($plante, $this->rotation->getPlante());
        self::assertSame($debut, $this->rotation->getDateDebut());
        self::assertSame($fin, $this->rotation->getDateFin());
        self::assertSame(2, $this->rotation->getStatus());
    }
}

