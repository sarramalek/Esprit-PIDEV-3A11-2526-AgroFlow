<?php

namespace App\Entity\Materiels;

use App\Entity\User\User;
use App\Repository\Materiels\MachineRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MachineRepository::class)]
#[ORM\Table(name: 'machine')]
class Machine
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: 'idM', type: 'integer')]
    private ?int $id = null;

    /**
     * La relation ManyToOne gère la colonne `cin` en base.
     * Pas de mapping scalaire séparé pour éviter le conflit Doctrine.
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'cin', referencedColumnName: 'cin', nullable: false, onDelete: 'CASCADE')]
    private User $agriculteur;

    #[ORM\Column(name: 'nom', type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire')]
    #[Assert\Length(min: 2, max: 255)]
    private string $nom = '';

    #[ORM\Column(name: 'marque', type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'La marque est obligatoire')]
    #[Assert\Length(min: 2, max: 255)]
    private string $marque = '';

    #[ORM\Column(name: 'modele', type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Le modèle est obligatoire')]
    #[Assert\Length(min: 1, max: 255)]
    private string $modele = '';

    #[ORM\Column(name: 'numeroSerie', type: 'string', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $numeroSerie = null;

    #[ORM\Column(name: 'etatM', type: 'string', length: 255)]
    #[Assert\NotBlank(message: "L'état est obligatoire")]
    #[Assert\Choice(choices: ['Neuf', 'Bon', 'Occasion', 'En panne'], message: 'État invalide')]
    private string $etatM = '';

    #[ORM\Column(name: 'dateAchat', type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateAchat = null;

    #[ORM\Column(name: 'kilometrage', type: 'integer')]
    #[Assert\NotBlank(message: 'Le kilométrage est obligatoire')]
    #[Assert\PositiveOrZero(message: 'Le kilométrage ne peut pas être négatif')]
    private int $kilometrage = 0;

    #[ORM\Column(name: 'dateLastVisite', type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateLastVisite = null;

    #[ORM\Column(name: 'kmLastVisite', type: 'integer')]
    #[Assert\NotBlank(message: 'Le kilométrage de dernière visite est obligatoire')]
    #[Assert\PositiveOrZero(message: 'Le kilométrage ne peut pas être négatif')]
    private int $kmLastVisite = 0;

    #[ORM\Column(name: 'prochaineMaintenance', type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $prochaineMaintenance = null;

    // ==================== Getters & Setters ====================

    public function getId(): ?int
    {
        return $this->id;
    }

    // ── Relation Agriculteur ──────────────────────────────────────────────────

    public function getAgriculteur(): ?User
    {
        return $this->agriculteur;
    }

    public function setAgriculteur(User $agriculteur): static
    {
        $this->agriculteur = $agriculteur;
        return $this;
    }

    /**
     * Retourne le CIN de l'agriculteur lié (depuis la relation).
     */
    public function getCin(): ?int
    {
        return $this->agriculteur->getCin();
    }

    /**
     * Alias explicite utilisé dans le contrôleur pour la vérification d'accès.
     */
    public function getCinAgriculteur(): ?int
    {
        return $this->agriculteur->getCin();
    }

    /**
     * Retourne le nom complet de l'agriculteur (Nom + Prénom).
     */
    public function getNomAgriculteur(): string
    {
        $nom    = $this->agriculteur->getNom()    ?? '';
        $prenom = $this->agriculteur->getPrenom() ?? '';
        $nomComplet = trim($nom . ' ' . $prenom);
        return $nomComplet ?: '—';
    }

    // ── Nom ──────────────────────────────────────────────────────────────────

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    // ── Marque ───────────────────────────────────────────────────────────────

    public function getMarque(): ?string
    {
        return $this->marque;
    }

    public function setMarque(string $marque): static
    {
        $this->marque = $marque;
        return $this;
    }

    // ── Modèle ───────────────────────────────────────────────────────────────

    public function getModele(): ?string
    {
        return $this->modele;
    }

    public function setModele(string $modele): static
    {
        $this->modele = $modele;
        return $this;
    }

    // ── Numéro de série ──────────────────────────────────────────────────────

    public function getNumeroSerie(): ?string
    {
        return $this->numeroSerie;
    }

    public function setNumeroSerie(?string $numeroSerie): static
    {
        $this->numeroSerie = $numeroSerie;
        return $this;
    }

    // ── État ─────────────────────────────────────────────────────────────────

    public function getEtatM(): ?string
    {
        return $this->etatM;
    }

    public function setEtatM(string $etatM): static
    {
        $this->etatM = $etatM;
        return $this;
    }

    // ── Date d'achat ─────────────────────────────────────────────────────────

    public function getDateAchat(): ?\DateTimeInterface
    {
        return $this->dateAchat;
    }

    public function setDateAchat(?\DateTimeInterface $dateAchat): static
    {
        $this->dateAchat = $dateAchat;
        return $this;
    }

    // ── Kilométrage ──────────────────────────────────────────────────────────

    public function getKilometrage(): int
    {
        return $this->kilometrage;
    }

    public function setKilometrage(int $kilometrage): static
    {
        $this->kilometrage = $kilometrage;
        return $this;
    }

    // ── Date dernière visite ─────────────────────────────────────────────────

    public function getDateLastVisite(): ?\DateTimeInterface
    {
        return $this->dateLastVisite;
    }

    public function setDateLastVisite(?\DateTimeInterface $dateLastVisite): static
    {
        $this->dateLastVisite = $dateLastVisite;
        return $this;
    }

    // ── Kilométrage dernière visite ───────────────────────────────────────────

    public function getKmLastVisite(): int
    {
        return $this->kmLastVisite;
    }

    public function setKmLastVisite(int $kmLastVisite): static
    {
        $this->kmLastVisite = $kmLastVisite;
        return $this;
    }

    // ── Prochaine maintenance ─────────────────────────────────────────────────

    public function getProchaineMaintenance(): ?\DateTimeInterface
    {
        return $this->prochaineMaintenance;
    }

    public function setProchaineMaintenance(?\DateTimeInterface $prochaineMaintenance): static
    {
        $this->prochaineMaintenance = $prochaineMaintenance;
        return $this;
    }
}