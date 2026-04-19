<?php

namespace App\Service;

use App\Entity\Animals\Examen;
use App\Repository\Animals\ExamenRepository;
use Doctrine\ORM\EntityManagerInterface;

class ReminderService
{
    private EntityManagerInterface $entityManager;
    private ExamenRepository $examenRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ExamenRepository $examenRepository
    ) {
        $this->entityManager = $entityManager;
        $this->examenRepository = $examenRepository;
    }

    /**
     * Récupère tous les examens avec des rappels actifs
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