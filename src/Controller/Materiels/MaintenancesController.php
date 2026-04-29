<?php

namespace App\Controller\Materiels;

use App\Entity\Materiels\Maintenance;
use App\Form\Materiels\MaintenanceType;
use App\Repository\Materiels\MaintenanceRepository;
use App\Service\MaintenanceAlertService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CORRECTIONS APPORTÉES :
 * 1. Toutes les routes /api/... sont placées AVANT /{id} pour éviter les conflits Symfony
 * 2. findOneWithMaterielName() est sécurisé avec fallback sur findOneBy(['idMain'=>$id])
 * 3. getMachineName() helper centralisé pour récupérer le nom de la machine
 * 4. aiSuggestMaintenance() utilise findBy(['idMain'=>$idMaintenance]) pour l'historique
 * 5. JSON responses avec headers explicites pour éviter les erreurs de parsing côté JS
 * 6. Gestion d'erreur améliorée dans tous les endpoints API
 */
#[Route('/agriculteur/maintenances')]
class MaintenancesController extends AbstractController
{
    private MaintenanceAlertService $alertService;

    public function __construct(MaintenanceAlertService $alertService)
    {
        $this->alertService = $alertService;
    }

    // ────────────────────────────────────────────────────────
    // INDEX
    // ────────────────────────────────────────────────────────
    #[Route('', name: 'agri_maintenances_index', methods: ['GET'])]
    public function index(Request $request, MaintenanceRepository $repo, PaginatorInterface $paginator): Response
    {
        $search = (string) $request->query->get('search', '');
        $type   = (string) $request->query->get('type', '');
        $sort   = (string) $request->query->get('sort', 'dateMain');
        $dir    = (string) $request->query->get('dir', 'DESC');

        $maintenances = $repo->searchWithMaterielName($search, $type, $sort, $dir);
        $pagination = $paginator->paginate(
            $maintenances,
            $request->query->getInt('page', 1),
            10
        );

        $maintenancesWithAlerts = [];
        foreach ($pagination->getItems() as $m) {
            if (!$m instanceof Maintenance) {
                continue;
            }
            $maintenancesWithAlerts[] = [
                'maintenance' => $m,
                'alert'       => $this->alertService->getAlertStatus($m),
            ];
        }

        return $this->render('maintenances/index.html.twig', [
            'maintenancesWithAlerts' => $maintenancesWithAlerts,
            'search'          => $search,
            'type'            => $type,
            'sort'            => $sort,
            'dir'             => $dir,
            'maintenancesPagination' => $pagination,
            'countByType'     => $repo->countByTypePanne(),
            'coutByMonth'     => $repo->getCoutByMonth(),
            'totalCout'       => $repo->getTotalCout(),
            'countByStatut'   => $repo->countByStatut(),
            'countByPriorite' => $repo->countByPriorite(),
        ]);
    }

    // ────────────────────────────────────────────────────────
    // CREATE  (AVANT /{id} !)
    // ────────────────────────────────────────────────────────
    #[Route('/new', name: 'agri_maintenances_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $maintenance = new Maintenance();
        $form = $this->createForm(MaintenanceType::class, $maintenance);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($maintenance);
            $em->flush();
            $this->addFlash('success', 'Maintenance ajoutée avec succès.');
            return $this->redirectToRoute('agri_maintenances_index');
        }
        return $this->render('maintenances/new.html.twig', ['form' => $form->createView()]);
    }

    // ────────────────────────────────────────────────────────
    // EXPORT EXCEL  (AVANT /{id} !)
    // ────────────────────────────────────────────────────────
    #[Route('/export/excel', name: 'agri_maintenances_export_excel', methods: ['GET'])]
    public function exportExcel(MaintenanceRepository $repo): StreamedResponse
    {
        $maintenances = $repo->findAllOrderedByDate();
        $response = new StreamedResponse(function () use ($maintenances) {
            $handle = fopen('php://output', 'w+');
            if ($handle === false) {
                return;
            }
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['AGROFLOW — Rapport Maintenances'], ';');
            fputcsv($handle, ['Exporté le : ' . (new \DateTime())->format('d/m/Y H:i')], ';');
            fputcsv($handle, ['Enregistrements : ' . count($maintenances)], ';');
            fputcsv($handle, [], ';');
            fputcsv($handle, ['#', 'Type', 'Coût (DT)', 'Date', 'Statut', 'Priorité', 'Km', 'Description', 'Recommandation', 'ID Matériel', 'Nom Matériel'], ';');
            $idx = 1; $total = 0.0;
            foreach ($maintenances as $m) {
                fputcsv($handle, [
                    $idx++,
                    $m->getTypePanne(),
                    number_format($m->getCout() ?? 0.0, 2, ',', ' '),
                    $m->getDateMain()?->format('d/m/Y') ?? '',
                    $m->getStatut(),
                    $m->getPriorite(),
                    $m->getKilometrage() ?? '',
                    $m->getDescription() ?? '',
                    $m->getRecommandation() ?? '',
                    $m->getIdM() ? '#' . $m->getIdM() : '',
                    $this->getMachineName($m),
                ], ';');
                $total += $m->getCout() ?? 0.0;
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

    // ────────────────────────────────────────────────────────
    // EXPORT PDF  (AVANT /{id} !)
    // ────────────────────────────────────────────────────────
    #[Route('/export/pdf', name: 'agri_maintenances_export_pdf', methods: ['GET'])]
    public function exportPdf(MaintenanceRepository $repo): Response
    {
        return $this->render('maintenances/pdf.html.twig', [
            'maintenances' => $repo->findAllOrderedByDate(),
            'generatedAt'  => new \DateTime(),
            'totalCout'    => $repo->getTotalCout(),
        ]);
    }

    // ────────────────────────────────────────────────────────
    // CALENDRIER DES RAPPELS ANNUELS
    // ────────────────────────────────────────────────────────
    #[Route('/calendar-reminders', name: 'agri_maintenances_calendar_reminders_page', methods: ['GET'])]
    public function calendarRemindersPage(MaintenanceRepository $repo): Response
    {
        return $this->render('maintenances/calendar_reminders.html.twig', [
            'maintenances' => $repo->findAllOrderedByDate(),
            'totalCout' => $repo->getTotalCout(),
            'countByType' => $repo->countByTypePanne(),
        ]);
    }

    // API : Récupérer les dates de rappel (dates surlignées en rouge)
    #[Route('/api/calendar/reminder-dates', name: 'agri_maintenances_api_reminder_dates', methods: ['GET'])]
    public function getReminderDates(MaintenanceRepository $repo): JsonResponse
    {
        try {
            $maintenances = $repo->findAll();
            $reminderDates = [];
            $now = new \DateTime();

            foreach ($maintenances as $m) {
                $dateMain = $m->getDateMain();
                if (!$dateMain) continue;

                $machineName = $this->getMachineName($m);
                $interval = $dateMain->diff($now);
                $yearsSince = $interval->y;
                $monthsSince = $interval->m;
                
                $isReminder = ($yearsSince >= 1) || ($m->getPriorite() === 'urgente');
                
                if (!$isReminder) continue;

                if ($yearsSince >= 1) {
                    $ageText = "Il y a {$yearsSince} an" . ($yearsSince > 1 ? 's' : '');
                    if ($monthsSince > 0) $ageText .= " et {$monthsSince} mois";
                    $message = "🔔 RAPPEL ANNUEL : {$machineName} n'a pas eu de maintenance depuis {$ageText}.";
                } else {
                    $message = "🔴 URGENT : {$machineName} nécessite une intervention immédiate !";
                }

                $reminderDates[] = [
                    'id' => $m->getIdMain(),
                    'date' => $dateMain->format('Y-m-d'),
                    'machineName' => $machineName,
                    'type' => $m->getTypePanne(),
                    'priorite' => $m->getPriorite(),
                    'statut' => $m->getStatut(),
                    'cout' => $m->getCout(),
                    'km' => $m->getKilometrage(),
                    'description' => $m->getDescription(),
                    'yearsSince' => $yearsSince,
                    'monthsSince' => $monthsSince,
                    'message' => $message,
                    'ageText' => $ageText ?? 'Maintenance urgente',
                ];
            }

            return $this->json([
                'success' => true,
                'reminders' => $reminderDates,
                'total' => count($reminderDates)
            ]);

        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // API : Détails d'un rappel spécifique
    #[Route('/api/calendar/reminder-detail/{id}', name: 'agri_maintenances_api_reminder_detail', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getReminderDetail(int $id, MaintenanceRepository $repo): JsonResponse
    {
        try {
            $maintenance = $this->findMaintenance($id, $repo);
            if (!$maintenance) {
                return $this->json(['success' => false, 'error' => 'Maintenance non trouvée'], 404);
            }

            $machineName = $this->getMachineName($maintenance);
            $dateMain = $maintenance->getDateMain();
            $now = new \DateTime();
            $interval = $dateMain ? $dateMain->diff($now) : null;
            $yearsSince = $interval ? $interval->y : 0;
            $monthsSince = $interval ? $interval->m : 0;
            
            $recommendations = $this->generateReminderRecommendations($maintenance, $yearsSince);
            $urgenceLevel = $this->calculateReminderUrgence($maintenance, $yearsSince);

            return $this->json([
                'success' => true,
                'id' => $maintenance->getIdMain(),
                'machineName' => $machineName,
                'type' => $maintenance->getTypePanne(),
                'priorite' => $maintenance->getPriorite(),
                'statut' => $maintenance->getStatut(),
                'dateMain' => $dateMain?->format('d/m/Y'),
                'cout' => $maintenance->getCout(),
                'km' => $maintenance->getKilometrage(),
                'description' => $maintenance->getDescription(),
                'recommandation' => $maintenance->getRecommandation(),
                'yearsSince' => $yearsSince,
                'monthsSince' => $monthsSince,
                'urgenceLevel' => $urgenceLevel,
                'recommendations' => $recommendations,
                'message' => $this->getReminderMessage($machineName, $yearsSince, $monthsSince, $maintenance->getPriorite()),
            ]);

        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ────────────────────────────────────────────────────────
    // API : Calendrier des rappels annuels (ancien)
    // ────────────────────────────────────────────────────────
    #[Route('/api/calendar/reminders', name: 'api_maintenance_calendar_reminders', methods: ['GET'])]
    public function getCalendarReminders(MaintenanceRepository $repo): JsonResponse
    {
        try {
            $maintenances = $repo->findAll();
            $events = [];
            $now = new \DateTime();

            foreach ($maintenances as $m) {
                $dateMain = $m->getDateMain();
                if (!$dateMain) continue;

                $interval    = $dateMain->diff($now);
                $yearsSince  = $interval->y;
                $monthsSince = $interval->m;
                $isReminder  = ($yearsSince >= 1) || ($m->getPriorite() === 'urgente');

                if (!$isReminder) continue;

                $machineName = $this->getMachineName($m);
                $id          = $m->getIdMain();

                if ($yearsSince >= 1) {
                    $ageText = "Il y a {$yearsSince} an" . ($yearsSince > 1 ? 's' : '');
                    if ($monthsSince > 0) $ageText .= " et {$monthsSince} mois";
                } else {
                    $ageText = 'URGENT - Intervention immédiate requise';
                }

                $events[] = [
                    'id'              => $id,
                    'title'           => '🔔 ' . $machineName,
                    'start'           => $dateMain->format('Y-m-d'),
                    'backgroundColor' => '#e74a3b',
                    'borderColor'     => '#c0392b',
                    'textColor'       => '#ffffff',
                    'classNames'      => ['reminder-event'],
                    'extendedProps'   => [
                        'machine'         => $machineName,
                        'type'            => $m->getTypePanne(),
                        'lastMaintenance' => $dateMain->format('d/m/Y'),
                        'age'             => $ageText,
                        'priority'        => $m->getPriorite(),
                        'maintenanceId'   => $id,
                    ],
                ];
            }

            return $this->json($events);

        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ────────────────────────────────────────────────────────
    // API : Suggestion automatique IA
    // ────────────────────────────────────────────────────────
    #[Route('/api/ai-suggest-maintenance/{id}', name: 'agri_maintenances_api_ai_suggest', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function aiSuggestMaintenance(int $id, MaintenanceRepository $repo): JsonResponse
    {
        try {
            $maintenance = $this->findMaintenance($id, $repo);
            if (!$maintenance) {
                return $this->json(['success' => false, 'error' => "Maintenance #{$id} non trouvée."], 404);
            }

            $machineHistory = $this->getMachineHistory($maintenance, $repo);
            $frequency = $this->analyzeMaintenanceFrequency($machineHistory);
            $currentState = $this->analyzeMachineState($maintenance);
            $suggestions = $this->generateAISuggestions($maintenance, $frequency, $currentState);
            $nextDate = $this->calculateNextRecommendedDate($maintenance, $frequency);
            $urgencyScore = $this->calculateUrgencyScore($maintenance, $frequency, $currentState);

            return $this->json([
                'success'            => true,
                'suggestions'        => $suggestions,
                'frequency'          => $frequency,
                'currentState'       => $currentState,
                'nextRecommendedDate'=> $nextDate?->format('d/m/Y'),
                'urgencyScore'       => $urgencyScore,
                'urgencyLevel'       => $this->getUrgencyLevel($urgencyScore),
                'recommendedActions' => $this->getRecommendedActions($urgencyScore, $maintenance),
            ]);

        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'error'   => 'Erreur serveur : ' . $e->getMessage(),
            ], 500);
        }
    }

    // ────────────────────────────────────────────────────────
    // API : RECOMMANDATION IA INTELLIGENTE (NOUVELLE)
    // ────────────────────────────────────────────────────────
    #[Route('/api/ai-intelligent-recommendation/{id}', name: 'agri_maintenances_api_ai_intelligent', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getIntelligentRecommendation(int $id, MaintenanceRepository $repo): JsonResponse
    {
        try {
            $maintenance = $this->findMaintenance($id, $repo);
            if (!$maintenance) {
                return $this->json(['success' => false, 'error' => 'Maintenance non trouvée'], 404);
            }

            $km = $maintenance->getKilometrage() ?? 0;
            $priorite = $maintenance->getPriorite();
            $statut = $maintenance->getStatut();
            $typePanne = $maintenance->getTypePanne() ?? 'Générale';
            $machineName = $this->getMachineName($maintenance);
            
            $riskScore = $this->calculateRiskScore($km, $priorite, $statut);
            $riskLevel = $this->getRiskLevel($riskScore);
            $nextKmRecommendation = $this->calculateNextKmRecommendation($km);
            $mainMessage = $this->getIntelligentMainMessage($machineName, $priorite, $statut, $km);
            $interpretation = $this->getIntelligentInterpretation($km, $priorite, $statut, $riskLevel);
            $actions = $this->getIntelligentActions($priorite, $statut, $km, $nextKmRecommendation);
            $additionalTips = $this->getAdditionalTips($typePanne, $km, $priorite);

            return $this->json([
                'success' => true,
                'id' => $maintenance->getIdMain(),
                'machineName' => $machineName,
                'km' => $km,
                'priorite' => $priorite,
                'statut' => $statut,
                'typePanne' => $typePanne,
                'riskScore' => $riskScore,
                'riskLevel' => $riskLevel,
                'nextKmRecommendation' => $nextKmRecommendation,
                'mainMessage' => $mainMessage,
                'interpretation' => $interpretation,
                'actions' => $actions,
                'additionalTips' => $additionalTips,
                'isUrgent' => ($priorite === 'urgente' || $statut === 'en_cours'),
            ]);
            
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ────────────────────────────────────────────────────────
    // API : Prompt libre IA
    // ────────────────────────────────────────────────────────
    #[Route('/api/generate-custom-prompt/{id}', name: 'agri_maintenances_api_custom_prompt', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function generateCustomPrompt(int $id, Request $request, MaintenanceRepository $repo): JsonResponse
    {
        try {
            $maintenance = $this->findMaintenance($id, $repo);
            if (!$maintenance) {
                return $this->json(['success' => false, 'error' => 'Maintenance non trouvée'], 404);
            }

            $dataRaw = json_decode($request->getContent(), true);
            $data = is_array($dataRaw) ? $dataRaw : [];
            $prompt = trim(isset($data['prompt']) && is_string($data['prompt']) ? $data['prompt'] : '');
            if (!$prompt) {
                return $this->json(['success' => false, 'error' => 'Prompt vide'], 400);
            }

            $response = $this->buildLocalPromptResponse($prompt, $maintenance);

            return $this->json(['success' => true, 'response' => $response]);

        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ────────────────────────────────────────────────────────
    // API : Durée de vie IA
    // ────────────────────────────────────────────────────────
    #[Route('/api/lifetime/{id}', name: 'agri_maintenances_api_lifetime', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function generateLifetime(int $id, MaintenanceRepository $repo): JsonResponse
    {
        try {
            $maintenance = $this->findMaintenance($id, $repo);
            if (!$maintenance) {
                return $this->json(['success' => false, 'error' => 'Maintenance non trouvée'], 404);
            }

            $km    = $maintenance->getKilometrage() ?? 0;
            $prio  = $maintenance->getPriorite();
            $type  = $maintenance->getTypePanne() ?? 'Général';
            $maxKm = 20000;

            $vieRestante = max(0, min(100, (int) round((($maxKm - $km) / $maxKm) * 100)));
            $penalite = match($prio) {
                'urgente' => 25, 'haute' => 15, 'moyenne' => 8, default => 3,
            };
            $vieRestante    = max(0, $vieRestante - $penalite);
            $anneesEstimees = round(max(0, ($maxKm - $km)) / 3000, 1);
            $risque = match(true) {
                $prio === 'urgente' || $km > 15000 => 'Élevé',
                $prio === 'haute'   || $km > 10000 => 'Moyen',
                default                            => 'Faible',
            };

            $composants = [
                ['nom' => 'Moteur',       'usure' => min(100, (int) round($km / 200))],
                ['nom' => 'Transmission', 'usure' => min(100, (int) round($km / 300))],
                ['nom' => 'Hydraulique',  'usure' => min(100, (int) round($km / 400))],
                ['nom' => 'Électricité',  'usure' => min(100, (int) round($km / 600))],
                ['nom' => 'Pneumatique',  'usure' => min(100, (int) round($km / 250))],
            ];

            $typeNorm = strtolower(explode(' ', $type)[0]);
            foreach ($composants as &$comp) {
                if (strtolower($comp['nom']) === $typeNorm) {
                    $bonus = match($prio) { 'urgente' => 30, 'haute' => 20, default => 10 };
                    $comp['usure'] = min(100, $comp['usure'] + $bonus);
                }
            }
            unset($comp);

            return $this->json([
                'success'            => true,
                'vieRestante'        => $vieRestante,
                'anneesEstimees'     => $anneesEstimees,
                'risquePanne'        => $risque,
                'composants'         => $composants,
                'recommandations'    => $this->buildLifetimeRecommendations($type, $prio, $km, $composants),
                'kilometrage'        => $km,
                'prochaine_vidange'  => max(0, (int) ceil($km / 5000) * 5000 - $km) . ' km',
                'prochaine_revision' => max(0, (int) ceil($km / 10000) * 10000 - $km) . ' km',
            ]);

        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ────────────────────────────────────────────────────────
    // API : Plan de maintenance JSON
    // ────────────────────────────────────────────────────────
    #[Route('/api/schedules/generate', name: 'agri_maintenances_api_schedule_generate', methods: ['POST'])]
    public function generateSchedule(Request $request, MaintenanceRepository $repo): JsonResponse
    {
        try {
            $dataRaw     = json_decode($request->getContent(), true);
            $data        = is_array($dataRaw) ? $dataRaw : [];
            $maintenance = null;

            if (isset($data['maintenanceId']) && is_numeric($data['maintenanceId'])) {
                $maintenance = $this->findMaintenance((int) $data['maintenanceId'], $repo);
                if (!$maintenance) {
                    return $this->json(['success' => false, 'error' => 'Maintenance non trouvée'], 404);
                }
            }

            $optionsRaw = $data['options'] ?? ['intervention','prevention','pieces','optimisation','securite','controle'];
            $options = is_array($optionsRaw)
                ? array_values(array_filter($optionsRaw, static fn ($opt): bool => is_string($opt)))
                : ['intervention','prevention','pieces','optimisation','securite','controle'];

            $machineData = [
                'type'           => $maintenance ? ($maintenance->getTypePanne() ?? 'Non spécifié') : (isset($data['typePanne']) && is_string($data['typePanne']) ? $data['typePanne'] : 'Non spécifié'),
                'priorite'       => $maintenance ? $maintenance->getPriorite() : (isset($data['priorite']) && is_string($data['priorite']) ? $data['priorite'] : 'moyenne'),
                'statut'         => $maintenance ? $maintenance->getStatut() : (isset($data['statut']) && is_string($data['statut']) ? $data['statut'] : 'planifie'),
                'kilometrage'    => $maintenance ? ($maintenance->getKilometrage() ?? 0) : (isset($data['kilometrage']) && is_numeric($data['kilometrage']) ? (int) $data['kilometrage'] : 0),
                'nom'            => $maintenance ? $this->getMachineName($maintenance) : (isset($data['nomMachine']) && is_string($data['nomMachine']) ? $data['nomMachine'] : 'Machine non spécifiée'),
                'description'    => $maintenance ? ($maintenance->getDescription() ?? '') : (isset($data['description']) && is_string($data['description']) ? $data['description'] : ''),
                'recommandation' => $maintenance ? ($maintenance->getRecommandation() ?? '') : (isset($data['recommandation']) && is_string($data['recommandation']) ? $data['recommandation'] : ''),
                'cout'           => $maintenance ? ($maintenance->getCout() ?? 0.0) : (isset($data['cout']) && is_numeric($data['cout']) ? (float) $data['cout'] : 0.0),
                'dateMain'       => $maintenance && $maintenance->getDateMain()
                                        ? $maintenance->getDateMain()->format('Y-m-d')
                                        : (isset($data['dateMain']) && is_string($data['dateMain']) ? $data['dateMain'] : null),
            ];

            $interventions = $this->calculateRecommendedInterventions($machineData);
            $plan          = $this->generateSchedulePlan($machineData, $options, $interventions);

            return $this->json(['success' => true, 'plan' => $plan, 'generatedBy' => 'local_algorithm']);

        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ────────────────────────────────────────────────────────
    // API : Page HTML Schedule
    // ────────────────────────────────────────────────────────
    #[Route('/api/schedule/{id}', name: 'agri_maintenances_api_schedule', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function apiSchedule(int $id, MaintenanceRepository $repo): Response
    {
        $maintenance = $this->findMaintenance($id, $repo);
        if (!$maintenance) throw $this->createNotFoundException('Maintenance non trouvée.');
        return $this->render('maintenances/api_schedule_html.html.twig', ['maintenance' => $maintenance]);
    }

    // ────────────────────────────────────────────────────────
    // SHOW  — /{id} en dernier !
    // ────────────────────────────────────────────────────────
    #[Route('/{id}', name: 'agri_maintenances_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, MaintenanceRepository $repo): Response
    {
        $maintenance = $this->findMaintenance($id, $repo);
        if (!$maintenance) throw $this->createNotFoundException('Maintenance non trouvée.');
        return $this->render('maintenances/show.html.twig', ['maintenance' => $maintenance]);
    }

    // ────────────────────────────────────────────────────────
    // EDIT
    // ────────────────────────────────────────────────────────
    #[Route('/{id}/edit', name: 'agri_maintenances_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Maintenance $maintenance, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(MaintenanceType::class, $maintenance);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Maintenance mise à jour avec succès.');
            return $this->redirectToRoute('agri_maintenances_show', ['id' => $maintenance->getIdMain()]);
        }
        return $this->render('maintenances/edit.html.twig', ['form' => $form->createView(), 'maintenance' => $maintenance]);
    }

    // ────────────────────────────────────────────────────────
    // DELETE
    // ────────────────────────────────────────────────────────
    #[Route('/{id}/delete', name: 'agri_maintenances_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Maintenance $maintenance, EntityManagerInterface $em): Response
    {
        $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete_maintenance_' . $maintenance->getIdMain(), is_string($token) ? $token : null)) {
            $em->remove($maintenance);
            $em->flush();
            $this->addFlash('success', 'Maintenance supprimée avec succès.');
        } else {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
        }
        return $this->redirectToRoute('agri_maintenances_index');
    }

    // ════════════════════════════════════════════════════════
    // HELPERS PRIVÉS
    // ════════════════════════════════════════════════════════

    private function findMaintenance(int $id, MaintenanceRepository $repo): ?Maintenance
    {
        try {
            $m = $repo->findOneWithMaterielName($id);
            if ($m) return $m;
        } catch (\Throwable $e) {
        }
        $fallback = $repo->findOneBy(['idMain' => $id]);
        return $fallback instanceof Maintenance ? $fallback : null;
    }

    private function getMachineName(Maintenance $m): string
    {
        if ($m->getNomMateriel()) {
            return $m->getNomMateriel();
        }
        if ($m->getNom()) {
            return $m->getNom();
        }
        return 'Machine non définie';
    }

    /**
     * @return list<Maintenance>
     */
    private function getMachineHistory(Maintenance $maintenance, MaintenanceRepository $repo): array
    {
        if ($maintenance->getIdM()) {
            try {
                /** @var list<Maintenance> $history */
                $history = $repo->findBy(['idM' => $maintenance->getIdM()], ['dateMain' => 'DESC']);
                return $history;
            } catch (\Throwable $e) {
            }
        }
        $nom = $this->getMachineName($maintenance);
        if ($nom !== 'Machine non définie') {
            try {
                /** @var list<Maintenance> $history */
                $history = $repo->findBy(['nom' => $nom], ['dateMain' => 'DESC']);
                return $history;
            } catch (\Throwable $e) {
            }
        }
        return [$maintenance];
    }

    /**
     * @param list<Maintenance> $history
     * @return array{
     *   hasHistory:bool,
     *   totalMaintenances:int,
     *   averageInterval:int|null,
     *   frequency:string,
     *   recommendedInterval:int
     * }
     */
    private function analyzeMaintenanceFrequency(array $history): array
    {
        $count = count($history);
        if ($count < 2) {
            return [
                'hasHistory'          => false,
                'totalMaintenances'   => $count,
                'averageInterval'     => null,
                'frequency'           => 'première maintenance',
                'recommendedInterval' => 90,
            ];
        }

        $totalInterval = 0;
        for ($i = 0; $i < $count - 1; $i++) {
            $d1 = $history[$i]->getDateMain();
            $d2 = $history[$i + 1]->getDateMain();
            if ($d1 && $d2) {
                $days = $d1->diff($d2)->days;
                if ($days !== false) {
                    $totalInterval += abs($days);
                }
            }
        }

        $avgInterval = (int) round($totalInterval / ($count - 1));
        $frequency   = match(true) {
            $avgInterval <= 30  => 'très fréquente',
            $avgInterval <= 90  => 'fréquente',
            $avgInterval <= 180 => 'modérée',
            default             => 'espacée',
        };

        return [
            'hasHistory'          => true,
            'totalMaintenances'   => $count,
            'averageInterval'     => $avgInterval,
            'frequency'           => $frequency,
            'recommendedInterval' => max(30, min(365, $avgInterval)),
        ];
    }

    /**
     * @return array{
     *   km:int,
     *   kmStatus:string,
     *   type:?string,
     *   priority:string,
     *   status:string,
     *   daysSinceLastMaintenance:int|null,
     *   healthScore:int
     * }
     */
    private function analyzeMachineState(Maintenance $m): array
    {
        $km       = $m->getKilometrage() ?? 0;
        $prio     = $m->getPriorite();
        $statut   = $m->getStatut();
        $lastDate = $m->getDateMain();
        $daysSince = null;
        if ($lastDate !== null) {
            $d = (new \DateTime())->diff($lastDate)->days;
            $daysSince = $d !== false ? $d : null;
        }

        return [
            'km'                        => $km,
            'kmStatus'                  => $this->getKmStatus($km),
            'type'                      => $m->getTypePanne(),
            'priority'                  => $prio,
            'status'                    => $statut,
            'daysSinceLastMaintenance'  => $daysSince,
            'healthScore'               => $this->calculateHealthScore($km, $prio, $statut),
        ];
    }

    private function getKmStatus(int $km): string
    {
        return match(true) {
            $km >= 15000 => 'critique',
            $km >= 10000 => 'élevé',
            $km >= 5000  => 'modéré',
            $km >= 1000  => 'normal',
            default      => 'faible',
        };
    }

    private function calculateHealthScore(int $km, string $prio, string $statut): int
    {
        $score = 100;
        $score -= match(true) { $km >= 15000 => 40, $km >= 10000 => 25, $km >= 5000 => 10, default => 0 };
        $score -= match($prio) { 'urgente' => 30, 'haute' => 20, 'moyenne' => 10, default => 0 };
        $score += match($statut) { 'termine' => 10, 'en_cours' => -15, default => 0 };
        return max(0, min(100, $score));
    }

    /**
     * @param array{
     *   hasHistory:bool,
     *   totalMaintenances:int,
     *   averageInterval:int|null,
     *   frequency:string,
     *   recommendedInterval:int
     * } $frequency
     * @param array{
     *   km:int,
     *   kmStatus:string,
     *   type:?string,
     *   priority:string,
     *   status:string,
     *   daysSinceLastMaintenance:int|null,
     *   healthScore:int
     * } $state
     * @return list<array{type:string,icon:string,title:string,message:string,priority:string,action:string}>
     */
    private function generateAISuggestions(Maintenance $m, array $frequency, array $state): array
    {
        $suggestions = [];
        $machineName = $this->getMachineName($m);
        $km          = $state['km'];
        $daysSince   = $state['daysSinceLastMaintenance'];

        if ($daysSince !== null && $daysSince > $frequency['recommendedInterval']) {
            $suggestions[] = [
                'type'     => 'time_overdue',
                'icon'     => '⏰',
                'title'    => 'Maintenance dépassée',
                'message'  => "La dernière maintenance de {$machineName} date de {$daysSince} jours. L'intervalle recommandé est de " . $frequency['recommendedInterval'] . " jours.",
                'priority' => 'haute',
                'action'   => 'Planifier immédiatement',
            ];
        }

        if ($km >= 5000) {
            $suggestions[] = [
                'type'     => 'km_threshold',
                'icon'     => '📊',
                'title'    => 'Kilométrage élevé',
                'message'  => "{$machineName} a atteint {$km} km. Une vidange et un contrôle général sont recommandés.",
                'priority' => $km >= 10000 ? 'urgente' : 'haute',
                'action'   => 'Programmer vidange',
            ];
        }

        if ($frequency['hasHistory'] && $frequency['averageInterval'] !== null && $frequency['averageInterval'] > 0) {
            $trend = $frequency['averageInterval'] < 60 ? 'maintenances rapprochées' : 'maintenances espacées';
            $suggestions[] = [
                'type'     => 'frequency_analysis',
                'icon'     => '📈',
                'title'    => 'Analyse de fréquence',
                'message'  => "Historique : {$frequency['totalMaintenances']} maintenance(s), intervalle moyen de {$frequency['averageInterval']} jours ({$trend}).",
                'priority' => 'basse',
                'action'   => "Consulter l'historique",
            ];
        }

        if ($state['healthScore'] < 50) {
            $suggestions[] = [
                'type'     => 'health_critical',
                'icon'     => '🆘',
                'title'    => 'État critique',
                'message'  => "Score de santé : {$state['healthScore']}/100. Une intervention urgente est nécessaire.",
                'priority' => 'urgente',
                'action'   => 'Intervenir maintenant',
            ];
        } elseif ($state['healthScore'] < 70) {
            $suggestions[] = [
                'type'     => 'health_warning',
                'icon'     => '⚠️',
                'title'    => 'Attention requise',
                'message'  => "Score de santé : {$state['healthScore']}/100. Planifier une maintenance préventive.",
                'priority' => 'haute',
                'action'   => 'Planifier',
            ];
        }

        if (empty($suggestions)) {
            $suggestions[] = [
                'type'     => 'preventive',
                'icon'     => '✅',
                'title'    => 'Maintenance préventive',
                'message'  => "{$machineName} semble en bon état. Une inspection préventive dans " . $frequency['recommendedInterval'] . " jours est recommandée.",
                'priority' => 'basse',
                'action'   => 'Planifier plus tard',
            ];
        }

        return $suggestions;
    }

    /**
     * @param array{recommendedInterval:int} $frequency
     */
    private function calculateNextRecommendedDate(Maintenance $m, array $frequency): ?\DateTime
    {
        $lastDate = $m->getDateMain();
        if (!$lastDate) return null;

        $interval  = \DateTime::createFromInterface($lastDate);
        $daysToAdd = $frequency['recommendedInterval'];
        $km        = $m->getKilometrage() ?? 0;
        $prio      = $m->getPriorite();

        if ($km > 10000) $daysToAdd = max(30, $daysToAdd - 30);
        elseif ($km > 5000) $daysToAdd = max(60, $daysToAdd - 15);

        if ($prio === 'urgente')    $daysToAdd = 7;
        elseif ($prio === 'haute')  $daysToAdd = 30;

        return $interval->modify("+{$daysToAdd} days");
    }

    /**
     * @param array{recommendedInterval:int} $frequency
     * @param array{
     *   km:int,
     *   priority:string,
     *   daysSinceLastMaintenance:int|null,
     *   healthScore:int
     * } $state
     */
    private function calculateUrgencyScore(Maintenance $m, array $frequency, array $state): int
    {
        $score      = 0;
        $daysSince  = $state['daysSinceLastMaintenance'] ?? 0;
        $recommended= $frequency['recommendedInterval'];

        if ($daysSince > $recommended) {
            $score += min(40, (int) round(($daysSince - $recommended) / max(1, $recommended) * 40));
        }

        $km = $state['km'];
        $score += match(true) { $km >= 15000 => 30, $km >= 10000 => 20, $km >= 5000 => 10, default => 0 };
        $score += match($state['priority']) { 'urgente' => 20, 'haute' => 15, 'moyenne' => 10, default => 0 };
        $score += min(10, (int) round((100 - $state['healthScore']) / 10));

        return min(100, $score);
    }

    private function getUrgencyLevel(int $score): string
    {
        return match(true) {
            $score >= 70 => 'critique',
            $score >= 50 => 'élevée',
            $score >= 30 => 'modérée',
            default      => 'faible',
        };
    }

    /** @return list<string> */
    private function getRecommendedActions(int $score, Maintenance $m): array
    {
        $actions     = [];
        $machineName = $this->getMachineName($m);

        if ($score >= 70) {
            $actions[] = "🚨 INTERVENTION IMMÉDIATE — {$machineName} nécessite une réparation urgente";
            $actions[] = "📞 Contacter le service technique d'urgence";
            $actions[] = "⛔ Ne pas utiliser la machine avant réparation";
        } elseif ($score >= 50) {
            $actions[] = "⚠️ Planifier une intervention sous 48h";
            $actions[] = "🔧 Préparer les pièces de rechange nécessaires";
            $actions[] = "📋 Réaliser un diagnostic complet";
        } elseif ($score >= 30) {
            $actions[] = "📅 Programmer une maintenance préventive sous 15 jours";
            $actions[] = "👀 Effectuer une inspection visuelle quotidienne";
            $actions[] = "📝 Tenir un journal de bord des anomalies";
        } else {
            $actions[] = "✅ Maintenir le planning de maintenance régulier";
            $actions[] = "🔍 Contrôles périodiques standards";
            $actions[] = "📊 Surveiller l'évolution des indicateurs";
        }

        $type = $m->getTypePanne() ?? '';
        if ($type === 'Moteur')       $actions[] = "🔧 Contrôler les niveaux d'huile et liquide de refroidissement";
        elseif ($type === 'Électricité') $actions[] = "⚡ Vérifier la batterie et le circuit électrique";
        elseif ($type === 'Hydraulique') $actions[] = "💧 Inspecter les flexibles et rechercher les fuites";

        return $actions;
    }

    private function buildLocalPromptResponse(string $prompt, Maintenance $maintenance): string
    {
        $type      = $maintenance->getTypePanne() ?? 'générale';
        $prio      = $maintenance->getPriorite();
        $km        = $maintenance->getKilometrage() ?? 0;
        $nom       = $this->getMachineName($maintenance);
        $promptLc  = strtolower($prompt);

        if (str_contains($promptLc, 'vérif') || str_contains($promptLc, 'étape')) {
            return "📋 RECOMMANDATIONS - Vérifications pour {$nom} (panne {$type}) :\n\n"
                . "1. 🔍 Inspection visuelle générale (fuites, câbles, fixations)\n"
                . "2. 🚀 Test de démarrage et fonctionnement à vide\n"
                . "3. 🌡️ Relevé des températures et pressions\n"
                . "4. 💧 Contrôle des niveaux (huile, liquide refroidissement, hydraulique)\n"
                . "5. 💻 Diagnostic électronique si disponible\n"
                . "6. ⚡ Test de charge progressive\n"
                . ($prio === 'urgente' ? "\n⚠️ RECOMMANDATION URGENTE : Ne pas utiliser la machine avant réparation." : '');
        }

        if (str_contains($promptLc, 'pièce') || str_contains($promptLc, 'remplacer')) {
            $rec  = "🔧 RECOMMANDATIONS - Pièces à contrôler/remplacer pour {$nom} :\n\n";
            $rec .= "| Pièce | Action | Priorité |\n|-------|--------|----------|\n";
            $rec .= "| Filtres (air, huile, carburant) | Vérifier et remplacer si nécessaire | Haute |\n";
            $rec .= "| Courroies et chaînes | Contrôler l'usure | Haute |\n";
            $rec .= "| Joints et flexibles | Inspection fuites | Moyenne |\n";
            if ($km > 5000)  $rec .= "| Huile moteur + filtre | Vidange obligatoire (km > 5000) | Haute |\n";
            if ($km > 10000) $rec .= "| Roulements et paliers | Inspection approfondie (km > 10000) | Haute |\n";
            if ($km > 15000) $rec .= "| Pièces d'usure moteur | Remplacement préventif (km > 15000) | Urgente |\n";
            $rec .= "| Bougies/injecteurs | Nettoyage ou remplacement si perte puissance | Moyenne |\n";
            $rec .= "| Batterie | Test et recharge si démarrage difficile | Basse |\n";
            return $rec;
        }

        if (str_contains($promptLc, 'sécurité') || str_contains($promptLc, 'précaution')) {
            return "⚠️ RECOMMANDATIONS DE SÉCURITÉ pour l'intervention sur {$nom} :\n\n"
                . "Équipements obligatoires :\n"
                . "• Casque, gants résistants, lunettes de protection\n"
                . "• Chaussures de sécurité avec semelle anti-perforation\n"
                . "• Gilet haute visibilité si intervention en extérieur\n\n"
                . "Procédures avant intervention :\n"
                . "• Couper le moteur et attendre le refroidissement complet\n"
                . "• Déconnecter la batterie (pôle négatif en premier)\n"
                . "• Caler les roues pour éviter tout mouvement\n"
                . "• Utiliser des chandelles certifiées\n\n"
                . "Pendant l'intervention :\n"
                . "• Extincteur à portée de main — zone bien ventilée\n"
                . "• Ne jamais travailler seul sur une intervention lourde\n\n"
                . "Après intervention :\n"
                . "• Rebrancher la batterie (pôle positif en premier)\n"
                . "• Tester la machine à vide avant utilisation normale";
        }

        if (str_contains($promptLc, 'temps') || str_contains($promptLc, 'outil') || str_contains($promptLc, 'durée')) {
            $duree = match($type) {
                'Moteur'            => '4 à 8 heures',
                'Transmission'      => '3 à 6 heures',
                'Hydraulique'       => '2 à 4 heures',
                'Électricité'       => '1 à 3 heures',
                'Vidange & filtres' => '1 à 2 heures',
                default             => '2 à 4 heures',
            };
            return "⏱️ RECOMMANDATIONS - Temps et outils pour panne {$type} :\n\n"
                . "Durée estimée : {$duree}\n\n"
                . "Outils nécessaires :\n"
                . "• Clés à douilles (jeu complet 8-24mm)\n"
                . "• Multimètre digital\n"
                . "• Valise de diagnostic OBD\n"
                . "• Manomètre de compression\n"
                . "• Thermomètre infrarouge\n"
                . "• Bac de récupération\n\n"
                . "Technicien recommandé : Spécialiste en maintenance agricole";
        }

        if (str_contains($promptLc, 'coût') || str_contains($promptLc, 'estimation') || str_contains($promptLc, 'prix')) {
            $coutPieces = match($type) {
                'Moteur'            => '300 – 800',
                'Transmission'      => '250 – 600',
                'Hydraulique'       => '150 – 400',
                'Électricité'       => '80 – 250',
                'Vidange & filtres' => '60 – 150',
                default             => '100 – 400',
            };
            return "💰 RECOMMANDATIONS - Estimation des coûts pour {$nom} :\n\n"
                . "| Poste | Coût estimé (DT) |\n|-------|------------------|\n"
                . "| Pièces détachées | {$coutPieces} |\n"
                . "| Main d'œuvre (taux horaire) | 50 – 120 |\n"
                . "| Forfait déplacement technicien | 30 – 80 |\n"
                . ($km > 10000 ? "| Révision générale supplémentaire | +200 à 500 |\n" : '')
                . "| Total estimé | À partir de " . explode(' – ', $coutPieces)[0] . " DT |\n\n"
                . "💡 Recommandations : Demander 2-3 devis, vérifier la garantie des pièces";
        }

        return "📋 RECOMMANDATION GÉNÉRALE pour {$nom} (panne {$type}, priorité {$prio}) :\n\n"
            . "Plan d'action recommandé :\n"
            . "1. 🔍 Réaliser un diagnostic complet avant toute intervention\n"
            . "2. 🎯 Identifier précisément la source du problème\n"
            . "3. 📦 Préparer les pièces et outils nécessaires\n"
            . "4. 🔧 Intervenir selon les procédures constructeur\n"
            . "5. ✅ Tester le bon fonctionnement après intervention\n"
            . "6. 📝 Documenter l'intervention dans le carnet de maintenance\n\n"
            . ($prio === 'urgente'
                ? "⚠️ ACTION IMMÉDIATE : Priorité urgente — intervenir sans délai."
                : "📅 PLANIFICATION : Planifier l'intervention dans les meilleurs délais.");
    }

    /**
     * @param list<array{nom:string,usure:int|float}> $composants
     * @return list<string>
     */
    private function buildLifetimeRecommendations(string $type, string $prio, int $km, array $composants): array
    {
        $recs = [];
        if ($prio === 'urgente') $recs[] = "Intervention immédiate requise — risque d'aggravation si non traité.";
        elseif ($prio === 'haute') $recs[] = "Planifier l'intervention sous 2 semaines maximum.";
        if ($km >= 5000)  $recs[] = "Vidange moteur et remplacement des filtres requis (kilométrage > 5 000 km).";
        if ($km >= 10000) $recs[] = "Révision générale conseillée — contrôle complet de tous les systèmes.";
        if ($km >= 15000) $recs[] = "Inspection approfondie de la transmission et du moteur (kilométrage critique).";

        usort(
            $composants,
            static fn (array $a, array $b): int => $b['usure'] <=> $a['usure']
        );
        if ($composants !== [] && $composants[0]['usure'] > 70) {
            $recs[] = "Composant critique : {$composants[0]['nom']} ({$composants[0]['usure']}% d'usure) — prévoir remplacement.";
        }
        $recs[] = "Effectuer des inspections visuelles quotidiennes et noter toute anomalie.";
        $recs[] = "Tenir à jour le carnet de maintenance pour assurer la traçabilité.";
        return array_slice($recs, 0, 5);
    }

    /**
     * @param array{kilometrage?:int,type?:string,priorite?:string} $d
     * @return list<array{name:string,reason?:string,priority:string}>
     */
    private function calculateRecommendedInterventions(array $d): array
    {
        $interventions = [];
        $km            = $d['kilometrage'] ?? 0;
        $typePanne     = $d['type'] ?? '';
        $priorite      = $d['priorite'] ?? 'moyenne';

        if ($km >= 5000)  $interventions[] = ['name' => '🛢️ Vidange moteur',           'reason' => 'Kilométrage > 5 000 km',  'priority' => $km >= 8000 ? 'haute' : 'moyenne'];
        if ($km >= 10000) $interventions[] = ['name' => '🔧 Révision générale',         'reason' => 'Kilométrage > 10 000 km', 'priority' => 'haute'];
        if ($km >= 15000) $interventions[] = ['name' => '⚙️ Remplacement transmission', 'reason' => 'Kilométrage > 15 000 km', 'priority' => 'urgente'];

        $typeInterventions = [
            'Mécanique'         => [['name' => '🔧 Contrôle des pièces mécaniques', 'priority' => 'haute'],   ['name' => '📊 Test de performance moteur',  'priority' => 'moyenne']],
            'Électricité'       => [['name' => '⚡ Diagnostic circuit électrique',  'priority' => 'haute'],   ['name' => '🔋 Test batterie et alternateur', 'priority' => 'moyenne']],
            'Hydraulique'       => [['name' => '💧 Contrôle circuit hydraulique',   'priority' => 'haute'],   ['name' => '🔍 Détection des fuites',         'priority' => 'haute']],
            'Moteur'            => [['name' => '🔧 Diagnostic complet moteur',      'priority' => 'urgente'], ['name' => '🌡️ Contrôle du refroidissement',  'priority' => 'haute']],
            'Vidange & filtres' => [['name' => '🛢️ Vidange et changement filtres',  'priority' => 'moyenne'], ['name' => '🌬️ Nettoyage filtre à air',       'priority' => 'basse']],
        ];
        if (isset($typeInterventions[$typePanne])) {
            foreach ($typeInterventions[$typePanne] as $i) $interventions[] = $i;
        }
        if (empty($interventions)) {
            $interventions[] = ['name' => '🔍 Diagnostic général', 'reason' => 'Inspection préventive', 'priority' => 'moyenne'];
        }
        if ($priorite === 'urgente') {
            $interventions[] = ['name' => "🚨 Intervention d'urgence", 'reason' => 'Panne prioritaire détectée', 'priority' => 'urgente'];
        }
        return $interventions;
    }

    /**
     * @param array{nom:string,type:string,priorite:string,kilometrage?:int,dateMain?:string|null,cout:float|int} $d
     * @param list<string> $options
     * @param list<array{name:string,reason?:string,priority:string}> $interventions
     */
    private function generateSchedulePlan(array $d, array $options, array $interventions): string
    {
        $plan  = "# 📋 PLAN DE MAINTENANCE\n\n## Récapitulatif\n";
        $plan .= "- Machine : {$d['nom']}\n";
        $plan .= "- Type : {$d['type']}\n";
        $plan .= "- Priorité : " . ucfirst($d['priorite']) . "\n";
        $km = $d['kilometrage'] ?? 0;
        $plan .= "- Kilométrage : " . ($km > 0 ? $km . ' km' : 'Non renseigné') . "\n";
        $plan .= "- Date prévue : " . ($d['dateMain'] ?? 'Non renseignée') . "\n";
        $plan .= "- Coût estimé : " . number_format($d['cout'], 2, ',', ' ') . " DT\n\n";

        if (in_array('intervention', $options)) {
            $plan .= "## 🔍 DIAGNOSTIC PRÉLIMINAIRE\n1. Inspection visuelle générale\n2. Test à vide\n3. Relevé des paramètres\n4. Identification des anomalies\n\n## 📝 ÉTAPES D'INTERVENTION\n";
            foreach ($interventions as $idx => $i) {
                $plan .= ($idx + 1) . ". {$i['name']}\n";
                if (isset($i['reason'])) $plan .= "   - Motif : {$i['reason']}\n";
                $plan .= "   - Priorité : " . ucfirst($i['priority']) . "\n";
            }
            $plan .= "\n";
        }

        if (in_array('prevention', $options)) {
            $days = match($d['priorite']) { 'urgente' => 30, 'haute' => 60, 'moyenne' => 90, default => 180 };
            $plan .= "## 📅 PLANNING PRÉVENTIF\n- Prochaine maintenance : dans {$days} jours\n";
            if ($km > 0) {
                $plan .= "- Prochaine vidange : tous les 5 000 km\n- Révision générale : tous les 10 000 km\n";
            }
            $plan .= "- Contrôle hebdomadaire : niveaux, pression pneus, éclairage\n- Contrôle mensuel : courroies, filtres, batterie\n\n";
        }

        if (in_array('pieces', $options)) {
            $plan .= "## 🔩 PIÈCES DÉTACHÉES\n| Pièce | Qté | Disponibilité | Coût (DT) |\n|-------|-----|---------------|----------|\n";
            $plan .= "| Filtre à huile | 1 | En stock | 45 |\n| Filtre à air | 1 | En stock | 35 |\n";
            $plan .= "| Filtre gasoil | 1 | En stock | 55 |\n| Huile moteur 5L | 1 | En stock | 85 |\n";
            $plan .= "| Courroie distribution | 1 | Sur commande (3-5j) | 120 |\n| Bougies | 4 | En stock | 25/pièce |\n\n";
        }

        if (in_array('optimisation', $options)) {
            $plan .= "## ⚡ OPTIMISATION\n- Regrouper la vidange avec le changement des filtres\n- Planifier la révision avec le contrôle de la transmission\n- Commander les pièces à l'avance\n\n";
        }

        if (in_array('securite', $options)) {
            $plan .= "## ⚠️ PRÉCAUTIONS DE SÉCURITÉ\n- EPI obligatoires (gants, lunettes, chaussures)\n- Couper le moteur et attendre le refroidissement\n- Chandelles obligatoires pour travail sous la machine\n- Extincteur à proximité — zone ventilée\n\n";
        }

        if (in_array('controle', $options)) {
            $plan .= "## ✅ CONTRÔLE POST-INTERVENTION\n- [ ] Absence de fuites\n- [ ] Niveaux des fluides\n- [ ] Fonctionnement à chaud\n- [ ] Absence de voyants\n- [ ] Freins et direction\n- [ ] Serrage des éléments\n- [ ] Documentation dans le carnet\n\n";
        }

        $score  = 100;
        $score -= match($d['priorite']) { 'urgente' => 30, 'haute' => 20, 'moyenne' => 10, default => 0 };
        $score -= match(true) { $km > 15000 => 25, $km > 10000 => 15, default => 0 };

        $plan .= "## 📊 SCORE DE SANTÉ : " . max(0, $score) . "/100\n\n";
        $plan .= "## 💡 RECOMMANDATIONS\n1. Suivre rigoureusement le planning d'entretien\n"
               . "2. Former les opérateurs aux bonnes pratiques\n"
               . "3. Tenir un carnet de bord à jour\n"
               . "4. Maintenir un stock minimum de pièces consommables\n"
               . "5. Inspections visuelles quotidiennes\n";

        return $plan;
    }

    // ────────────────────────────────────────────────────────
    // HELPERS POUR LE CALENDRIER DES RAPPELS
    // ────────────────────────────────────────────────────────
    /** @return list<string> */
    private function generateReminderRecommendations(Maintenance $m, int $yearsSince): array
    {
        $recommendations = [];
        $km = $m->getKilometrage() ?? 0;
        $priorite = $m->getPriorite();

        if ($priorite === 'urgente') {
            $recommendations[] = "🚨 INTERVENTION IMMÉDIATE - Ne pas utiliser la machine";
            $recommendations[] = "📞 Contacter le service technique d'urgence";
            $recommendations[] = "🔧 Préparer les pièces de rechange nécessaires";
        } elseif ($yearsSince >= 2) {
            $recommendations[] = "⚠️ REVISION MAJEURE - Plus de 2 ans sans maintenance";
            $recommendations[] = "🔧 Contrôle complet du moteur et de la transmission";
            $recommendations[] = "🛢️ Vidange complète et changement de tous les filtres";
            $recommendations[] = "🔍 Inspection approfondie du système hydraulique";
        } elseif ($yearsSince >= 1) {
            $recommendations[] = "📅 RAPPEL ANNUEL - Maintenance préventive recommandée";
            $recommendations[] = "🔧 Vérification générale de l'état de la machine";
            $recommendations[] = "🛢️ Vidange moteur et changement des filtres";
            $recommendations[] = "🔍 Contrôle des courroies et des niveaux";
        }

        if ($km > 10000) {
            $recommendations[] = "📊 Kilométrage élevé ({$km} km) - Révision complète nécessaire";
        } elseif ($km > 5000) {
            $recommendations[] = "📊 Kilométrage intermédiaire ({$km} km) - Vérification recommandée";
        }

        return $recommendations;
    }

    /**
     * @return array{score:int,level:string,color:string,label:string}
     */
    private function calculateReminderUrgence(Maintenance $m, int $yearsSince): array
    {
        $priorite = $m->getPriorite();
        $km = $m->getKilometrage() ?? 0;
        
        $score = 0;
        $level = 'faible';
        $color = '#2d6a2d';
        
        if ($priorite === 'urgente') {
            $score = 100;
            $level = 'critique';
            $color = '#a32d2d';
        } elseif ($yearsSince >= 2) {
            $score = 90;
            $level = 'critique';
            $color = '#a32d2d';
        } elseif ($yearsSince >= 1) {
            $score = 70;
            $level = 'élevé';
            $color = '#e67e22';
        } elseif ($km > 15000) {
            $score = 80;
            $level = 'élevé';
            $color = '#e67e22';
        } elseif ($km > 10000) {
            $score = 50;
            $level = 'moyen';
            $color = '#f39c12';
        }
        
        return [
            'score' => $score,
            'level' => $level,
            'color' => $color,
            'label' => $this->getReminderUrgenceLabel($level),
        ];
    }

    private function getReminderUrgenceLabel(string $level): string
    {
        return match($level) {
            'critique' => '🔴 CRITIQUE - Action immédiate',
            'élevé' => '🟠 ÉLEVÉ - Planifier sous 48h',
            'moyen' => '🟡 MOYEN - Planifier sous 15 jours',
            default => '🟢 FAIBLE - Surveillance normale',
        };
    }

    private function getReminderMessage(string $machineName, int $yearsSince, int $monthsSince, string $priorite): string
    {
        if ($priorite === 'urgente') {
            return "🔴 URGENT : {$machineName} nécessite une intervention immédiate !";
        }
        
        if ($yearsSince >= 2) {
            return "⚠️ ALERTE MAJEURE : {$machineName} n'a pas eu de maintenance depuis {$yearsSince} ans et {$monthsSince} mois. Révision complète obligatoire !";
        }
        
        if ($yearsSince >= 1) {
            if ($monthsSince > 0) {
                return "🔔 RAPPEL ANNUEL : {$machineName} n'a pas eu de maintenance depuis {$yearsSince} an et {$monthsSince} mois. Une visite de maintenance est recommandée.";
            }
            return "🔔 RAPPEL ANNUEL : {$machineName} n'a pas eu de maintenance depuis {$yearsSince} an. Une visite de maintenance est recommandée.";
        }
        
        return "📅 {$machineName} a un rappel de visite ou maintenance programmée.";
    }

    // ════════════════════════════════════════════════════════
    // HELPERS POUR LA RECOMMANDATION IA INTELLIGENTE
    // ════════════════════════════════════════════════════════

    private function calculateRiskScore(int $km, string $priorite, string $statut): int
    {
        $score = 0;
        
        if ($km >= 15000) $score += 40;
        elseif ($km >= 10000) $score += 30;
        elseif ($km >= 5000) $score += 20;
        elseif ($km >= 2000) $score += 10;
        
        $score += match($priorite) {
            'urgente' => 35,
            'haute' => 25,
            'moyenne' => 15,
            default => 5,
        };
        
        $score += match($statut) {
            'en_cours' => 20,
            'planifie' => 10,
            'termine' => 0,
            default => 5,
        };
        
        return min(100, $score);
    }
    
    private function getRiskLevel(int $score): string
    {
        if ($score >= 70) return 'critique';
        if ($score >= 50) return 'élevé';
        if ($score >= 30) return 'modéré';
        return 'faible';
    }
    
    private function calculateNextKmRecommendation(int $km): int
    {
        if ($km >= 15000) return 20000;
        if ($km >= 10000) return 15000;
        if ($km >= 5000) return 10000;
        if ($km >= 2000) return 5000;
        return 5000;
    }
    
    private function getIntelligentMainMessage(string $machineName, string $priorite, string $statut, int $km): string
    {
        if ($priorite === 'urgente' && $statut === 'en_cours') {
            return "⚠️ INTERVENTION EN COURS - {$machineName} est actuellement en réparation urgente. 🔥";
        }
        
        if ($priorite === 'urgente') {
            return "🚨 ALERTE CRITIQUE - {$machineName} nécessite une intervention immédiate ! 🔥";
        }
        
        if ($statut === 'en_cours') {
            return "🔧 INTERVENTION EN COURS - Une maintenance est en cours sur {$machineName}.";
        }
        
        if ($priorite === 'haute') {
            return "⚠️ HAUTE PRIORITÉ - {$machineName} doit être maintenue dans les plus brefs délais.";
        }
        
        if ($km >= 15000) {
            return "📊 KILOMÉTRAGE CRITIQUE - {$machineName} a atteint {$km} km. Révision majeure requise !";
        }
        
        if ($km >= 10000) {
            return "📊 KILOMÉTRAGE ÉLEVÉ - {$machineName} approche les {$km} km. Maintenance préventive recommandée.";
        }
        
        return "🔔 RECOMMANDATION IA - Sur la base des données actuelles et des cas similaires, il est fortement recommandé d'adopter un cycle de maintenance préventive tous les 5000 km afin de réduire les risques futurs et optimiser la durée de vie de {$machineName}.";
    }
    
    /** @return list<string> */
    private function getIntelligentInterpretation(int $km, string $priorite, string $statut, string $riskLevel): array
    {
        $interpretation = [];
        
        if ($riskLevel === 'critique') {
            $interpretation[] = "⚠️ Niveau de risque : CRITIQUE";
            $interpretation[] = "Le système détecte un risque de panne majeur basé sur :";
        } elseif ($riskLevel === 'élevé') {
            $interpretation[] = "⚠️ Niveau de risque : ÉLEVÉ";
            $interpretation[] = "Le système détecte un niveau de risque modéré à élevé basé sur :";
        } elseif ($riskLevel === 'modéré') {
            $interpretation[] = "📊 Niveau de risque : MODÉRÉ";
            $interpretation[] = "Le système détecte un niveau de risque basé sur :";
        } else {
            $interpretation[] = "✅ Niveau de risque : FAIBLE";
            $interpretation[] = "La machine semble en bonne état, surveillance recommandée basée sur :";
        }
        
        $factors = [];
        if ($km > 0) $factors[] = "le kilométrage actuel ({$km} km)";
        if ($priorite !== 'faible') $factors[] = "la priorité ({$priorite})";
        if ($statut !== 'termine') $factors[] = "le statut actuel ({$statut})";
        
        if (!empty($factors)) {
            $interpretation[] = "• " . implode("\n• ", $factors);
        }
        
        if ($riskLevel === 'critique' || $riskLevel === 'élevé') {
            $interpretation[] = "\n💡 Une maintenance doit être planifiée immédiatement pour éviter une panne critique.";
        } elseif ($riskLevel === 'modéré') {
            $interpretation[] = "\n💡 Une maintenance est planifiée à l'avance pour éviter une panne critique.";
        } else {
            $interpretation[] = "\n💡 Continuer le suivi régulier et les inspections périodiques.";
        }
        
        return $interpretation;
    }
    
    /** @return list<string> */
    private function getIntelligentActions(string $priorite, string $statut, int $km, int $nextKm): array
    {
        $actions = [];
        
        if ($priorite === 'urgente' && $statut === 'en_cours') {
            $actions[] = "🔧 Finaliser l'intervention en cours et tester la machine";
            $actions[] = "📝 Documenter les réparations effectuées";
            $actions[] = "🔄 Planifier la prochaine maintenance préventive";
        } elseif ($priorite === 'urgente') {
            $actions[] = "🚨 DÉPLACER LE TECHNICIEN D'URGENCE - Intervention immédiate requise";
            $actions[] = "📞 Contacter le service technique sans délai";
            $actions[] = "⛔ Ne pas utiliser la machine avant intervention";
        } elseif ($priorite === 'haute') {
            $actions[] = "📅 Planifier la maintenance sous 48 heures maximum";
            $actions[] = "🔧 Préparer les pièces de rechange nécessaires";
            $actions[] = "📋 Réaliser un diagnostic complet avant intervention";
        } elseif ($statut === 'en_cours') {
            $actions[] = "🔧 Poursuivre l'intervention en cours";
            $actions[] = "✅ Effectuer les tests de validation";
            $actions[] = "🔄 Mettre à jour le statut après intervention";
        } else {
            $actions[] = "📅 Programmer la prochaine maintenance avant d'atteindre {$nextKm} km";
            if ($km >= 5000) {
                $actions[] = "🛢️ Prévoir une vidange et changement des filtres";
                $actions[] = "🔍 Contrôle des courroies et niveaux";
            }
            $actions[] = "🔧 Effectuer une vérification complète des composants principaux";
            $actions[] = "🔄 Mettre à jour régulièrement le statut après intervention";
        }
        
        return $actions;
    }
    
    /** @return list<string> */
    private function getAdditionalTips(string $typePanne, int $km, string $priorite): array
    {
        $tips = [];
        
        if ($typePanne === 'Moteur' || $km > 10000) {
            $tips[] = "Contrôler la compression moteur et l'injection";
        }
        if ($typePanne === 'Hydraulique' || $km > 8000) {
            $tips[] = "Vérifier l'absence de fuites sur le circuit hydraulique";
        }
        if ($typePanne === 'Électricité') {
            $tips[] = "Tester la batterie et l'alternateur";
        }
        if ($priorite === 'haute' || $priorite === 'urgente') {
            $tips[] = "Prévoir un technicien spécialisé pour l'intervention";
        }
        
        if (empty($tips)) {
            $tips[] = "Effectuer une inspection visuelle hebdomadaire";
            $tips[] = "Tenir à jour le carnet de maintenance";
        }
        
        return $tips;
    }
}