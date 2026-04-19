<?php

namespace App\Entity\Animals;

use App\Repository\Animals\ExamenRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExamenRepository::class)]
#[ORM\Table(name: 'examens_sante')]
class Examen
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\NotBlank(message: "La date de l'examen est obligatoire.")]
    #[Assert\Type(type: "\DateTimeInterface", message: "Format de date invalide.")]
    private ?\DateTimeInterface $date_examen = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\NotBlank(message: "Le type d'examen est obligatoire.")]
    #[Assert\Choice(
        choices: ["Vaccin", "Radio", "Scanner", "Consultation"],
        message: "Veuillez sélectionner un type d'examen valide."
    )]
    private ?string $type_examen = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\NotBlank(message: "Le diagnostic est obligatoire.")]
    #[Assert\Choice(
        choices: ["En bonne santé", "Infection", "Fracture", "Urgence"],
        message: "Le diagnostic sélectionné n'est pas valide."
    )]
    private ?string $diagnostic = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: "Le traitement est obligatoire.")]
    #[Assert\Choice(
        choices: ["Repos", "Antibiotiques", "Observation", "Chirurgie"],
        message: "Le traitement sélectionné n'est pas valide."
    )]
    private ?string $traitement = null;

    #[ORM\ManyToOne(targetEntity: Animaux::class, inversedBy: 'examen')]
    #[ORM\JoinColumn(name: 'id_animal', referencedColumnName: 'id', nullable: false)]
    #[Assert\NotNull(message: "L'examen doit être relié à un animal.")]
    private ?Animaux $animal = null;

    // --- GETTERS ET SETTERS ---

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateExamen(): ?\DateTimeInterface
    {
        return $this->date_examen;
    }

    public function setDateExamen(?\DateTimeInterface $date_examen): static
    {
        $this->date_examen = $date_examen;
        return $this;
    }

    public function getTypeExamen(): ?string
    {
        return $this->type_examen;
    }

    public function setTypeExamen(?string $type_examen): static
    {
        $this->type_examen = $type_examen;
        return $this;
    }

    public function getDiagnostic(): ?string
    {
        return $this->diagnostic;
    }

    public function setDiagnostic(?string $diagnostic): static
    {
        $this->diagnostic = $diagnostic;
        return $this;
    }

    public function getTraitement(): ?string
    {
        return $this->traitement;
    }

    public function setTraitement(?string $traitement): static
    {
        $this->traitement = $traitement;
        return $this;
    }

    public function getAnimal(): ?Animaux
    {
        return $this->animal;
    }

    public function setAnimal(?Animaux $animal): static
    {
        $this->animal = $animal;
        return $this;
    }
    public function getHealthScore(): int
    {
        $score = 50; // Base de départ

        // Bonus
        if ($this->type_examen === 'Vaccin') {
            $score += 20;
        }
        if ($this->diagnostic === 'En bonne santé') {
            $score += 10;
        }

        // Malus
        if ($this->diagnostic === 'Infection' || $this->diagnostic === 'Urgence') {
            $score -= 40;
        }
        if ($this->traitement === 'Chirurgie' || $this->traitement === 'Antibiotiques') {
            $score -= 30;
        }

        // Clamp le score entre 0 et 100
        return max(0, min(100, $score));
    }

    /**
     * Retourne l'emoji correspondant au score de santé
     */
    public function getHealthEmoji(): string
    {
        $score = $this->getHealthScore();

        if ($score >= 80) return '🌟';
        if ($score >= 50) return '🙂';
        return '⚠️';
    }

    /**
     * Calcule le prix estimé de l'acte vétérinaire (DT)
     */
    public function getEstimatedPrice(): int
    {
        $price = 0;
        switch ($this->type_examen) {
            case 'Vaccin': $price = 50; break;
            case 'Consultation': $price = 80; break;
            case 'Radio':
            case 'Scanner': $price = 150; break;
        }

        if ($this->traitement === 'Chirurgie') {
            $price += 200;
        }

        return $price;
    }

    /**
     * Vérifie si le rappel est bientôt (moins de 7 jours)
     * Méthode virtuelle qui calcule si cet examen nécessite un rappel
     */
    public function isReminderSoon(): bool
    {
        // Pour les vaccins, considérer qu'un rappel est nécessaire 1 an après
        if ($this->type_examen === 'Vaccin' && $this->date_examen) {
            $nextReminderDate = $this->calculateNextReminderDate();
            if ($nextReminderDate) {
                $now = new \DateTime();
                $interval = $now->diff($nextReminderDate);
                $days = $interval->days;

                // Si la date est dans le passé, ce n'est pas bientôt
                if ($nextReminderDate < $now) {
                    return false;
                }

                return $days <= 7;
            }
        }

        return false;
    }

    /**
     * Calcule automatiquement la date du prochain rappel basée sur le type de vaccin
     */
    public function calculateNextReminderDate(): ?\DateTimeInterface
    {
        if ($this->type_examen !== 'Vaccin' || !$this->date_examen) {
            return null;
        }

        $nextDate = clone $this->date_examen;

        if (str_contains(strtolower($this->diagnostic), 'rage')) {
            $nextDate->modify('+1 year');
        } elseif (str_contains(strtolower($this->diagnostic), 'tétanos')) {
            $nextDate->modify('+10 years');
        } elseif (str_contains(strtolower($this->diagnostic), 'grippe')) {
            $nextDate->modify('+1 year');
        } elseif ($this->traitement === 'Antibiotiques') {
            $nextDate->modify('+7 days');
        } elseif ($this->traitement === 'Chirurgie') {
            $nextDate->modify('+7 days');
        } elseif ($this->traitement === 'Observation') {
            $nextDate->modify('+1 month');
        } else {
            $nextDate->modify('+1 year');
        }

        return $nextDate;
    }

    /**
     * Retourne le type de rappel pour cet examen
     */
    public function getReminderType(): ?string
    {
        if ($this->type_examen !== 'Vaccin') {
            return null;
        }

        if (str_contains(strtolower($this->diagnostic), 'rage')) {
            return 'Rappel rage';
        }

        if (str_contains(strtolower($this->diagnostic), 'tétanos')) {
            return 'Rappel tétanos';
        }

        if (str_contains(strtolower($this->diagnostic), 'grippe')) {
            return 'Rappel grippe';
        }

        if ($this->traitement === 'Antibiotiques') {
            return 'Suivi antibiotiques';
        }

        if ($this->traitement === 'Chirurgie') {
            return 'Suivi post-opératoire';
        }

        if ($this->traitement === 'Observation') {
            return 'Suivi observation';
        }

        return 'Rappel annuel';
    }

    /**
     * Indique si un vaccin est programmé dans le futur et doit être alerté
     */
    public function isFutureVaccineAlert(): bool
    {
        return $this->type_examen === 'Vaccin'
            && $this->date_examen !== null
            && $this->date_examen > new \DateTime();
    }

    /**
     * Vérifie si cet examen doit avoir un rappel
     */
    public function shouldHaveReminder(): bool
    {
        return $this->type_examen === 'Vaccin';
    }
}