<?php

namespace App\Entity\User;

use App\Repository\User\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Terrain\Terrain;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    private ?int $cin = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $prenom = null;

    #[ORM\Column(type: 'string', length: 8, nullable: true)]
    private ?string $tel = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateNaiss = null;

    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $mdp = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $ville = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $role = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateCreationcpt = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateDernierchg = null;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private bool $twoFactorEnabled = false;

    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    private ?string $twoFactorSecret = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $twoFactorBackupCodes = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $img = null;

    // CORRECTION : nullable: true et initialisation à null
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $telegramChatId = null;

    /**
     * Terrain auquel l'ouvrier est assigné (nullable).
     * Un ouvrier appartient à un seul terrain à la fois.
     */
    #[ORM\ManyToOne(targetEntity: Terrain::class, inversedBy: 'ouvriers')]
    #[ORM\JoinColumn(name: 'id_terrain', referencedColumnName: 'id_terrain', nullable: true, onDelete: 'SET NULL')]
    private ?Terrain $terrain = null;


    // ==================== UserInterface ====================

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        // On aligne les chiffres avec tes routes de sécurité
        // 1 = Ouvrier, 2 = Agriculteur, 3 = Admin
        $roles = match ((int)$this->role) {
            1 => ['ROLE_OUVRIER'],      // Correspond à ^/ouvrier
            2 => ['ROLE_AGRICULTEUR'],  // Correspond à ^/agriculteur
            3 => ['ROLE_ADMIN'],        // Correspond à ^/admin
            default => [],
        };

        // Toujours ajouter ROLE_USER par défaut
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function getPassword(): string
    {
        return (string) $this->mdp;
    }

    public function eraseCredentials(): void {}

    // ==================== GETTERS & SETTERS ====================

    public function getCin(): ?int
    {
        return $this->cin;
    }
    public function setCin(int $cin): self
    {
        $this->cin = $cin;
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }
    public function setNom(?string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }
    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getTel(): ?string
    {
        return $this->tel;
    }
    public function setTel(?string $tel): self
    {
        $this->tel = $tel;
        return $this;
    }

    public function getDateNaiss(): ?\DateTimeInterface
    {
        return $this->dateNaiss;
    }
    public function setDateNaiss(?\DateTimeInterface $dateNaiss): self
    {
        $this->dateNaiss = $dateNaiss;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getMdp(): ?string
    {
        return $this->mdp;
    }
    public function setMdp(?string $mdp): self
    {
        $this->mdp = $mdp;
        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }
    public function setAdresse(?string $adresse): self
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }
    public function setVille(?string $ville): self
    {
        $this->ville = $ville;
        return $this;
    }

    public function getRole(): ?int
    {
        return $this->role;
    }
    public function setRole(?int $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getDateCreationcpt(): ?\DateTimeInterface
    {
        return $this->dateCreationcpt;
    }
    public function setDateCreationcpt(?\DateTimeInterface $dateCreationcpt): self
    {
        $this->dateCreationcpt = $dateCreationcpt;
        return $this;
    }

    public function getDateDernierchg(): ?\DateTimeInterface
    {
        return $this->dateDernierchg;
    }
    public function setDateDernierchg(?\DateTimeInterface $dateDernierchg): self
    {
        $this->dateDernierchg = $dateDernierchg;
        return $this;
    }

    public function isTwoFactorEnabled(): bool
    {
        return $this->twoFactorEnabled;
    }
    public function setTwoFactorEnabled(bool $twoFactorEnabled): self
    {
        $this->twoFactorEnabled = $twoFactorEnabled;
        return $this;
    }

    public function getTwoFactorSecret(): ?string
    {
        return $this->twoFactorSecret;
    }
    public function setTwoFactorSecret(?string $twoFactorSecret): self
    {
        $this->twoFactorSecret = $twoFactorSecret;
        return $this;
    }

    public function getTwoFactorBackupCodes(): ?string
    {
        return $this->twoFactorBackupCodes;
    }
    public function setTwoFactorBackupCodes(?string $twoFactorBackupCodes): self
    {
        $this->twoFactorBackupCodes = $twoFactorBackupCodes;
        return $this;
    }

    public function getImg(): ?string
    {
        return $this->img;
    }
    public function setImg(?string $img): self
    {
        $this->img = $img;
        return $this;
    }
    public function getTelegramChatId(): ?string
    {
        return $this->telegramChatId;
    }

    public function setTelegramChatId(?string $telegramChatId): self
    {
        $this->telegramChatId = $telegramChatId;
        return $this;
    }

    // ==================== TERRAIN ====================

    public function getTerrain(): ?Terrain
    {
        return $this->terrain;
    }

    public function setTerrain(?Terrain $terrain): self
    {
        $this->terrain = $terrain;
        return $this;
    }
}
