<?php

namespace App\Entity\Materiels;

use App\Repository\Materiels\MachineRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MachineRepository::class)]
#[ORM\Table(name: 'machine')]
class Machine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idM', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'nom', length: 255)]
    private ?string $nom = null;

    #[ORM\Column(name: 'marque', length: 255)]
    private ?string $marque = null;

    #[ORM\Column(name: 'modele', length: 255)]
    private ?string $modele = null;

    #[ORM\Column(name: 'numeroSerie', length: 255)]
    private ?string $numeroSerie = null;

    #[ORM\Column(name: 'etatM', length: 255)]
    private ?string $etatM = null;

    #[ORM\Column(name: 'dateAchat', type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateAchat = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMarque(): ?string
    {
        return $this->marque;
    }

    public function setMarque(?string $marque): static
    {
        $this->marque = $marque;
        return $this;
    }

    public function getModele(): ?string
    {
        return $this->modele;
    }

    public function setModele(?string $modele): static
    {
        $this->modele = $modele;
        return $this;
    }

    public function getNumeroSerie(): ?string
    {
        return $this->numeroSerie;
    }

    public function setNumeroSerie(?string $numeroSerie): static
    {
        $this->numeroSerie = $numeroSerie;
        return $this;
    }

    public function getEtatM(): ?string
    {
        return $this->etatM;
    }

    public function setEtatM(?string $etatM): static
    {
        $this->etatM = $etatM;
        return $this;
    }

    public function getDateAchat(): ?\DateTimeInterface
    {
        return $this->dateAchat;
    }

    public function setDateAchat(?\DateTimeInterface $dateAchat): static
    {
        $this->dateAchat = $dateAchat;
        return $this;
    }
}