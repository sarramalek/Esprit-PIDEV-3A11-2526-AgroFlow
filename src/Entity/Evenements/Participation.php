<?php

namespace App\Entity\Evenements;

use App\Entity\User\User;
use App\Repository\Evenements\ParticipationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ParticipationRepository::class)]
#[ORM\Table(name: 'participation')]
class Participation
{
    public function __construct()
    {
        $this->dateInscription = new \DateTime();
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_participation', type: Types::INTEGER)]
    private ?int $idParticipation = null;

    #[ORM\Column(name: 'statut_participation', type: Types::STRING, length: 50)]
    #[Assert\NotBlank(message: 'Le statut ne peut pas être vide !')]
    private string $statutParticipation = '';

    #[ORM\Column(name: 'date_inscription', type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: 'La date d\'inscription est obligatoire.')]
    #[Assert\LessThanOrEqual(value: 'today', message: 'La date d\'inscription ne peut pas être dans le futur.')]
    private \DateTimeInterface $dateInscription;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $presence = false;

    #[ORM\ManyToOne(targetEntity: Evenement::class)]
    #[ORM\JoinColumn(name: 'id_evenement', referencedColumnName: 'id_evenement', nullable: false)]
    #[Assert\NotNull(message: 'Veuillez sélectionner un événement.')]
    private Evenement $evenement;

    // Relation vers User : la clé étrangère id_user référence cin de la table users
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'cin', nullable: false)]
    #[Assert\NotNull(message: 'Veuillez sélectionner un utilisateur.')]
    private User $utilisateur;

    // ================= GETTERS / SETTERS =================

    public function getIdParticipation(): ?int
    {
        return $this->idParticipation;
    }

    public function getStatutParticipation(): ?string
    {
        return $this->statutParticipation;
    }

    public function setStatutParticipation(string $statutParticipation): static
    {
        $this->statutParticipation = $statutParticipation;
        return $this;
    }

    public function getDateInscription(): ?\DateTimeInterface
    {
        return $this->dateInscription;
    }

    public function setDateInscription(\DateTimeInterface $dateInscription): static
    {
        $this->dateInscription = $dateInscription;
        return $this;
    }

    public function isPresence(): bool
    {
        return $this->presence;
    }

    public function setPresence(bool $presence): static
    {
        $this->presence = $presence;
        return $this;
    }

    public function getEvenement(): ?Evenement
    {
        return $this->evenement;
    }

    public function setEvenement(Evenement $evenement): static
    {
        $this->evenement = $evenement;
        return $this;
    }

    public function getUtilisateur(): ?User
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(User $utilisateur): static
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    public function __toString(): string
    {
        return sprintf('Participation #%d', $this->idParticipation ?? 0);
    }
}