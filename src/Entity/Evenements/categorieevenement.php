<?php

namespace App\Entity\Evenements;

use App\Repository\Evenements\categorieevenementRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: categorieevenementRepository::class)]
#[ORM\Table(name: 'categorieevenement')]
class categorieevenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_categorie', type: 'integer')]
    private ?int $idCategorie = null;

    #[ORM\Column(name: 'nom_categorie', type: 'string', length: 100)]
    #[Assert\NotBlank(message: 'Le nom de la catégorie ne peut pas être vide !')]
    #[Assert\Length(
        min: 3,
        max: 100,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères !',
        maxMessage: 'Le nom ne doit pas dépasser {{ limit }} caractères !'
    )]
    private string $nomCategorie = '';

    #[ORM\Column(name: 'description', type: 'text')]
    #[Assert\NotBlank(message: 'La description ne peut pas être vide !')]
    #[Assert\Length(
        min: 10,
        max: 500,
        minMessage: 'La description doit contenir au moins {{ limit }} caractères !',
        maxMessage: 'La description ne doit pas dépasser {{ limit }} caractères !'
    )]
    private string $descriptionCategorie = '';

    // ================= GETTERS & SETTERS =================

    public function getIdCategorie(): ?int
    {
        return $this->idCategorie;
    }

    public function getNomCategorie(): ?string
    {
        return $this->nomCategorie;
    }

    public function setNomCategorie(string $nomCategorie): static
    {
        $this->nomCategorie = $nomCategorie;
        return $this;
    }

    public function getDescriptionCategorie(): ?string
    {
        return $this->descriptionCategorie;
    }

    public function setDescriptionCategorie(string $descriptionCategorie): static
    {
        $this->descriptionCategorie = $descriptionCategorie;
        return $this;
    }

    public function __toString(): string
    {
        return $this->nomCategorie;
    }
}