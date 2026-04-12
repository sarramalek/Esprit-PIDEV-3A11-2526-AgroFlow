<?php

namespace App\Entity\Animals;

use App\Repository\Animals\AnimauxRepository;
use App\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
// Importation indispensable pour utiliser les contraintes de validation
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AnimauxRepository::class)]
class Animaux
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom de l'animal est obligatoire.")] 
    #[Assert\Regex(
        pattern: "/^[a-zA-ZÀ-ÿ\s\-]+$/u",
        message: "Le nom ne doit contenir que des lettres."
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Veuillez sélectionner une espèce.")]
    #[Assert\Choice(
        choices: ["Chien", "Chat", "Vache", "Chèvre", "Mouton", "Cheval"],
        message: "L'espèce sélectionnée n'est pas valide."
    )]
    private ?string $espece = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "La date de naissance est requise.")] 
    #[Assert\Type(type: "\DateTimeInterface", message: "Format de date invalide.")] 
    #[Assert\LessThanOrEqual(
        value: "today", 
        message: "La date de naissance ne peut pas être dans le futur."
    )]
    private ?\DateTimeInterface $date_naissance = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le sexe est obligatoire.")]
    #[Assert\Choice(choices: ["MALE", "FEMELLE"], message: "Le sexe doit être MALE ou FEMELLE.")]
    private ?string $sexe = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive(message: "Le poids doit être un nombre positif.")] 
    #[Assert\Type(type: "numeric", message: "Le poids doit être un nombre valide.")]
    private ?float $poids = null;

    #[ORM\OneToMany(mappedBy: 'animal', targetEntity: Examen::class, orphanRemoval: true)]
    private Collection $examen;

#[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'cin', nullable: true)]
    private ?User $user = null;

    public function __construct()
    {
        $this->examen = new ArrayCollection();
    }

    // --- GETTERS ET SETTERS ---

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

    public function getEspece(): ?string
    {
        return $this->espece;
    }

    public function setEspece(?string $espece): static
    {
        $this->espece = $espece;
        return $this;
    }

    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->date_naissance;
    }

    public function setDateNaissance(?\DateTimeInterface $date_naissance): static
    {
        $this->date_naissance = $date_naissance;
        return $this;
    }

    public function getSexe(): ?string
    {
        return $this->sexe;
    }

    public function setSexe(?string $sexe): static
    {
        $this->sexe = $sexe;
        return $this;
    }

    public function getPoids(): ?float
    {
        return $this->poids;
    }

    public function setPoids(?float $poids): static
    {
        $this->poids = $poids;
        return $this;
    }

    /**
     * @return Collection<int, Examen>
     */
    public function getExamen(): Collection
    {
        return $this->examen;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }
}