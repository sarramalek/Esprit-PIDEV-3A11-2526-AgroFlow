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
    $dateNaissance = $animal->getDateNaissance();
    $dateExamen = $examen->getDateExamen();

    if ($dateNaissance !== null && $dateExamen !== null) {
        if ($dateExamen->getTimestamp() < $dateNaissance->getTimestamp()) {
            throw new \InvalidArgumentException(
                'La date de l\'examen ne peut pas être antérieure à la date de naissance de l\'animal'
            );
        }
    }

    return true;
}
}