<?php

namespace App\Tests\Unit\User;

use App\Entity\User\Tache;
use App\Entity\User\User;
use PHPUnit\Framework\TestCase;

final class TacheEntityTest extends TestCase
{
    public function testSettersAndGetters(): void
    {
        $tache = new Tache();
        $assignee = (new User())->setCin(98765432)->setNom('Worker');
        $echeance = new \DateTimeImmutable('2026-06-15');

        $tache->setNomTache('Irrigation')
            ->setDescription('Vérifier le système')
            ->setAssignee($assignee)
            ->setEtat('en cours')
            ->setPriorite('haute')
            ->setDateEcheancee($echeance);

        $this->assertSame('Irrigation', $tache->getNomTache());
        $this->assertSame('Vérifier le système', $tache->getDescription());
        $this->assertSame($assignee, $tache->getAssignee());
        $this->assertSame('en cours', $tache->getEtat());
        $this->assertSame('haute', $tache->getPriorite());
        $this->assertSame('2026-06-15', $tache->getDateEcheancee()?->format('Y-m-d'));
    }
}
