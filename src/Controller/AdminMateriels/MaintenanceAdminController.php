<?php
// src/Controller/AdminMateriels/MaintenanceAdminController.php

namespace App\Controller\AdminMateriels;

use App\Entity\Materiels\Maintenance;
use App\Entity\Materiels\Machine;
use App\Repository\Materiels\MaintenanceRepository;
use App\Repository\Materiels\MachineRepository;
use App\Service\GeminiRecommendationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;

#[Route('/admin/materiels/maintenances', name: 'admin_maintenances_')]
class MaintenanceAdminController extends AbstractController
{
    // ─────────────────────────────────────────────────────────────────────────
    // INDEX - Liste des maintenances AVEC PAGINATION
    // ─────────────────────────────────────────────────────────────────────────
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(
        Request $request,
        MaintenanceRepository $repo,
        MachineRepository $machineRepo,
        PaginatorInterface $paginator
    ): Response {
        $search     = $request->query->get('search', '');
        $type       = $request->query->get('type', '');
        $sort       = $request->query->get('sort', 'dateMain');
        $dir        = $request->query->get('dir', 'DESC');
        $coutFilter = $request->query->get('coutFilter', '');
        $idM        = $request->query->get('idM', '');
        $statut     = $request->query->get('statut', '');
        $priorite   = $request->query->get('priorite', '');

        if ($coutFilter === 'asc') {
            $sort = 'cout'; $dir = 'ASC';
        } elseif ($coutFilter === 'desc') {
            $sort = 'cout'; $dir = 'DESC';
        }

        $maintenances = $repo->searchWithMaterielName($search, $type, $sort, $dir, $idM, $statut, $priorite);
        $types        = array_column($repo->countByTypePanne(), 'type');
        $machines     = $machineRepo->findAll();

        $page = max(1, $request->query->getInt('page', 1));
        $limit = $request->query->getInt('limit', 10);
        $allowedLimits = [10, 25, 50, 100];
        if (!in_array($limit, $allowedLimits)) {
            $limit = 10;
        }

        $pagination = $paginator->paginate($maintenances, $page, $limit);
        $paginatedMaintenances = $pagination->getItems();

        $pageTotalCout = 0;
        foreach ($paginatedMaintenances as $m) {
            $pageTotalCout += (float) $m->getCout();
        }

        return $this->render('admins/maintenances/index.html.twig', [
            'pagination'       => $pagination,
            'maintenances'     => $paginatedMaintenances,
            'types'            => $types,
            'totalCout'        => $pageTotalCout,
            'search'           => $search,
            'selectedType'     => $type,
            'selectedIdM'      => $idM,
            'selectedStatut'   => $statut,
            'selectedPriorite' => $priorite,
            'sort'             => $sort,
            'dir'              => $dir,
            'coutFilter'       => $coutFilter,
            'machines'         => $machines,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STATISTIQUES
    // ─────────────────────────────────────────────────────────────────────────
    #[Route('/statistiques/bar', name: 'stats_bar', methods: ['GET'])]
    public function statsBar(MaintenanceRepository $repo): Response
    {
        $byType    = $repo->countByTypePanne();
        $coutMonth = $repo->getCoutByMonth();
        $totalCout = $repo->getTotalCout();
        $total     = array_sum(array_column($byType, 'total'));

        return $this->render('admins/maintenances/stats_bar.html.twig', [
            'byType'    => $byType,
            'coutMonth' => $coutMonth,
            'totalCout' => $totalCout,
            'total'     => $total,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HISTORIQUE - Timeline des maintenances AVEC IA GEMINI
    // ─────────────────────────────────────────────────────────────────────────
    #[Route('/historique', name: 'history', methods: ['GET'])]
    public function history(
        MaintenanceRepository $repo,
        MachineRepository $machineRepo,
        Request $request,
        GeminiRecommendationService $geminiService,
        LoggerInterface $logger
    ): Response {
        $idM      = $request->query->get('idM', '');
        $statut   = $request->query->get('statut', '');
        $priorite = $request->query->get('priorite', '');
        $year     = $request->query->get('year', '');

        $criteria = [];
        if ($idM !== '') {
            $criteria['idM'] = (int) $idM;
        }

        $maintenances = $repo->findBy($criteria, ['dateMain' => 'DESC']);

        if ($statut !== '') {
            $maintenances = array_filter($maintenances, fn(Maintenance $m) => $m->getStatut() === $statut);
        }

        if ($priorite !== '') {
            $maintenances = array_filter($maintenances, fn(Maintenance $m) => $m->getPriorite() === $priorite);
        }

        if ($year !== '') {
            $maintenances = array_filter($maintenances, fn(Maintenance $m) => $m->getDateMain()?->format('Y') === $year);
        }

        $maintenances = array_values($maintenances);

        foreach ($maintenances as $maintenance) {
            try {
                $existingReco = $maintenance->getRecommandation();
                if (empty($existingReco) || strlen($existingReco) < 10) {
                    $recommendation = $geminiService->generateRecommendation($maintenance);
                    $maintenance->setRecommandation($recommendation);
                    $logger->info('IA Gemini recommendation generated', [
                        'maintenance_id' => $maintenance->getIdMain(),
                        'type'           => $maintenance->getTypePanne(),
                    ]);
                }
            } catch (\Exception $e) {
                $logger->error('Failed to generate Gemini recommendation', [
                    'maintenance_id' => $maintenance->getIdMain(),
                    'error'          => $e->getMessage(),
                ]);
                $maintenance->setRecommandation($this->getFallbackRecommendation($maintenance));
            }
        }

        // Sauvegarder les modifications si nécessaire
        if (!empty($maintenances)) {
            $repo->saveAll($maintenances);
        }

        $grouped = [];
        foreach ($maintenances as $m) {
            $date = $m->getDateMain();
            if (!$date) continue;
            $grouped[$date->format('Y-m')][] = $m;
        }
        krsort($grouped);

        $totalCout     = array_sum(array_map(fn(Maintenance $m) => (float) $m->getCout(), $maintenances));
        $years         = array_unique(array_filter(array_map(fn(Maintenance $m) => $m->getDateMain()?->format('Y'), $maintenances)));
        rsort($years);

        $machines      = $machineRepo->findAll();
        $urgentesCount = count(array_filter($maintenances, fn($m) => $m->getPriorite() === 'urgente'));

        return $this->render('admins/maintenances/history.html.twig', [
            'grouped'          => $grouped,
            'totalCout'        => $totalCout,
            'machines'         => $machines,
            'years'            => $years,
            'selectedIdM'      => $idM,
            'selectedStatut'   => $statut,
            'selectedPriorite' => $priorite,
            'selectedYear'     => $year,
            'totalCount'       => count($maintenances),
            'urgentesCount'    => $urgentesCount,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DASHBOARD IA - Vue globale
    // ─────────────────────────────────────────────────────────────────────────
    #[Route('/ai/dashboard', name: 'ai_dashboard', methods: ['GET'])]
    public function aiDashboard(
        MachineRepository $machineRepo,
        GeminiRecommendationService $geminiService
    ): Response {
        $machines    = $machineRepo->findAll();
        $healthScores = [];
        $allAlerts   = [];
        $predictions = [];

        foreach ($machines as $machine) {
            $healthScores[$machine->getId()] = $geminiService->calculateHealthScore($machine);
            $alerts = $geminiService->generateSmartAlerts($machine);
            foreach ($alerts as &$alert) {
                if (!isset($alert['type'])) {
                    $alert['type'] = 'info';
                }
            }
            $allAlerts   = array_merge($allAlerts, $alerts);
            $predictions[$machine->getId()] = $geminiService->predictFailure($machine);
        }

        $priorities      = $geminiService->prioritizeInterventions($machines);
        $schedule        = $geminiService->generateOptimizedSchedule($priorities, 30);
        $avgHealthScore  = !empty($healthScores) ? array_sum(array_column($healthScores, 'score')) / count($healthScores) : 0;
        $criticalAlerts  = count(array_filter($allAlerts, fn($a) => ($a['type'] ?? 'info') === 'critique'));
        $highRiskMachines = count(array_filter($predictions, fn($p) => ($p['risque_panne'] ?? 'faible') === 'élevé'));

        return $this->render('admins/maintenances/ai_dashboard.html.twig', [
            'machines'        => $machines,
            'healthScores'    => $healthScores,
            'alerts'          => $allAlerts,
            'predictions'     => $predictions,
            'priorities'      => $priorities,
            'schedule'        => $schedule,
            'avgHealthScore'  => round($avgHealthScore),
            'criticalAlerts'  => $criticalAlerts,
            'highRiskMachines'=> $highRiskMachines,
            'totalMachines'   => count($machines),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // API: Prédiction pour une machine
    // ─────────────────────────────────────────────────────────────────────────
    #[Route('/ai/predict/{id}', name: 'ai_predict', methods: ['GET'])]
    public function apiPredictMachine(
        int $id,
        MachineRepository $machineRepo,
        GeminiRecommendationService $geminiService
    ): JsonResponse {
        $machine = $machineRepo->find($id);
        if (!$machine) {
            return $this->json(['error' => 'Machine non trouvée'], 404);
        }

        return $this->json([
            'machine'     => ['id' => $machine->getId(), 'nom' => $machine->getNom(), 'marque' => $machine->getMarque()],
            'health_score'=> $geminiService->calculateHealthScore($machine),
            'prediction'  => $geminiService->predictFailure($machine),
            'alerts'      => $geminiService->generateSmartAlerts($machine),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // API: Planning optimisé
    // ─────────────────────────────────────────────────────────────────────────
    #[Route('/ai/schedule', name: 'ai_schedule', methods: ['GET'])]
    public function apiOptimizedSchedule(
        MachineRepository $machineRepo,
        GeminiRecommendationService $geminiService
    ): JsonResponse {
        $machines   = $machineRepo->findAll();
        $priorities = $geminiService->prioritizeInterventions($machines);
        $schedule   = $geminiService->generateOptimizedSchedule($priorities, 30);

        return $this->json([
            'total_machines'           => count($machines),
            'interventions_planifiees' => count($schedule),
            'priorities'               => $priorities,
            'planning'                 => $schedule,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // API: Alertes globales
    // ─────────────────────────────────────────────────────────────────────────
    #[Route('/ai/alerts', name: 'ai_alerts', methods: ['GET'])]
    public function apiGlobalAlerts(
        MachineRepository $machineRepo,
        GeminiRecommendationService $geminiService
    ): JsonResponse {
        $machines  = $machineRepo->findAll();
        $allAlerts = [];

        foreach ($machines as $machine) {
            $allAlerts = array_merge($allAlerts, $geminiService->generateSmartAlerts($machine));
        }

        return $this->json([
            'total_alerts'   => count($allAlerts),
            'critical_alerts'=> count(array_filter($allAlerts, fn($a) => ($a['type'] ?? 'info') === 'critique')),
            'warning_alerts' => count(array_filter($allAlerts, fn($a) => ($a['type'] ?? 'info') === 'warning')),
            'alerts'         => $allAlerts,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // API: Analyse IA complète pour une maintenance
    // ─────────────────────────────────────────────────────────────────────────
    #[Route('/ai/analyze/{id}', name: 'ai_analyze', methods: ['GET'])]
    public function apiAnalyze(
        int $id,
        MaintenanceRepository $maintenanceRepo,
        GeminiRecommendationService $geminiService,
        LoggerInterface $logger
    ): JsonResponse {
        $maintenance = $maintenanceRepo->find($id);

        if (!$maintenance) {
            return $this->json(['error' => 'Maintenance non trouvée'], 404);
        }

        try {
            $recommendation = $geminiService->generateRecommendation($maintenance);

            return $this->json([
                'recommendation'   => $recommendation,
                'type_panne'       => $maintenance->getTypePanne(),
                'priorite'         => $maintenance->getPriorite(),
                'date_maintenance' => $maintenance->getDateMain()?->format('Y-m-d'),
                'cout'             => $maintenance->getCout(),
            ]);
        } catch (\Exception $e) {
            $logger->error('Failed to generate AI analysis', [
                'maintenance_id' => $maintenance->getIdMain(),
                'error'          => $e->getMessage(),
            ]);

            return $this->json([
                'error'                  => 'Erreur lors de l\'analyse IA',
                'fallback_recommendation'=> $this->getFallbackRecommendation($maintenance),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EXPORT PDF
    // ─────────────────────────────────────────────────────────────────────────
    #[Route('/export/pdf', name: 'export_pdf', methods: ['GET'])]
    public function exportPdf(MaintenanceRepository $repo): Response
    {
        $maintenances = $repo->findAllOrderedByDate();
        $totalCout    = $repo->getTotalCout();
        $byType       = $repo->countByTypePanne();

        $html = $this->renderView('admins/maintenances/pdf.html.twig', [
            'maintenances' => $maintenances,
            'totalCout'    => $totalCout,
            'byType'       => $byType,
            'date'         => new \DateTime(),
        ]);

        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // NEW - Créer une nouvelle maintenance (CORRIGÉ - KILOMÉTRAGE OBLIGATOIRE)
    // ─────────────────────────────────────────────────────────────────────────
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        MachineRepository $machineRepo,
        GeminiRecommendationService $geminiService,
        LoggerInterface $logger
    ): Response {
        $maintenance = new Maintenance();
        $machines    = $machineRepo->findAll();
        $errors      = [];

        // Valeurs par défaut
        $maintenance->setStatut('planifie');
        $maintenance->setPriorite('moyenne');
        $maintenance->setRecommandation('Aucune recommandation spécifique pour le moment.');
        $maintenance->setKilometrage(0); // IMPORTANT: Valeur par défaut pour éviter NULL

        if ($request->isMethod('POST')) {
            // Type de panne
            $typePanne = trim($request->request->get('typePanne', ''));
            if (empty($typePanne)) {
                $errors['typePanne'] = 'Le type de panne est obligatoire.';
            } else {
                $maintenance->setTypePanne($typePanne);
            }

            // Coût
            $cout = $request->request->get('cout');
            if ($cout === null || $cout === '') {
                $errors['cout'] = 'Le coût est obligatoire.';
            } else {
                $coutFloat = (float) $cout;
                if ($coutFloat <= 0) {
                    $errors['cout'] = 'Le coût doit être supérieur à 0.';
                } elseif ($coutFloat > 999999.99) {
                    $errors['cout'] = 'Le coût ne peut pas dépasser 999 999,99 DT.';
                } else {
                    $maintenance->setCout($coutFloat);
                }
            }

            // Date
            $dateStr = $request->request->get('dateMain');
            if (empty($dateStr)) {
                $errors['dateMain'] = 'La date de maintenance est obligatoire.';
            } else {
                try {
                    $date = new \DateTime($dateStr);
                    $today = new \DateTime();
                    if ($date > $today) {
                        $errors['dateMain'] = 'La date ne peut pas être dans le futur.';
                    } else {
                        $maintenance->setDateMain($date);
                    }
                } catch (\Exception $e) {
                    $errors['dateMain'] = 'Format de date invalide.';
                }
            }

            // Description
            $description = trim($request->request->get('description', ''));
            if (empty($description)) {
                $errors['description'] = 'La description est obligatoire.';
            } elseif (strlen($description) < 5) {
                $errors['description'] = 'La description doit contenir au moins 5 caractères.';
            } elseif (strlen($description) > 1000) {
                $errors['description'] = 'La description ne peut pas dépasser 1000 caractères.';
            } else {
                $maintenance->setDescription($description);
            }

            // Statut
            $statut = $request->request->get('statut', 'planifie');
            if (!in_array($statut, ['planifie', 'en_cours', 'termine'])) {
                $errors['statut'] = 'Statut invalide.';
            } else {
                $maintenance->setStatut($statut);
            }

            // Priorité
            $priorite = $request->request->get('priorite', 'moyenne');
            if (!in_array($priorite, ['faible', 'moyenne', 'haute', 'urgente'])) {
                $errors['priorite'] = 'Priorité invalide.';
            } else {
                $maintenance->setPriorite($priorite);
            }

            // KILOMÉTRAGE - OBLIGATOIRE (correction)
            $kilometrage = $request->request->get('kilometrage');
            if ($kilometrage === null || $kilometrage === '') {
                $errors['kilometrage'] = 'Le kilométrage est obligatoire.';
            } else {
                $kmInt = (int) $kilometrage;
                if ($kmInt < 0) {
                    $errors['kilometrage'] = 'Le kilométrage doit être positif ou nul.';
                } elseif ($kmInt > 9999999) {
                    $errors['kilometrage'] = 'Le kilométrage ne peut pas dépasser 9 999 999 km.';
                } else {
                    $maintenance->setKilometrage($kmInt);
                }
            }

            // Recommandation (optionnelle)
            $recommandation = trim($request->request->get('recommandation', ''));
            if (!empty($recommandation)) {
                if (strlen($recommandation) > 2000) {
                    $errors['recommandation'] = 'La recommandation ne peut pas dépasser 2000 caractères.';
                } else {
                    $maintenance->setRecommandation($recommandation);
                }
            }

            // Matériel associé (optionnel)
            $idM = $request->request->get('idM');
            if (!empty($idM)) {
                $machine = $machineRepo->find((int) $idM);
                if ($machine) {
                    $maintenance->setIdM((int) $idM);
                    $maintenance->setNom($machine->getNom());
                } else {
                    $errors['idM'] = 'La machine sélectionnée n\'existe pas.';
                }
            }

            if (empty($errors)) {
                // Générer une recommandation IA si non fournie
                if (empty($recommandation)) {
                    try {
                        $aiRecommendation = $geminiService->generateRecommendation($maintenance);
                        $maintenance->setRecommandation($aiRecommendation);
                        $logger->info('AI recommendation generated for new maintenance', [
                            'maintenance_type' => $typePanne
                        ]);
                    } catch (\Exception $e) {
                        $logger->warning('Could not generate AI recommendation, using fallback', [
                            'error' => $e->getMessage()
                        ]);
                        $maintenance->setRecommandation($this->getFallbackRecommendation($maintenance));
                    }
                }

                $em->persist($maintenance);
                $em->flush();
                $this->addFlash('success', '✅ Maintenance ajoutée avec succès.');
                return $this->redirectToRoute('admin_maintenances_index');
            } else {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error);
                }
            }
        }

        return $this->render('admins/maintenances/new.html.twig', [
            'maintenance' => $maintenance,
            'machines'    => $machines,
            'errors'      => $errors,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SHOW - Afficher une maintenance
    // ─────────────────────────────────────────────────────────────────────────
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, MaintenanceRepository $repo): Response
    {
        $maintenance = $repo->findOneWithMaterielName($id);
        if (!$maintenance) {
            $this->addFlash('error', 'Maintenance introuvable.');
            return $this->redirectToRoute('admin_maintenances_index');
        }

        return $this->render('admins/maintenances/show.html.twig', [
            'maintenance' => $maintenance,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EDIT - Modifier une maintenance (CORRIGÉ - KILOMÉTRAGE OBLIGATOIRE)
    // ─────────────────────────────────────────────────────────────────────────
    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(
        int $id,
        Request $request,
        MaintenanceRepository $repo,
        MachineRepository $machineRepo,
        EntityManagerInterface $em,
        GeminiRecommendationService $geminiService,
        LoggerInterface $logger
    ): Response {
        $maintenance = $repo->find($id);
        if (!$maintenance) {
            $this->addFlash('error', 'Maintenance introuvable.');
            return $this->redirectToRoute('admin_maintenances_index');
        }

        // S'assurer que la recommandation n'est pas null
        if (empty($maintenance->getRecommandation())) {
            $maintenance->setRecommandation($this->getFallbackRecommendation($maintenance));
        }

        // S'assurer que le kilométrage n'est pas null
        if ($maintenance->getKilometrage() === null) {
            $maintenance->setKilometrage(0);
        }

        $machines = $machineRepo->findAll();
        $errors   = [];

        if ($request->isMethod('POST')) {
            // Type de panne
            $typePanne = trim($request->request->get('typePanne', ''));
            if (empty($typePanne)) {
                $errors['typePanne'] = 'Le type de panne est obligatoire.';
            } else {
                $maintenance->setTypePanne($typePanne);
            }

            // Coût
            $cout = $request->request->get('cout');
            if ($cout === null || $cout === '') {
                $errors['cout'] = 'Le coût est obligatoire.';
            } else {
                $coutFloat = (float) $cout;
                if ($coutFloat <= 0) {
                    $errors['cout'] = 'Le coût doit être supérieur à 0.';
                } elseif ($coutFloat > 999999.99) {
                    $errors['cout'] = 'Le coût ne peut pas dépasser 999 999,99 DT.';
                } else {
                    $maintenance->setCout($coutFloat);
                }
            }

            // Date
            $dateStr = $request->request->get('dateMain');
            if (empty($dateStr)) {
                $errors['dateMain'] = 'La date de maintenance est obligatoire.';
            } else {
                try {
                    $date = new \DateTime($dateStr);
                    $today = new \DateTime();
                    if ($date > $today) {
                        $errors['dateMain'] = 'La date ne peut pas être dans le futur.';
                    } else {
                        $maintenance->setDateMain($date);
                    }
                } catch (\Exception $e) {
                    $errors['dateMain'] = 'Format de date invalide.';
                }
            }

            // Description
            $description = trim($request->request->get('description', ''));
            if (!empty($description)) {
                if (strlen($description) < 5) {
                    $errors['description'] = 'La description doit contenir au moins 5 caractères.';
                } elseif (strlen($description) > 1000) {
                    $errors['description'] = 'La description ne peut pas dépasser 1000 caractères.';
                } else {
                    $maintenance->setDescription($description);
                }
            } else {
                $errors['description'] = 'La description est obligatoire.';
            }

            // Statut
            $statut = $request->request->get('statut', 'planifie');
            if (!in_array($statut, ['planifie', 'en_cours', 'termine'])) {
                $errors['statut'] = 'Statut invalide.';
            } else {
                $maintenance->setStatut($statut);
            }

            // Priorité
            $priorite = $request->request->get('priorite', 'moyenne');
            if (!in_array($priorite, ['faible', 'moyenne', 'haute', 'urgente'])) {
                $errors['priorite'] = 'Priorité invalide.';
            } else {
                $maintenance->setPriorite($priorite);
            }

            // KILOMÉTRAGE - OBLIGATOIRE (correction)
            $kilometrage = $request->request->get('kilometrage');
            if ($kilometrage === null || $kilometrage === '') {
                $errors['kilometrage'] = 'Le kilométrage est obligatoire.';
            } else {
                $kmInt = (int) $kilometrage;
                if ($kmInt < 0) {
                    $errors['kilometrage'] = 'Le kilométrage doit être positif ou nul.';
                } elseif ($kmInt > 9999999) {
                    $errors['kilometrage'] = 'Le kilométrage ne peut pas dépasser 9 999 999 km.';
                } else {
                    $maintenance->setKilometrage($kmInt);
                }
            }

            // Recommandation (optionnelle)
            $recommandation = trim($request->request->get('recommandation', ''));
            if (!empty($recommandation)) {
                if (strlen($recommandation) > 2000) {
                    $errors['recommandation'] = 'La recommandation ne peut pas dépasser 2000 caractères.';
                } else {
                    $maintenance->setRecommandation($recommandation);
                }
            } elseif (empty($recommandation) && empty($maintenance->getRecommandation())) {
                try {
                    $aiRecommendation = $geminiService->generateRecommendation($maintenance);
                    $maintenance->setRecommandation($aiRecommendation);
                } catch (\Exception $e) {
                    $maintenance->setRecommandation($this->getFallbackRecommendation($maintenance));
                }
            }

            // Matériel associé (optionnel)
            $idM = $request->request->get('idM');
            if (!empty($idM)) {
                $machine = $machineRepo->find((int) $idM);
                if ($machine) {
                    $maintenance->setIdM((int) $idM);
                    $maintenance->setNom($machine->getNom());
                } else {
                    $errors['idM'] = 'La machine sélectionnée n\'existe pas.';
                }
            } else {
                $maintenance->setIdM(null);
                $maintenance->setNom(null);
            }

            if (empty($errors)) {
                $em->flush();
                $this->addFlash('success', '✅ Maintenance mise à jour avec succès.');
                return $this->redirectToRoute('admin_maintenances_index');
            } else {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error);
                }
            }
        }

        return $this->render('admins/maintenances/edit.html.twig', [
            'maintenance' => $maintenance,
            'machines'    => $machines,
            'errors'      => $errors,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DELETE - Supprimer une maintenance
    // ─────────────────────────────────────────────────────────────────────────
    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(
        int $id,
        Request $request,
        MaintenanceRepository $repo,
        EntityManagerInterface $em
    ): Response {
        $maintenance = $repo->find($id);
        if (!$maintenance) {
            $this->addFlash('error', 'Maintenance introuvable.');
            return $this->redirectToRoute('admin_maintenances_index');
        }

        if ($this->isCsrfTokenValid('delete_maintenance_' . $id, $request->request->get('_token'))) {
            $em->remove($maintenance);
            $em->flush();
            $this->addFlash('success', '✅ Maintenance supprimée avec succès.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('admin_maintenances_index');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE METHODS
    // ─────────────────────────────────────────────────────────────────────────

    private function getFallbackRecommendation(Maintenance $m): string
    {
        $type     = strtolower($m->getTypePanne());
        $priorite = $m->getPriorite();

        $fallbacks = [
            'électrique' => '⚡ Contrôle préventif du circuit électrique recommandé',
            'mécanique'  => '🔧 Inspection mécanique générale recommandée',
            'hydraulique'=> '💧 Vérification du circuit hydraulique',
            'moteur'     => '🔩 Révision moteur recommandée',
        ];

        foreach ($fallbacks as $key => $message) {
            if (str_contains($type, $key)) {
                return $message;
            }
        }

        if ($priorite === 'urgente') {
            return "⚠️ Intervention immédiate requise pour {$m->getTypePanne()}";
        }

        return "🔍 Surveillance périodique recommandée pour {$m->getTypePanne()}";
    }
}