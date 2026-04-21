<?php
namespace App\Entity\Terrain;

use App\Repository\Terrain\TerrainRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\User\User;


#[ORM\Entity(repositoryClass: TerrainRepository::class)]
#[ORM\Table(name: 'terrain')]
class Terrain
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_terrain')]
    private ?int $id = null;

    #[ORM\Column(name: 'nom_terrain', length: 50, nullable: true)]
    private ?string $nomTerrain = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $surface = null;

    #[ORM\Column(name: 'type_sol', length: 200, nullable: true)]
    private ?string $typeSol = null;

    #[ORM\Column(length: 2000, nullable: true)]
    private ?string $localisation = null;

    #[ORM\Column(name: 'p_h', type: 'float', nullable: true)]
    private ?float $pH = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $cin = null;

    #[ORM\OneToMany(mappedBy: 'terrain', targetEntity: Rotation::class, cascade: ['remove'])]
    private Collection $rotations;
 #[ORM\OneToMany(mappedBy: 'terrain', targetEntity: User::class, cascade: ['persist'])]
    private Collection $ouvriers;
 
   
    public function __construct()
    {
        $this->rotations = new ArrayCollection();
        $this->ouvriers  = new ArrayCollection();   
    }

    public function getId(): ?int { return $this->id; }

    public function getNomTerrain(): ?string { return $this->nomTerrain; }
    public function setNomTerrain(?string $v): static { $this->nomTerrain = $v; return $this; }

    public function getSurface(): ?float { return $this->surface; }
    public function setSurface(?float $v): static { $this->surface = $v; return $this; }

    public function getTypeSol(): ?string { return $this->typeSol; }
    public function setTypeSol(?string $v): static { $this->typeSol = $v; return $this; }

    public function getLocalisation(): ?string { return $this->localisation; }
    public function setLocalisation(?string $v): static { $this->localisation = $v; return $this; }

    public function getPH(): ?float { return $this->pH; }
    public function setPH(?float $v): static { $this->pH = $v; return $this; }

    public function getCin(): ?int { return $this->cin; }
    public function setCin(?int $v): static { $this->cin = $v; return $this; }

    public function getRotations(): Collection { return $this->rotations; }
     public function getOuvriers(): Collection
    {
        return $this->ouvriers;
    }
 
    public function addOuvrier(User $ouvrier): self
    {
        if (!$this->ouvriers->contains($ouvrier)) {
            $this->ouvriers->add($ouvrier);
            $ouvrier->setTerrain($this);
        }
        return $this;
    }
 
    public function removeOuvrier(User $ouvrier): self
    {
        if ($this->ouvriers->removeElement($ouvrier)) {
            if ($ouvrier->getTerrain() === $this) {
                $ouvrier->setTerrain(null);
            }
        }
        return $this;
    }
}