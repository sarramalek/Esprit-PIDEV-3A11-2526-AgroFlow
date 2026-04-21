<?php

namespace App\Controller\Animals;

use App\Service\Animals\ReminderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/reminders')]
final class ReminderController extends AbstractController
{
    #[Route(name: 'app_reminders_dashboard', methods: ['GET'])]
    #[IsGranted('ROLE_AGRICULTEUR')]
    public function dashboard(ReminderService $reminderService): Response
    {
        $user = $this->getUser();

        // Filtrer les rappels pour l'utilisateur actuel
        $allReminders = $reminderService->getActiveReminders();
        $userReminders = array_filter($allReminders, function($examen) use ($user) {
            return $examen->getAnimal() && $examen->getAnimal()->getUser() === $user;
        });

        $upcomingReminders = array_filter($userReminders, function($examen) {
            return $examen->isReminderSoon();
        });

        $overdueReminders = $reminderService->getOverdueReminders();
        $userOverdueReminders = array_filter($overdueReminders, function($examen) use ($user) {
            return $examen->getAnimal() && $examen->getAnimal()->getUser() === $user;
        });

        return $this->render('Animals/reminders/dashboard.html.twig', [
            'upcoming_reminders' => $upcomingReminders,
            'overdue_reminders' => $userOverdueReminders,
            'total_active' => count($userReminders),
        ]);
    }
}
