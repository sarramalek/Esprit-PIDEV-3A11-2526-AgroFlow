<?php

namespace App\Entity\User;

use App\Repository\User\OffreRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OffreRepository::class)]
#[ORM\Table(name: 'offres')]
class Offre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $idOffres = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\NotBlank(message: 'Veuillez entrer un nom.')]
    private ?string $nomOffre = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Assert\Positive(message: 'Le prix doit être positif.')]
    private ?float $prix = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\Positive(message: 'La durée doit être positive.')]
    private ?int $dureeOffre = null;

    // ==================== GETTERS & SETTERS ====================

    public function getIdOffres(): ?int { return $this->idOffres; }

    public function getNomOffre(): ?string { return $this->nomOffre; }
    public function setNomOffre(?string $nomOffre): self { $this->nomOffre = $nomOffre; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }

    public function getPrix(): ?float { return $this->prix; }
    public function setPrix(?float $prix): self { $this->prix = $prix; return $this; }

    public function getDureeOffre(): ?int { return $this->dureeOffre; }
    public function setDureeOffre(?int $dureeOffre): self { $this->dureeOffre = $dureeOffre; return $this; }
}