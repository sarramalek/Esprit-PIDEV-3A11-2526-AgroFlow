<?php

namespace App\Entity\Materiels;

use App\Repository\Materiels\MaintenanceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MaintenanceRepository::class)]
#[ORM\Table(name: 'maintenance')]
class Maintenance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idMain', type: 'integer')]
    private ?int $idMain = null;

    #[ORM\Column(name: 'typePanne', length: 255)]
    private ?string $typePanne = null;

    #[ORM\Column(name: 'cout', type: 'float')]
    private ?float $cout = null;

    #[ORM\Column(name: 'dateMain', type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateMain = null;

    #[ORM\Column(name: 'description', length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'idM', type: 'integer', nullable: true)]
    private ?int $idM = null;

    // Propriété non persistée pour le nom de la machine
    private ?string $nom = null;

    // ====================== Getters & Setters ======================

    public function getIdMain(): ?int
    {
        return $this->idMain;
    }
        public function setIdMain(int $idMain): static
        {
            $this->idMain = $idMain;
            return $this;
        }
    public function getId(): ?int
    {
        return $this->idMain;
    }

    public function getTypePanne(): ?string
    {
        return $this->typePanne;
    }

    public function setTypePanne(string $typePanne): static
    {
        $this->typePanne = $typePanne;
        return $this;
    }

    public function getCout(): ?float
    {
        return $this->cout;
    }

    public function setCout(float $cout): static
    {
        $this->cout = $cout;
        return $this;
    }

    public function getDateMain(): ?\DateTimeInterface
    {
        return $this->dateMain;
    }

    public function setDateMain(?\DateTimeInterface $dateMain): static
    {
        $this->dateMain = $dateMain;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getIdM(): ?int
    {
        return $this->idM;
    }

    public function setIdM(?int $idM): static
    {
        $this->idM = $idM;
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }
}