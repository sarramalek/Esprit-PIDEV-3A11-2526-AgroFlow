<?php

namespace App\Tests\Entity\Materiels;

use App\Entity\Materiels\Maintenance;
use PHPUnit\Framework\TestCase;

class MaintenanceTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $maintenance = new Maintenance();

        self::assertNull($maintenance->getIdMain());
        self::assertNull($maintenance->getId());
        self::assertSame('planifie', $maintenance->getStatut());
        self::assertSame('moyenne', $maintenance->getPriorite());
    }

    public function testGettersAndSetters(): void
    {
        $maintenance = new Maintenance();
        $dateMain = new \DateTimeImmutable('2025-02-14');

        $maintenance
            ->setIdMain(42)
            ->setTypePanne('Moteur')
            ->setCout(350.5)
            ->setDateMain($dateMain)
            ->setDescription('Remplacement de la courroie')
            ->setIdM(7)
            ->setStatut('en_cours')
            ->setRecommandation('Verifier le filtre a huile')
            ->setPriorite('haute')
            ->setKilometrage(120000)
            ->setNom('Tracteur X');

        self::assertSame(42, $maintenance->getIdMain());
        self::assertSame(42, $maintenance->getId());
        self::assertSame('Moteur', $maintenance->getTypePanne());
        self::assertSame(350.5, $maintenance->getCout());
        self::assertSame($dateMain, $maintenance->getDateMain());
        self::assertSame('Remplacement de la courroie', $maintenance->getDescription());
        self::assertSame(7, $maintenance->getIdM());
        self::assertSame('en_cours', $maintenance->getStatut());
        self::assertSame('Verifier le filtre a huile', $maintenance->getRecommandation());
        self::assertSame('haute', $maintenance->getPriorite());
        self::assertSame(120000, $maintenance->getKilometrage());
        self::assertSame('Tracteur X', $maintenance->getNom());
        self::assertSame('Tracteur X', $maintenance->getNomMateriel());
    }
}
