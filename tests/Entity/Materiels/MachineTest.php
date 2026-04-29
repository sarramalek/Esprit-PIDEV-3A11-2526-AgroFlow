<?php

namespace App\Tests\Entity\Materiels;

use App\Entity\Materiels\Machine;
use App\Entity\User\User;
use PHPUnit\Framework\TestCase;

class MachineTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $machine = new Machine();
        $dateAchat = new \DateTimeImmutable('2024-01-15');
        $dateLastVisite = new \DateTimeImmutable('2024-06-10');
        $prochaineMaintenance = new \DateTimeImmutable('2024-12-01');

        $machine
            ->setNom('Tracteur X')
            ->setMarque('John Deere')
            ->setModele('5075E')
            ->setNumeroSerie('JD-ABC-123')
            ->setEtatM('Bon')
            ->setDateAchat($dateAchat)
            ->setKilometrage(15000)
            ->setDateLastVisite($dateLastVisite)
            ->setKmLastVisite(14000)
            ->setProchaineMaintenance($prochaineMaintenance);

        self::assertSame('Tracteur X', $machine->getNom());
        self::assertSame('John Deere', $machine->getMarque());
        self::assertSame('5075E', $machine->getModele());
        self::assertSame('JD-ABC-123', $machine->getNumeroSerie());
        self::assertSame('Bon', $machine->getEtatM());
        self::assertSame($dateAchat, $machine->getDateAchat());
        self::assertSame(15000, $machine->getKilometrage());
        self::assertSame($dateLastVisite, $machine->getDateLastVisite());
        self::assertSame(14000, $machine->getKmLastVisite());
        self::assertSame($prochaineMaintenance, $machine->getProchaineMaintenance());
    }

    public function testAgriculteurDerivedFields(): void
    {
        $user = (new User())
            ->setCin(12345678)
            ->setNom('Dupont')
            ->setPrenom('Marie');

        $machine = (new Machine())->setAgriculteur($user);

        self::assertSame($user, $machine->getAgriculteur());
        self::assertSame(12345678, $machine->getCin());
        self::assertSame(12345678, $machine->getCinAgriculteur());
        self::assertSame('Dupont Marie', $machine->getNomAgriculteur());
    }

    public function testNomAgriculteurFallbackWhenNoUser(): void
    {
        $machine = new Machine();

        self::assertNull($machine->getAgriculteur());
        self::assertNull($machine->getCin());
        self::assertNull($machine->getCinAgriculteur());
        self::assertSame('—', $machine->getNomAgriculteur());
    }
}
