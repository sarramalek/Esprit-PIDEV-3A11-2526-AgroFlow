<?php

namespace App\Entity\stocks;

use App\Entity\stocks\MouvementStock;
use App\Repository\stocks\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\Table(name: "article")]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_article")]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?float $quantite_en_stock = null;

    #[ORM\Column]
    private ?float $seuil_alerte = null;

    #[ORM\Column(length: 20)]
    private ?string $unite_mesure = null;

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $prix_unitaire = null;

    #[ORM\ManyToOne(targetEntity: Categorie::class, inversedBy: "articles")]
    #[ORM\JoinColumn(name: "id_categorie", referencedColumnName: "id_categorie", nullable: true)]
    private ?Categorie $categorie = null;

    /**
     * @var Collection<int, MouvementStock>
     */
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
}
