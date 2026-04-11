<?php

namespace App\Entity\User;

use App\Repository\User\TacheRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TacheRepository::class)]
#[ORM\Table(name: 'taches')]
class Tache
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_tache', type: 'integer')]
    private ?int $idTache = null;

    #[ORM\Column(name: 'nom_tache', type: 'string', length: 255, nullable: true)]
    private ?string $nomTache = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'assignee', referencedColumnName: 'cin', nullable: true)]
    private ?User $assignee = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $etat = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $priorite = null;

    #[ORM\Column(name: 'date_echeancee', type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateEcheancee = null;

    public function getId(): ?int { return $this->idTache; }
    public function getIdTache(): ?int { return $this->idTache; }

    public function getNomTache(): ?string { return $this->nomTache; }
    public function setNomTache(?string $v): self { $this->nomTache = $v; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $v): self { $this->description = $v; return $this; }

    public function getAssignee(): ?User { return $this->assignee; }
    public function setAssignee(?User $v): self { $this->assignee = $v; return $this; }

    public function getEtat(): ?string { return $this->etat; }
    public function setEtat(?string $v): self { $this->etat = $v; return $this; }

    public function getPriorite(): ?string { return $this->priorite; }
    public function setPriorite(?string $v): self { $this->priorite = $v; return $this; }

    public function getDateEcheancee(): ?\DateTimeInterface { return $this->dateEcheancee; }
    public function setDateEcheancee(?\DateTimeInterface $v): self { $this->dateEcheancee = $v; return $this; }
}