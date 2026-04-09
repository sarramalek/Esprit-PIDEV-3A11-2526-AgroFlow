<?php

namespace App\Entity\stocks;

use App\Entity\User\User;
use App\Entity\stocks\MouvementStock;
use App\Repository\stocks\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\Table(name: "article")]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_article", type: "integer")]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: "Le nom du produit ne peut pas être vide.")]
    #[Assert\Length(min: 3, minMessage: "Le nom doit contenir au moins {{ limit }} caractères.")]
    private ?string $nom = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "La quantité est obligatoire.")]
    #[Assert\PositiveOrZero(message: "La quantité ne peut pas être négative.")]
    private ?float $quantite_en_stock = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Le seuil d'alerte est obligatoire.")]
    #[Assert\PositiveOrZero(message: "Le seuil doit être un nombre positif.")]
    private ?float $seuil_alerte = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: "Veuillez préciser l'unité (Kg, Litre, etc.).")]
    private ?string $unite_mesure = null;

    #[ORM\Column(type: "float", nullable: true)]
    #[Assert\NotNull(message: "Le prix unitaire est requis.")]
    #[Assert\Positive(message: "Le prix doit être supérieur à zéro.")]
    private ?float $prix_unitaire = null;

    #[ORM\ManyToOne(targetEntity: Categorie::class, inversedBy: "articles")]
    #[ORM\JoinColumn(name: "id_categorie", referencedColumnName: "id_categorie", nullable: true)]
    private ?Categorie $categorie = null;

    // Ajout du lien vers l'agriculteur (User)
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "id_user", referencedColumnName: "cin", nullable: true)]
    private ?User $user = null;

    // Correction : Le nom de la propriété doit être 'mouvements' pour matcher ton MouvementStock
    #[ORM\OneToMany(targetEntity: MouvementStock::class, mappedBy: 'article', orphanRemoval: true)]
    private Collection $mouvements;

    public function __construct()
    {
        $this->mouvements = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }
    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getQuantiteEnStock(): ?float
    {
        return $this->quantite_en_stock;
    }
    public function setQuantiteEnStock(float $quantite): self
    {
        $this->quantite_en_stock = $quantite;
        return $this;
    }

    public function getSeuilAlerte(): ?float
    {
        return $this->seuil_alerte;
    }
    public function setSeuilAlerte(float $seuil): self
    {
        $this->seuil_alerte = $seuil;
        return $this;
    }

    public function getUniteMesure(): ?string
    {
        return $this->unite_mesure;
    }
    public function setUniteMesure(string $unite): self
    {
        $this->unite_mesure = $unite;
        return $this;
    }

    public function getPrixUnitaire(): ?float
    {
        return $this->prix_unitaire;
    }
    public function setPrixUnitaire(?float $prix_unitaire): self
    {
        $this->prix_unitaire = $prix_unitaire;
        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }
    public function setCategorie(?Categorie $categorie): self
    {
        $this->categorie = $categorie;
        return $this;
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

    /**
     * @return Collection<int, MouvementStock>
     */
    public function getMouvements(): Collection
    {
        return $this->mouvements;
    }

    public function addMouvement(MouvementStock $mouvement): static
    {
        if (!$this->mouvements->contains($mouvement)) {
            $this->mouvements->add($mouvement);
            $mouvement->setArticle($this);
        }
        return $this;
    }

    public function removeMouvement(MouvementStock $mouvement): static
    {
        if ($this->mouvements->removeElement($mouvement)) {
            if ($mouvement->getArticle() === $this) {
                $mouvement->setArticle(null);
            }
        }
        return $this;
    }

    public function getValeurTotaleStock(): float
    {
        return ($this->quantite_en_stock ?? 0) * ($this->prix_unitaire ?? 0);
    }

    public function isStockCritique(): bool
    {
        return $this->quantite_en_stock <= $this->seuil_alerte;
    }
}
