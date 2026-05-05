<?php

namespace App\Service\Animals;

use App\Entity\Animals\Animaux;
use App\Entity\Animals\Examen;

class VeterinaryManager
{
    public function validateAnimal(Animaux $animal): bool
    {
        if ($animal->getPoids() !== null && $animal->getPoids() <= 0) {
            throw new \InvalidArgumentException('Le poids de l\'animal doit être strictement positif');
        }

        return true;
    }

    public function validateExamen(Examen $examen): bool
    {
        $animal = $examen->getAnimal();
        
        if (!$animal) {
            throw new \InvalidArgumentException('L\'examen doit être associé à un animal');
        }

        if ($examen->getDateExamen() && $animal->getDateNaissance()) {
            if ($examen->getDateExamen() < $animal->getDateNaissance()) {
                throw new \InvalidArgumentException('La date de l\'examen ne peut pas être antérieure à la date de naissance de l\'animal');
            }
        }

        return true;
    }
}
