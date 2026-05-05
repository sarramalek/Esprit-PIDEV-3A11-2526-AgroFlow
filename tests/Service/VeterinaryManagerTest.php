<?php

namespace App\Tests\Service;

use App\Entity\Animals\Animaux;
use App\Entity\Animals\Examen;
use App\Service\Animals\VeterinaryManager;
use PHPUnit\Framework\TestCase;

class VeterinaryManagerTest extends TestCase
{
    public function testValidAnimal()
    {
        $animal = new Animaux();
        $animal->setNom('Rex');
        $animal->setPoids(15.5);

        $manager = new VeterinaryManager();
        $this->assertTrue($manager->validateAnimal($animal));
    }

    public function testAnimalWithNegativeWeight()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le poids de l\'animal doit être strictement positif');

        $animal = new Animaux();
        $animal->setNom('Rex');
        $animal->setPoids(-5.0);

        $manager = new VeterinaryManager();
        $manager->validateAnimal($animal);
    }

    public function testValidExamen()
    {
        $animal = new Animaux();
        $animal->setDateNaissance(new \DateTime('2022-01-01'));

        $examen = new Examen();
        $examen->setAnimal($animal);
        $examen->setDateExamen(new \DateTime('2023-01-01'));

        $manager = new VeterinaryManager();
        $this->assertTrue($manager->validateExamen($examen));
    }

    public function testExamenBeforeBirthDate()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La date de l\'examen ne peut pas être antérieure à la date de naissance de l\'animal');

        $animal = new Animaux();
        $animal->setDateNaissance(new \DateTime('2022-01-01'));

        $examen = new Examen();
        $examen->setAnimal($animal);
        $examen->setDateExamen(new \DateTime('2021-01-01'));

        $manager = new VeterinaryManager();
        $manager->validateExamen($examen);
    }
}
