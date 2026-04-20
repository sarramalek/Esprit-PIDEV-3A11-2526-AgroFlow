<?php

namespace App\Command\Animals;

use App\Service\Animals\ReminderService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:reminders:update',
    description: 'Met à jour automatiquement les rappels pour tous les examens',
)]
class UpdateRemindersCommand extends Command
{
    private ReminderService $reminderService;

    public function __construct(ReminderService $reminderService)
    {
        $this->reminderService = $reminderService;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Mise à jour des rappels');

        $io->text('Analyse des rappels en cours...');

        $report = $this->reminderService->generateReminderReport();

        $io->success('Analyse terminée !');

        $io->table(
            ['Type', 'Nombre'],
            [
                ['Rappels actifs', $report['total_active']],
                ['Rappels bientôt', count($report['upcoming'])],
                ['Rappels en retard', count($report['overdue'])],
            ]
        );

        if (!empty($report['upcoming'])) {
            $io->section('Rappels arrivant bientôt :');
            foreach ($report['upcoming'] as $examen) {
                $nextDate = $examen->calculateNextReminderDate();
                $io->text(sprintf(
                    '• %s - %s (%s)',
                    $examen->getAnimal() ? $examen->getAnimal()->getNom() : 'N/A',
                    $examen->getReminderType(),
                    $nextDate ? $nextDate->format('d/m/Y') : 'N/A'
                ));
            }
        }

        if (!empty($report['overdue'])) {
            $io->warning('Rappels en retard :');
            foreach ($report['overdue'] as $examen) {
                $nextDate = $examen->calculateNextReminderDate();
                $io->text(sprintf(
                    '• %s - %s (%s)',
                    $examen->getAnimal() ? $examen->getAnimal()->getNom() : 'N/A',
                    $examen->getReminderType(),
                    $nextDate ? $nextDate->format('d/m/Y') : 'N/A'
                ));
            }
        }

        return Command::SUCCESS;
    }
}
