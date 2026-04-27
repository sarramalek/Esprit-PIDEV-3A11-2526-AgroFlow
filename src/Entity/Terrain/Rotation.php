<?php
namespace App\Entity\Terrain;

use App\Repository\Terrain\RotationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RotationRepository::class)]
#[ORM\Table(name: 'rotation')]
class Rotation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_rotation', type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Terrain::class, inversedBy: 'rotations')]
    #[ORM\JoinColumn(name: 'id_terrain', referencedColumnName: 'id_terrain', nullable: true)]
    private ?Terrain $terrain = null;

    #[ORM\ManyToOne(targetEntity: Plante::class)]
    #[ORM\JoinColumn(name: 'id_plante', referencedColumnName: 'id_plante', nullable: true)]
    private ?Plante $plante = null;

    #[ORM\Column(name: 'date_debut_t', type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(name: 'date_fin_t', type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private int $status = 1;

    public function getId(): ?int { return $this->id; }
    public function getTerrain(): ?Terrain { return $this->terrain; }
    public function setTerrain(?Terrain $v): static { $this->terrain = $v; return $this; }
    public function getPlante(): ?Plante { return $this->plante; }
    public function setPlante(?Plante $v): static { $this->plante = $v; return $this; }
    public function getDateDebut(): ?\DateTimeInterface { return $this->dateDebut; }
    public function setDateDebut(?\DateTimeInterface $v): static { $this->dateDebut = $v; return $this; }
    public function getDateFin(): ?\DateTimeInterface { return $this->dateFin; }
    public function setDateFin(?\DateTimeInterface $v): static { $this->dateFin = $v; return $this; }
    public function getStatus(): int { return $this->status; }
    public function setStatus(int $v): static { $this->status = $v; return $this; }
}