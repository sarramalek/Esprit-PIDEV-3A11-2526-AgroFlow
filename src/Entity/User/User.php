<?php

namespace App\Entity\User;

use App\Repository\User\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Terrain\Terrain;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Scheb\TwoFactorBundle\Model\BackupCodeInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface , TwoFactorInterface
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

    // src/Entity/User/User.php
#[ORM\Column(type: 'integer', options: ['default' => 0])]
private int $twoFactorEnabled = 0;


    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    private ?string $twoFactorSecret = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $twoFactorBackupCodes = null;

    #[ORM\Column(type: 'string', length: 255, nullable: false, options: ['default' => 'default.png'])]
    private ?string $img = 'default.png';

    /**
     * Terrain auquel l'ouvrier est assigné (nullable).
     * Un ouvrier appartient à un seul terrain à la fois.
     */
    #[ORM\ManyToOne(targetEntity: Terrain::class, inversedBy: 'ouvriers')]
    #[ORM\JoinColumn(name: 'id_terrain', referencedColumnName: 'id_terrain', nullable: true, onDelete: 'SET NULL')]
    private ?Terrain $terrain = null;

    //----------------------------------------------------------
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $googleAuthenticatorSecret = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private array $backupCodes = [];
    // ==================== UserInterface ====================

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        return match((int)$this->role) {
            1 => ['ROLE_OUVRIER'],
            2 => ['ROLE_AGRICULTEUR'],
            3 => ['ROLE_ADMIN'],
            default => ['ROLE_USER'],
        };
    }

    public function getPassword(): string
    {
        return (string) $this->mdp;
    }

    public function eraseCredentials(): void {}

    // ==================== GETTERS & SETTERS ====================

    public function getCin(): ?int { return $this->cin; }
    public function setCin(int $cin): self { $this->cin = $cin; return $this; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(?string $nom): self { $this->nom = $nom; return $this; }

    public function getPrenom(): ?string { return $this->prenom; }
    public function setPrenom(?string $prenom): self { $this->prenom = $prenom; return $this; }

    public function getTel(): ?string { return $this->tel; }
    public function setTel(?string $tel): self { $this->tel = $tel; return $this; }

    public function getDateNaiss(): ?\DateTimeInterface { return $this->dateNaiss; }
    public function setDateNaiss(?\DateTimeInterface $dateNaiss): self { $this->dateNaiss = $dateNaiss; return $this; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): self { $this->email = $email; return $this; }

    public function getMdp(): ?string { return $this->mdp; }
    public function setMdp(?string $mdp): self { $this->mdp = $mdp; return $this; }

    public function getAdresse(): ?string { return $this->adresse; }
    public function setAdresse(?string $adresse): self { $this->adresse = $adresse; return $this; }

    public function getVille(): ?string { return $this->ville; }
    public function setVille(?string $ville): self { $this->ville = $ville; return $this; }

    public function getRole(): ?int { return $this->role; }
    public function setRole(?int $role): self { $this->role = $role; return $this; }

    public function getDateCreationcpt(): ?\DateTimeInterface { return $this->dateCreationcpt; }
    public function setDateCreationcpt(?\DateTimeInterface $dateCreationcpt): self { $this->dateCreationcpt = $dateCreationcpt; return $this; }

    public function getDateDernierchg(): ?\DateTimeInterface { return $this->dateDernierchg; }
    public function setDateDernierchg(?\DateTimeInterface $dateDernierchg): self { $this->dateDernierchg = $dateDernierchg; return $this; }

    public function getTwoFactorEnabled(): int { return $this->twoFactorEnabled; }
public function setTwoFactorEnabled(int $v): self { $this->twoFactorEnabled = $v; return $this; }
public function isTwoFactorEnabled(): bool { return $this->twoFactorEnabled === 1; }
    public function getImg(): ?string { return $this->img; }
    public function setImg(?string $img): self { $this->img = $img; return $this; }

    // ==================== TERRAIN ====================

    public function getTerrain(): ?Terrain { return $this->terrain; }
    public function setTerrain(?Terrain $terrain): self { $this->terrain = $terrain; return $this; }
    // Interface Google 2FA
    public function isGoogleAuthenticatorEnabled(): bool
    {
        return $this->googleAuthenticatorSecret !== null;
    }

    public function getGoogleAuthenticatorUsername(): string
    {
        return $this->email;
    }

    public function getGoogleAuthenticatorSecret(): ?string
    {
        return $this->googleAuthenticatorSecret;
    }

    public function setGoogleAuthenticatorSecret(?string $secret): void
    {
        $this->googleAuthenticatorSecret = $secret;
    }

    // Interface Backup codes
    public function isBackupCode(string $code): bool
    {
        return in_array($code, $this->backupCodes);
    }

    public function invalidateBackupCode(string $code): void
    {
        $this->backupCodes = array_filter(
            $this->backupCodes,
            fn($c) => $c !== $code
        );
    }

    public function setBackupCodes(array $codes): void
    {
        $this->backupCodes = $codes;
    }

    public function getBackupCodes(): array
    {
        return $this->backupCodes;
    }
}