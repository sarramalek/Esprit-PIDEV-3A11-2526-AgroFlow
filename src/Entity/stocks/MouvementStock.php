<?php

namespace App\Entity\stocks;

use App\Entity\stocks\Article;
use App\Repository\stocks\MouvementStockRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MouvementStockRepository::class)]
class MouvementStock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private ?string $type = null;

    #[ORM\Column]
    private ?float $quantite = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateMouvement = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $motif = null;

    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'mouvementStocks')]
    #[ORM\JoinColumn(name: "article_id", referencedColumnName: "id_article", nullable: false)]
    private ?Article $article = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }
    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getQuantite(): ?float
    {
        return $this->quantite;
    }
    public function setQuantite(float $quantite): static
    {
        $this->quantite = $quantite;
        return $this;
    }

    public function getDateMouvement(): ?\DateTimeImmutable
    {
        return $this->dateMouvement;
    }
    public function setDateMouvement(\DateTimeImmutable $dateMouvement): static
    {
        $this->dateMouvement = $dateMouvement;
        return $this;
    }

    public function getMotif(): ?string
    {
        return $this->motif;
    }
    public function setMotif(?string $motif): static
    {
        $this->motif = $motif;
        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }
    public function setArticle(?Article $article): static
    {
        $this->article = $article;
        return $this;
    }
}
