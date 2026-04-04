<?php

namespace App\Entity\stocks;

use App\Repository\stocks\ArticleRepository;
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

    #[ORM\ManyToOne(targetEntity: Categorie::class)]
    #[ORM\JoinColumn(name: "id_categorie", referencedColumnName: "id_categorie", nullable: true)]
    private ?Categorie $categorie = null;

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
    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }
    public function setCategorie(?Categorie $categorie): self
    {
        $this->categorie = $categorie;
        return $this;
    }
}
