<?php

namespace App\Entity\User;

use App\Entity\User\User;
use App\Repository\User\TacheRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TacheRepository::class)]
#[ORM\Table(name: 'taches')]
class Tache
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $idTache = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $nomTache = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'assignee', referencedColumnName: 'cin', nullable: false)]
    private ?User $assignee = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $etat = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $priorite = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateEcheancee = null;

    // ==================== GETTERS & SETTERS ====================

    public function getIdTache(): ?int { return $this->idTache; }

    public function getNomTache(): ?string { return $this->nomTache; }
    public function setNomTache(?string $nomTache): self { $this->nomTache = $nomTache; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }

    public function getAssignee(): ?User { return $this->assignee; }
    public function setAssignee(?User $assignee): self { $this->assignee = $assignee; return $this; }

    public function getEtat(): ?string { return $this->etat; }
    public function setEtat(?string $etat): self { $this->etat = $etat; return $this; }

    public function getPriorite(): ?string { return $this->priorite; }
    public function setPriorite(?string $priorite): self { $this->priorite = $priorite; return $this; }

    public function getDateEcheancee(): ?\DateTimeInterface { return $this->dateEcheancee; }
    public function setDateEcheancee(?\DateTimeInterface $dateEcheancee): self { $this->dateEcheancee = $dateEcheancee; return $this; }
}