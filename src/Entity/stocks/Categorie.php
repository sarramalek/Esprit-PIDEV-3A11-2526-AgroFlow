<?php

namespace App\Entity\stocks;

use App\Entity\User\User;
use App\Entity\stocks\Article;
use App\Repository\stocks\CategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategorieRepository::class)]
#[ORM\Table(name: "categorie")]
class Categorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    /** @var int|null */
    #[ORM\Column(name: "id_categorie")]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le nom de la catégorie est obligatoire.")]
    private string $nom = '';

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    // Utilisation de CamelCase pour PHP, snake_case pour la base de données
    #[ORM\Column(name: "date_creation", type: "datetime_immutable")]
    private \DateTimeImmutable $dateCreation;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "id_user", referencedColumnName: "cin", nullable: false)]
    private User $agriculteur;

    /**
     * @var Collection<int, Article>
     */
    #[ORM\OneToMany(mappedBy: 'categorie', targetEntity: Article::class)]
    private Collection $articles;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
        $this->dateCreation = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }
    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDateCreation(): \DateTimeImmutable
    {
        return $this->dateCreation;
    }
    public function setDateCreation(\DateTimeImmutable $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getAgriculteur(): User
    {
        return $this->agriculteur;
    }
    public function setAgriculteur(User $agriculteur): self
    {
        $this->agriculteur = $agriculteur;
        return $this;
    }

    /** @return Collection<int, Article> */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    #[ORM\Column(name: "id_admin", type: "integer", nullable: true)]
    private ?int $idAdmin = null;

    public function getIdAdmin(): ?int
    {
        return $this->idAdmin;
    }

    public function setIdAdmin(?int $idAdmin): self
    {
        $this->idAdmin = $idAdmin;
        return $this;
    }
}
