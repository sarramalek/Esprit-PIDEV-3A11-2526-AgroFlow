<?php

namespace App\Entity\User;

use App\Repository\User\AbonnementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AbonnementRepository::class)]
#[ORM\Table(name: 'abonnements')]
class Abonnement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_abonn', type: Types::INTEGER)]
    private ?int $idAbonn = null;

    #[ORM\Column(name: 'cin', type: Types::INTEGER)]
    private int $cin;

    #[ORM\Column(name: 'id_offre', type: Types::INTEGER)]
    private int $idOffre;

    #[ORM\Column(name: 'date_inscription', type: Types::DATE_MUTABLE)]
    private \DateTimeInterface $dateInscription;

    #[ORM\Column(name: 'date_expiration', type: Types::DATE_MUTABLE)]
    private \DateTimeInterface $dateExpiration;

    #[ORM\Column(name: 'situation', type: Types::STRING, length: 9)]
    private string $situation;

    // --- Getters & Setters ---

    public function getIdAbonn(): ?int
    {
        return $this->idAbonn;
    }

    public function getCin(): int
    {
        return $this->cin;
    }

    public function setCin(int $cin): static
    {
        $this->cin = $cin;
        return $this;
    }

    public function getIdOffre(): int
    {
        return $this->idOffre;
    }

    public function setIdOffre(int $idOffre): static
    {
        $this->idOffre = $idOffre;
        return $this;
    }

    public function getDateInscription(): \DateTimeInterface
    {
        return $this->dateInscription;
    }

    public function setDateInscription(\DateTimeInterface $dateInscription): static
    {
        $this->dateInscription = $dateInscription;
        return $this;
    }

    public function getDateExpiration(): \DateTimeInterface
    {
        return $this->dateExpiration;
    }

    public function setDateExpiration(\DateTimeInterface $dateExpiration): static
    {
        $this->dateExpiration = $dateExpiration;
        return $this;
    }

    public function getSituation(): string
    {
        return $this->situation;
    }

    public function setSituation(string $situation): static
    {
        $this->situation = $situation;
        return $this;
    }
}