<?php

namespace App\Service\Animals;

use App\Entity\Animals\Examen;
use App\Repository\Animals\ExamenRepository;

class ReminderService
{
    private ExamenRepository $examenRepository;

    public function __construct(
        ExamenRepository $examenRepository
    ) {
        $this->examenRepository = $examenRepository;
    }

    /**
     * Récupère tous les examens avec des rappels actifs
     */
    /**
     * @return array<int, Examen>
     */
    public function getActiveReminders(): array
    {
        $allExamens = $this->examenRepository->findAll();
        return array_filter($allExamens, function($examen) {
            return $examen->shouldHaveReminder();
        });
    }

    /**
     * Récupère les rappels qui arrivent bientôt (< 7 jours)
     */
    /**
     * @return array<int, Examen>
     */
    public function getUpcomingReminders(int $days = 7): array
    {
        $allExamens = $this->examenRepository->findAll();
        return array_filter($allExamens, function($examen) use ($days) {
            if (!$examen->shouldHaveReminder()) {
                return false;
            }

            $nextReminderDate = $examen->calculateNextReminderDate();
            if (!$nextReminderDate) {
                return false;
            }

            $now = new \DateTime();
            $interval = $now->diff($nextReminderDate);
            $daysDiff = $interval->days;

            // Si la date est dans le passé, ce n'est pas bientôt
            if ($nextReminderDate < $now) {
                return false;
            }

            return $daysDiff <= $days;
        });
    }

    /**
     * Récupère les rappels en retard
     */
    /**
     * @return array<int, Examen>
     */
    public function getOverdueReminders(): array
    {
        $allExamens = $this->examenRepository->findAll();
        return array_filter($allExamens, function($examen) {
            if (!$examen->shouldHaveReminder()) {
                return false;
            }

            $nextReminderDate = $examen->calculateNextReminderDate();
            if (!$nextReminderDate) {
                return false;
            }

            $now = new \DateTime();
            return $nextReminderDate < $now;
        });
    }

    /**
     * Met à jour automatiquement les rappels pour tous les examens
     * Note: Les rappels sont calculés à la volée, pas de mise à jour nécessaire
     */
    public function updateAllReminders(): void
    {
        // Les rappels sont calculés dynamiquement, rien à persister
    }

    /**
     * Génère un rapport des rappels
     */
    /**
     * @return array{upcoming:array<int, Examen>, overdue:array<int, Examen>, total_active:int}
     */
    public function generateReminderReport(): array
    {
        return [
            'upcoming' => $this->getUpcomingReminders(),
            'overdue' => $this->getOverdueReminders(),
            'total_active' => count($this->getActiveReminders()),
        ];
    }

    /**
     * Vérifie si un examen nécessite un rappel automatique
     */
    public function shouldCreateReminder(Examen $examen): bool
    {
        return $examen->shouldHaveReminder();
    }
}
