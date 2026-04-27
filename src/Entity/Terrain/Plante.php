<?php
namespace App\Entity\Terrain;

use App\Repository\Terrain\PlanteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlanteRepository::class)]
#[ORM\Table(name: 'plante')]
class Plante
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_plante')]
    private ?int $id = null;

    #[ORM\Column(name: 'nom_p', length: 100)]
    private string $nomP = '';

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $variete = null;

    #[ORM\Column(name: 'besoin_eau', type: 'float', nullable: true)]
    private ?float $besoinEau = null;

    #[ORM\Column(name: 'cycle_jours', type: 'integer', nullable: true)]
    private ?int $cycleJours = null;

    public function getId(): ?int { return $this->id; }
    public function getNomP(): string { return $this->nomP; }
    public function setNomP(string $v): static { $this->nomP = $v; return $this; }
    public function getVariete(): ?string { return $this->variete; }
    public function setVariete(?string $v): static { $this->variete = $v; return $this; }
    public function getBesoinEau(): ?float { return $this->besoinEau; }
    public function setBesoinEau(?float $v): static { $this->besoinEau = $v; return $this; }
    public function getCycleJours(): ?int { return $this->cycleJours; }
    public function setCycleJours(?int $v): static { $this->cycleJours = $v; return $this; }
    public function __toString(): string { return $this->nomP; }
}