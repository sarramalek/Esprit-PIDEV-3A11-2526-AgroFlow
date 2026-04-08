<?php

namespace App\Entity\stocks;

use App\Entity\User\User;
use App\Entity\stocks\Article;
use App\Repository\stocks\MouvementStockRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MouvementStockRepository::class)]
#[ORM\Table(name: "mouvement_stock")]
class MouvementStock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id")]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private ?string $type = null; // ENTREE ou SORTIE

    #[ORM\Column]
    private ?float $quantite = null;

    #[ORM\Column(name: "date_mouvement")]
    private ?\DateTimeImmutable $dateMouvement = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $motif = null;

    // CORRECTION ICI : 
    // name = nom de la colonne dans la table mouvement_stock (article_id selon ton phpMyAdmin)
    // referencedColumnName = nom de la clé primaire dans la table article (id_article selon ton entité Article)
    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'mouvements')]
    #[ORM\JoinColumn(name: "article_id", referencedColumnName: "id_article", nullable: false)]
    private ?Article $article = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "id_user", referencedColumnName: "cin", nullable: false)]
    private ?User $user = null;

    // ... (reste des getters et setters identiques)

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getQuantite(): ?float
    {
        return $this->quantite;
    }
    public function setQuantite(float $quantite): self
    {
        $this->quantite = $quantite;
        return $this;
    }

    public function getDateMouvement(): ?\DateTimeImmutable
    {
        return $this->dateMouvement;
    }
    public function setDateMouvement(\DateTimeImmutable $dateMouvement): self
    {
        $this->dateMouvement = $dateMouvement;
        return $this;
    }

    public function getMotif(): ?string
    {
        return $this->motif;
    }
    public function setMotif(?string $motif): self
    {
        $this->motif = $motif;
        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }
    public function setArticle(?Article $article): self
    {
        $this->article = $article;
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
}
