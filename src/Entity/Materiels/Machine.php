<?php

namespace App\Entity\Materiels;

use App\Entity\User\User;
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

    // ── CORRECTION CLÉ ───────────────────────────────────────────────────────
    // On NE mappe PLUS $cin comme champ scalaire Doctrine.
    // La colonne `cin` est entièrement gérée par la relation ManyToOne ci-dessous.
    // On garde uniquement une propriété PHP non-mappée pour la compatibilité
    // avec le code existant (getCin / setCin).
    private ?int $cin = null;

    // La relation ManyToOne gère la colonne `cin` en base
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'cin', referencedColumnName: 'cin', nullable: false, onDelete: 'CASCADE')]
    private ?User $agriculteur = null;

    #[ORM\Column(name: 'nom', length: 255)]
    private ?string $nom = null;

    #[ORM\Column(name: 'marque', length: 255)]
    private ?string $marque = null;

    #[ORM\Column(name: 'modele', length: 255)]
    private ?string $modele = null;

    #[ORM\Column(name: 'numeroSerie', length: 255, nullable: true)]
    private ?string $numeroSerie = null;

    #[ORM\Column(name: 'etatM', length: 255)]
    private ?string $etatM = null;

    #[ORM\Column(name: 'dateAchat', type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateAchat = null;

    // ==================== Getters & Setters ====================

    public function getId(): ?int
    {
        return $this->id;
    }

    // getCin / setCin lisent/écrivent la propriété PHP non-mappée
    // La vraie valeur en base est gérée par la relation agriculteur
    public function getCin(): ?int
    {
        // Priorité à la relation pour avoir la valeur réelle
        return $this->agriculteur?->getCin() ?? $this->cin;
    }

    public function setCin(?int $cin): static
    {
        $this->cin = $cin;
        return $this;
    }

    // ==================== Relation Agriculteur ====================

    public function getAgriculteur(): ?User
    {
        return $this->agriculteur;
    }

    public function setAgriculteur(?User $agriculteur): static
    {
        $this->agriculteur = $agriculteur;
        // Synchroniser la propriété PHP locale
        $this->cin = $agriculteur?->getCin();
        return $this;
    }

    /**
     * Retourne le nom complet de l'agriculteur (Nom + Prénom)
     */
    public function getNomAgriculteur(): string
    {
        if (!$this->agriculteur) {
            return '—';
        }

        $nom    = $this->agriculteur->getNom()    ?? '';
        $prenom = $this->agriculteur->getPrenom() ?? '';
        $nomComplet = trim($nom . ' ' . $prenom);

        return $nomComplet ?: '—';
    }

    /**
     * Retourne le CIN de l'agriculteur
     */
    public function getCinAgriculteur(): ?int
    {
        return $this->agriculteur?->getCin() ?? $this->cin;
    }

    // ==================== Autres getters/setters ====================

    public function getNom(): ?string { return $this->nom; }
    public function setNom(?string $nom): static { $this->nom = $nom; return $this; }

    public function getMarque(): ?string { return $this->marque; }
    public function setMarque(?string $marque): static { $this->marque = $marque; return $this; }

    public function getModele(): ?string { return $this->modele; }
    public function setModele(?string $modele): static { $this->modele = $modele; return $this; }

    public function getNumeroSerie(): ?string { return $this->numeroSerie; }
    public function setNumeroSerie(?string $numeroSerie): static { $this->numeroSerie = $numeroSerie; return $this; }

    public function getEtatM(): ?string { return $this->etatM; }
    public function setEtatM(?string $etatM): static { $this->etatM = $etatM; return $this; }

    public function getDateAchat(): ?\DateTimeInterface { return $this->dateAchat; }
    public function setDateAchat(?\DateTimeInterface $dateAchat): static { $this->dateAchat = $dateAchat; return $this; }
}