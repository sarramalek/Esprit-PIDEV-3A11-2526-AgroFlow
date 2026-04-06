<?php

namespace App\Entity\stocks;

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
    #[ORM\Column(name: "id_article")]
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

    #[ORM\OneToMany(targetEntity: MouvementStock::class, mappedBy: 'article', orphanRemoval: true)]
    private Collection $mouvementStocks;

    public function __construct()
    {
        $this->mouvementStocks = new ArrayCollection();
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

    public function getMouvementStocks(): Collection
    {
        return $this->mouvementStocks;
    }

    public function addMouvementStock(MouvementStock $mouvementStock): static
    {
        if (!$this->mouvementStocks->contains($mouvementStock)) {
            $this->mouvementStocks->add($mouvementStock);
            $mouvementStock->setArticle($this);
        }
        return $this;
    }

    public function removeMouvementStock(MouvementStock $mouvementStock): static
    {
        if ($this->mouvementStocks->removeElement($mouvementStock)) {
            if ($mouvementStock->getArticle() === $this) {
                $mouvementStock->setArticle(null);
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
