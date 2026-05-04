<?php

namespace App\Tests\Unit\User;

use App\Entity\User\Offre;
use PHPUnit\Framework\TestCase;

final class OffreEntityTest extends TestCase
{
    public function testSettersAndGetters(): void
    {
        $offre = new Offre();
        $offre->setNomOffre('Pack Pro')
            ->setDescription('Offre premium')
            ->setPrix(120.5)
            ->setDureeOffre(30);

        $this->assertSame('Pack Pro', $offre->getNomOffre());
        $this->assertSame('Offre premium', $offre->getDescription());
        $this->assertSame(120.5, $offre->getPrix());
        $this->assertSame(30, $offre->getDureeOffre());
    }
}
