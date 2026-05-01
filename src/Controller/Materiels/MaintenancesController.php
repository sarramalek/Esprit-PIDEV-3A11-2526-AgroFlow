<?php
// src/Controller/Materiels/MaintenancesController.php

namespace App\Controller\Materiels;

use App\Entity\Materiels\Maintenance;
use App\Form\Materiels\MaintenanceType;
use App\Repository\Materiels\MaintenanceRepository;
use App\Service\MaintenanceChatbotService;
use App\Service\MaintenanceAlertService;
use App\Service\GeminiRecommendationService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/agriculteur/maintenances')]
class MaintenancesController extends AbstractController
{
    public function __construct(
        private readonly MaintenanceAlertService $alertService,
        private readonly MaintenanceChatbotService $chatbotService,
        private readonly GeminiRecommendationService $geminiService,
    ) {}

    // ══════════════════════════════════════════════════════════
    //  CRUD
    // ══════════════════════════════════════════════════════════

    #[Route('', name: 'agri_maintenances_index', methods: ['GET'])]
    public function index(
        Request $request,
        MaintenanceRepository $repository,
        PaginatorInterface $paginator,
        EntityManagerInterface $entityManager,
    ): Response {
        $search    = (string) $request->query->get('search', '');
        $type      = (string) $request->query->get('type', '');
        $sort      = (string) $request->query->get('sort', 'dateMain');
        $direction = (string) $request->query->get('dir', 'DESC');

        $maintenances = $repository->searchWithMaterielName($search, $type, $sort, $direction);

        $pagination = $paginator->paginate(
            $maintenances,
            $request->query->getInt('page', 1),
            10,
        );

        $maintenancesWithAlerts = [];
        $statisticsAlerts       = ['urgente' => 0, 'haute' => 0, 'moyenne' => 0, 'faible' => 0, 'total_critical' => 0];
        $needsFlush             = false;

        foreach ($pagination->getItems() as $maintenance) {
            if (!$maintenance instanceof Maintenance) {
                continue;
            }

            // Génération de la recommandation IA si le champ est vide
            if (empty(trim((string) $maintenance->getRecommandation()))) {
                try {
                    $recommendation = $this->geminiService->generateRecommendation($maintenance);
                    if (!empty($recommendation)) {
                        $maintenance->setRecommandation($recommendation);
                        $needsFlush = true;
                    }
                } catch (\Throwable) {
                    // Non-bloquant — ignorer
                }
            }

            $hasCritical  = $this->alertService->hasCriticalAlerts($maintenance);
            $alertCounts  = $this->alertService->countAlertsByLevel($maintenance);

            if ($hasCritical) {
                $statisticsAlerts['total_critical']++;
            }
            foreach ($alertCounts as $level => $count) {
                if (isset($statisticsAlerts[$level]) && $level !== 'total') {
                    $statisticsAlerts[$level] += $count;
                }
            }

            $maintenancesWithAlerts[] = [
                'maintenance' => $maintenance,
                'alertStatus' => $this->alertService->getAlertStatus($maintenance),
                'alerts'      => $this->alertService->generateIntelligentAlerts($maintenance),
                'hasCritical' => $hasCritical,
                'alertSummary'=> $this->alertService->getAlertSummary($maintenance),
            ];
        }

        if ($needsFlush) {
            try {
                $entityManager->flush();
            } catch (\Throwable) {
                // Non-bloquant
            }
        }

        return $this->render('maintenances/index.html.twig', [
            'maintenancesWithAlerts'  => $maintenancesWithAlerts,
            'search'                  => $search,
            'type'                    => $type,
            'sort'                    => $sort,
            'direction'               => $direction,
            'maintenancesPagination'  => $pagination,
            'countByType'             => $repository->countByTypePanne(),
            'coutByMonth'             => $repository->getCoutByMonth(),
            'totalCout'               => $repository->getTotalCout(),
            'countByStatut'           => $repository->countByStatut(),
            'countByPriorite'         => $repository->countByPriorite(),
            'statisticsAlerts'        => $statisticsAlerts,
        ]);
    }

    #[Route('/new', name: 'agri_maintenances_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $maintenance = new Maintenance();
        $form = $this->createForm(MaintenanceType::class, $maintenance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($maintenance);
            $entityManager->flush();

            $this->addFlash('success', 'Maintenance ajoutée avec succès.');
            return $this->redirectToRoute('agri_maintenances_index');
        }

        return $this->render('maintenances/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'agri_maintenances_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, MaintenanceRepository $repository): Response
    {
        $maintenance = $this->findMaintenanceOr404($id, $repository);

        return $this->render('maintenances/show.html.twig', [
            'maintenance' => $maintenance,
            'alerts'      => $this->alertService->generateIntelligentAlerts($maintenance),
            'hasCritical' => $this->alertService->hasCriticalAlerts($maintenance),
        ]);
    }

    #[Route('/{id}/edit', name: 'agri_maintenances_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Maintenance $maintenance, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MaintenanceType::class, $maintenance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Maintenance mise à jour avec succès.');
            return $this->redirectToRoute('agri_maintenances_show', ['id' => $maintenance->getIdMain()]);
        }

        return $this->render('maintenances/edit.html.twig', [
            'form'        => $form->createView(),
            'maintenance' => $maintenance,
        ]);
    }

    #[Route('/{id}/delete', name: 'agri_maintenances_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Maintenance $maintenance, EntityManagerInterface $entityManager): Response
    {
        $token = $request->request->get('_token');

        if ($this->isCsrfTokenValid(
            'delete_maintenance_' . $maintenance->getIdMain(),
            is_string($token) ? $token : null,
        )) {
            $entityManager->remove($maintenance);
            $entityManager->flush();
            $this->addFlash('success', 'Maintenance supprimée avec succès.');
        } else {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
        }

        return $this->redirectToRoute('agri_maintenances_index');
    }

    // ══════════════════════════════════════════════════════════
    //  Export
    // ══════════════════════════════════════════════════════════

    #[Route('/export/excel', name: 'agri_maintenances_export_excel', methods: ['GET'])]
    public function exportExcel(MaintenanceRepository $repository): StreamedResponse
    {
        $maintenances = $repository->findAllOrderedByDate();

        $response = new StreamedResponse(function () use ($maintenances): void {
            $handle = fopen('php://output', 'w+');
            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF"); // BOM UTF-8
            fputcsv($handle, ['AGROFLOW — Rapport Maintenances'], ';');
            fputcsv($handle, ['Exporté le : ' . (new \DateTime())->format('d/m/Y H:i')], ';');
            fputcsv($handle, ['Enregistrements : ' . count($maintenances)], ';');
            fputcsv($handle, [], ';');
            fputcsv($handle, [
                '#', 'Type', 'Coût (DT)', 'Date', 'Statut', 'Priorité', 'Alerte IA',
                'Km', 'Description', 'Recommandation IA', 'ID Matériel', 'Nom Matériel',
            ], ';');

            $index = 1;
            $total = 0.0;

            foreach ($maintenances as $maintenance) {
                fputcsv($handle, [
                    $index++,
                    $maintenance->getTypePanne(),
                    number_format($maintenance->getCout() ?? 0.0, 2, ',', ' '),
                    $maintenance->getDateMain()?->format('d/m/Y') ?? '',
                    $maintenance->getStatut(),
                    $maintenance->getPriorite(),
                    $this->alertService->getAlertSummary($maintenance),
                    $maintenance->getKilometrage() ?? '',
                    $maintenance->getDescription() ?? '',
                    $maintenance->getRecommandation() ?? '',
                    $maintenance->getIdM() ? '#' . $maintenance->getIdM() : '',
                    $this->getMachineName($maintenance),
                ], ';');

                $total += $maintenance->getCout() ?? 0.0;
            }

            fputcsv($handle, [], ';');
            fputcsv($handle, ['', 'TOTAL', number_format($total, 2, ',', ' ') . ' DT'], ';');
            fclose($handle);
        });

        $filename = 'maintenances_' . (new \DateTime())->format('Ymd_Hi') . '.csv';
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }

    #[Route('/export/pdf', name: 'agri_maintenances_export_pdf', methods: ['GET'])]
    public function exportPdf(MaintenanceRepository $repository): Response
    {
        $maintenances           = $repository->findAllOrderedByDate();
        $maintenancesWithAlerts = [];

        foreach ($maintenances as $maintenance) {
            $maintenancesWithAlerts[] = [
                'maintenance'  => $maintenance,
                'alertSummary' => $this->alertService->getAlertSummary($maintenance),
                'hasCritical'  => $this->alertService->hasCriticalAlerts($maintenance),
            ];
        }

        return $this->render('maintenances/pdf.html.twig', [
            'maintenances' => $maintenancesWithAlerts,
            'generatedAt'  => new \DateTime(),
            'totalCout'    => $repository->getTotalCout(),
        ]);
    }

    // ══════════════════════════════════════════════════════════
    //  Chatbot — redirection permanente
    // ══════════════════════════════════════════════════════════

    /**
     * Ancienne URL /agriculteur/maintenances/chatbot → nouvelle URL /agriculteur/chatbot
     *
     * IMPORTANT : cette route DOIT être déclarée AVANT les routes paramétrées
     * /{id} et /{id}/edit pour éviter que Symfony ne tente de convertir
     * "chatbot" en entier.
     */
    #[Route('/chatbot', name: 'agri_maintenances_chatbot', methods: ['GET'])]
    public function chatbotRedirect(): Response
    {
        return $this->redirectToRoute('agri_chatbot_index', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    // ══════════════════════════════════════════════════════════
    //  API — Alertes
    // ══════════════════════════════════════════════════════════

    #[Route('/api/alerts/{id}', name: 'agri_maintenances_api_alerts', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getAlerts(int $id, MaintenanceRepository $repository): JsonResponse
    {
        try {
            $maintenance = $this->findMaintenance($id, $repository);
            if (!$maintenance) {
                return $this->json(['success' => false, 'error' => 'Maintenance non trouvée'], 404);
            }

            return $this->json([
                'success'     => true,
                'alerts'      => $this->alertService->generateIntelligentAlerts($maintenance),
                'hasCritical' => $this->alertService->hasCriticalAlerts($maintenance),
                'summary'     => $this->alertService->getAlertSummary($maintenance),
                'counts'      => $this->alertService->countAlertsByLevel($maintenance),
            ]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[Route('/api/alerts-summary', name: 'agri_maintenances_api_alerts_summary', methods: ['GET'])]
    public function getAllAlertsSummary(MaintenanceRepository $repository): JsonResponse
    {
        try {
            $maintenances = $repository->findAll();
            $globalStats  = ['urgente' => 0, 'haute' => 0, 'moyenne' => 0, 'faible' => 0, 'total_critical' => 0];
            $items        = [];

            foreach ($maintenances as $maintenance) {
                $hasCritical = $this->alertService->hasCriticalAlerts($maintenance);
                $counts      = $this->alertService->countAlertsByLevel($maintenance);

                if ($hasCritical) {
                    $globalStats['total_critical']++;
                }
                foreach ($counts as $level => $count) {
                    if (isset($globalStats[$level]) && $level !== 'total') {
                        $globalStats[$level] += $count;
                    }
                }

                $items[] = [
                    'id'          => $maintenance->getIdMain(),
                    'hasCritical' => $hasCritical,
                    'summary'     => $this->alertService->getAlertSummary($maintenance),
                ];
            }

            return $this->json(['success' => true, 'global' => $globalStats, 'maintenances' => $items]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ══════════════════════════════════════════════════════════
    //  API — Rappels calendrier
    // ══════════════════════════════════════════════════════════

    #[Route('/api/calendar/reminder-dates', name: 'agri_maintenances_api_reminder_dates', methods: ['GET'])]
    public function getReminderDates(MaintenanceRepository $repository): JsonResponse
    {
        try {
            $maintenances  = $repository->findAll();
            $reminderDates = [];
            $now           = new \DateTime();

            foreach ($maintenances as $maintenance) {
                $dateMain = $maintenance->getDateMain();
                if (!$dateMain) {
                    continue;
                }

                $interval    = $dateMain->diff($now);
                $yearsSince  = $interval->y;
                $monthsSince = $interval->m;
                $isReminder  = ($yearsSince >= 1) || ($maintenance->getPriorite() === 'urgente');

                if (!$isReminder) {
                    continue;
                }

                $machineName = $this->getMachineName($maintenance);

                if ($yearsSince >= 1) {
                    $ageText = "Il y a {$yearsSince} an" . ($yearsSince > 1 ? 's' : '');
                    if ($monthsSince > 0) {
                        $ageText .= " et {$monthsSince} mois";
                    }
                    $message = "🔔 RAPPEL ANNUEL : {$machineName} n'a pas eu de maintenance depuis {$ageText}.";
                } else {
                    $ageText = 'Maintenance urgente';
                    $message = "🔴 URGENT : {$machineName} nécessite une intervention immédiate !";
                }

                $reminderDates[] = [
                    'id'          => $maintenance->getIdMain(),
                    'date'        => $dateMain->format('Y-m-d'),
                    'machineName' => $machineName,
                    'type'        => $maintenance->getTypePanne(),
                    'priorite'    => $maintenance->getPriorite(),
                    'statut'      => $maintenance->getStatut(),
                    'cout'        => $maintenance->getCout(),
                    'km'          => $maintenance->getKilometrage(),
                    'description' => $maintenance->getDescription(),
                    'yearsSince'  => $yearsSince,
                    'monthsSince' => $monthsSince,
                    'message'     => $message,
                    'ageText'     => $ageText,
                ];
            }

            return $this->json([
                'success'   => true,
                'reminders' => $reminderDates,
                'total'     => count($reminderDates),
            ]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[Route('/api/calendar/reminder-detail/{id}', name: 'agri_maintenances_api_reminder_detail', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getReminderDetail(int $id, MaintenanceRepository $repository): JsonResponse
    {
        try {
            $maintenance = $repository->findOneWithMaterielName($id);
            if (!$maintenance) {
                return $this->json(['success' => false, 'error' => 'Maintenance non trouvée'], 404);
            }

            $now         = new \DateTime();
            $dateMain    = $maintenance->getDateMain();
            $interval    = $dateMain ? $dateMain->diff($now) : null;
            $yearsSince  = $interval?->y ?? 0;
            $monthsSince = $interval?->m ?? 0;
            $kilometers  = $maintenance->getKilometrage() ?? 0;
            $machineName = $this->getMachineName($maintenance);

            return $this->json([
                'success'          => true,
                'id'               => $maintenance->getIdMain(),
                'machineName'      => $machineName,
                'type'             => $maintenance->getTypePanne(),
                'priorite'         => $maintenance->getPriorite(),
                'statut'           => $maintenance->getStatut(),
                'dateMain'         => $dateMain?->format('Y-m-d'),
                'yearsSince'       => $yearsSince,
                'monthsSince'      => $monthsSince,
                'kilometers'       => $kilometers,
                'cout'             => $maintenance->getCout(),
                'description'      => $maintenance->getDescription(),
                'urgenceLevel'     => $this->calculateUrgenceLevel($maintenance, $yearsSince),
                'aiRecommendation' => $this->generateAIRecommendationForReminder($maintenance, $yearsSince, $kilometers),
                'message'          => $yearsSince >= 1
                    ? "🔔 RAPPEL ANNUEL : {$machineName} n'a pas eu de maintenance depuis {$yearsSince} an" . ($yearsSince > 1 ? 's' : '')
                    : "🔴 URGENT : {$machineName} nécessite une intervention immédiate !",
            ]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ══════════════════════════════════════════════════════════
    //  API — Recommandation IA
    // ══════════════════════════════════════════════════════════

    #[Route('/api/generate-recommendation/{id}', name: 'agri_maintenances_api_generate_recommendation', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function generateRecommendation(int $id, MaintenanceRepository $repository, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $maintenance = $this->findMaintenance($id, $repository);
            if (!$maintenance) {
                return $this->json(['success' => false, 'error' => 'Maintenance non trouvée'], 404);
            }

            $recommendation = $this->geminiService->generateRecommendation($maintenance);

            if (!empty($recommendation)) {
                $maintenance->setRecommandation($recommendation);
                $entityManager->flush();
            }

            return $this->json([
                'success'        => true,
                'id'             => $maintenance->getIdMain(),
                'recommendation' => $recommendation ?: $this->geminiService->getFallbackRecommendation($maintenance),
            ]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ══════════════════════════════════════════════════════════
    //  Page Rappels calendrier
    // ══════════════════════════════════════════════════════════

    #[Route('/calendar-reminders', name: 'agri_maintenances_calendar_reminders_page', methods: ['GET'])]
    public function calendarRemindersPage(MaintenanceRepository $repository): Response
    {
        return $this->render('maintenances/calendar_reminders.html.twig', [
            'maintenances' => $repository->findAllOrderedByDate(),
            'totalCout'    => $repository->getTotalCout(),
            'countByType'  => $repository->countByTypePanne(),
        ]);
    }

    // ══════════════════════════════════════════════════════════
    //  Méthodes privées
    // ══════════════════════════════════════════════════════════

    /**
     * Calcule le niveau d'urgence d'une maintenance.
     *
     * @return array{level: string, label: string, score: int}
     */
    private function calculateUrgenceLevel(Maintenance $maintenance, int $yearsSince): array
    {
        $priority   = $maintenance->getPriorite();
        $kilometers = $maintenance->getKilometrage() ?? 0;

        if ($priority === 'urgente' || $yearsSince >= 2) {
            return ['level' => 'critique', 'label' => '🔴 CRITIQUE — Intervention immédiate', 'score' => 90];
        }
        if ($priority === 'haute' || $yearsSince >= 1 || $kilometers > 15_000) {
            return ['level' => 'elevated', 'label' => '🟠 ÉLEVÉ — Planifier sous 48 h', 'score' => 70];
        }
        if ($kilometers > 10_000) {
            return ['level' => 'moderate', 'label' => '🟡 MODÉRÉ — Planifier sous 30 jours', 'score' => 50];
        }

        return ['level' => 'faible', 'label' => '🟢 FAIBLE — Surveillance normale', 'score' => 20];
    }

    /**
     * Génère une recommandation locale (sans API) pour les rappels calendrier.
     */
    private function generateAIRecommendationForReminder(
        Maintenance $maintenance,
        int $yearsSince,
        int $kilometers,
    ): string {
        $type     = $maintenance->getTypePanne();
        $priority = $maintenance->getPriorite();

        $header = match (true) {
            $priority === 'urgente' => "🚨 URGENCE — Intervention immédiate requise !\n\n",
            $yearsSince >= 2        => "⚠️ CRITIQUE — Plus de 2 ans sans maintenance. Révision majeure obligatoire !\n\n",
            $yearsSince >= 1        => "🔔 RAPPEL ANNUEL — Maintenance préventive recommandée.\n\n",
            default                 => "💡 Recommandation :\n\n",
        };

        $kmSection = match (true) {
            $kilometers >= 15_000 => "📊 Kilométrage CRITIQUE (" . number_format($kilometers, 0, ',', ' ') . " km) :\n• Révision majeure OBLIGATOIRE\n• Vidange complète + tous les filtres\n• Contrôle exhaustif moteur et transmission\n\n",
            $kilometers >= 10_000 => "📊 Kilométrage ÉLEVÉ (" . number_format($kilometers, 0, ',', ' ') . " km) :\n• Révision générale conseillée\n• Vidange moteur et changement des filtres\n\n",
            $kilometers >=  5_000 => "📊 Kilométrage INTERMÉDIAIRE (" . number_format($kilometers, 0, ',', ' ') . " km) :\n• Vidange et entretien courant requis\n\n",
            default               => '',
        };

        $typeSection = match ($type) {
            'Moteur'       => "🔧 PANNE MOTEUR :\n• Vérifier la compression\n• Contrôler les niveaux d'huile\n• Inspecter les injecteurs\n\n",
            'Électricité'  => "⚡ PANNE ÉLECTRIQUE :\n• Tester batterie et alternateur\n• Vérifier les fusibles\n• Diagnostic OBD\n\n",
            'Hydraulique'  => "💧 PANNE HYDRAULIQUE :\n• Contrôler le niveau de fluide\n• Inspecter les flexibles\n• Rechercher les fuites\n\n",
            'Transmission' => "⚙️ TRANSMISSION :\n• Vérifier niveaux huile boîte\n• Contrôler les courroies\n• Tester l'embrayage\n\n",
            default        => "🔍 DIAGNOSTIC GÉNÉRAL :\n• Inspection visuelle complète\n• Vérification des niveaux\n• Test de fonctionnement\n\n",
        };

        $safety = "⚠️ SÉCURITÉ :\n• Port des EPI obligatoire\n• Couper le moteur avant intervention\n• Déconnecter la batterie (pôle négatif)\n\n";

        $nextKm      = $kilometers > 0 ? (int) (ceil(($kilometers + 1) / 5000) * 5000) : 5000;
        $remaining   = max(0, $nextKm - $kilometers);
        $preventive  = "📋 MAINTENANCE PRÉVENTIVE :\n";
        $preventive .= "• Prochaine vidange dans " . number_format($remaining, 0, ',', ' ') . " km\n";
        $preventive .= "• Contrôle hebdomadaire : niveaux, pression pneus\n";
        $preventive .= "• Tenir à jour le carnet de maintenance";

        return $header . $kmSection . $typeSection . $safety . $preventive;
    }

    /**
     * Récupère le nom lisible de la machine associée à une maintenance.
     * Priorité : getNomMateriel() → getNom() → "Machine #ID" → 'Machine non définie'
     */
    private function getMachineName(Maintenance $maintenance): string
    {
        // Méthode dédiée (ajoutée par jointure DQL dans le repository)
        if (method_exists($maintenance, 'getNomMateriel')) {
            $name = $maintenance->getNomMateriel();
            if (!empty($name)) {
                return (string) $name;
            }
        }

        // Relation chargée : $maintenance->getIdM() peut retourner l'objet Machine
        $machine = method_exists($maintenance, 'getIdM') ? $maintenance->getIdM() : null;

        if ($machine !== null && !is_int($machine) && !is_string($machine)) {
            // C'est un objet Machine/Materiel — essayer des getters courants
            foreach (['getNom', 'getLibelle', 'getDesignation', 'getMarque'] as $getter) {
                if (method_exists($machine, $getter)) {
                    $val = $machine->$getter();
                    if (!empty($val)) {
                        return (string) $val;
                    }
                }
            }
            return 'Machine #' . (method_exists($machine, 'getId') ? $machine->getId() : '?');
        }

        // getIdM() retourne un scalaire (clé étrangère brute)
        if ($machine !== null) {
            return "Machine #{$machine}";
        }

        return 'Machine non définie';
    }

    /**
     * Cherche une maintenance par son ID (avec jointure, puis fallback simple).
     */
    private function findMaintenance(int $id, MaintenanceRepository $repository): ?Maintenance
    {
        try {
            $maintenance = $repository->findOneWithMaterielName($id);
            if ($maintenance instanceof Maintenance) {
                return $maintenance;
            }
        } catch (\Throwable) {
            // Ignorer
        }

        $fallback = $repository->findOneBy(['idMain' => $id]);
        return $fallback instanceof Maintenance ? $fallback : null;
    }

    /**
     * Trouve une maintenance ou lève une 404.
     */
    private function findMaintenanceOr404(int $id, MaintenanceRepository $repository): Maintenance
    {
        $maintenance = $this->findMaintenance($id, $repository);
        if (!$maintenance) {
            throw $this->createNotFoundException("Maintenance #{$id} non trouvée.");
        }
        return $maintenance;
    }
}