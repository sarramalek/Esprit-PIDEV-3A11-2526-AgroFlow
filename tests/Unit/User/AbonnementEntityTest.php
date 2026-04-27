<?php

namespace App\Tests\Unit\User;

use App\Entity\User\Abonnement;
use PHPUnit\Framework\TestCase;

final class AbonnementEntityTest extends TestCase
{
    public function testSettersAndGetters(): void
    {
        $abonnement = new Abonnement();
        $dateInscription = new \DateTimeImmutable('2026-04-01');
        $dateExpiration = new \DateTimeImmutable('2026-05-01');

        $abonnement->setCin(12345678)
            ->setIdOffre(7)
            ->setDateInscription($dateInscription)
            ->setDateExpiration($dateExpiration)
            ->setSituation('actif')
            ->setPrixPaye(49.9);

        $this->assertSame(12345678, $abonnement->getCin());
        $this->assertSame(7, $abonnement->getIdOffre());
        $this->assertSame('actif', $abonnement->getSituation());
        $this->assertSame(49.9, $abonnement->getPrixPaye());
        $this->assertSame('2026-04-01', $abonnement->getDateInscription()?->format('Y-m-d'));
        $this->assertSame('2026-05-01', $abonnement->getDateExpiration()?->format('Y-m-d'));
    }
}
