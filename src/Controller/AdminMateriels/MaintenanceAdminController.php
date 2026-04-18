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
use Psr\Log\LoggerInterface;

#[Route('/admin/materiels/maintenances', name: 'admin_maintenances_')]
class MaintenanceAdminController extends AbstractController
{
    // ─────────────────────────────────────────────────────────────────────────
    // INDEX - Liste des maintenances
    // ─────────────────────────────────────────────────────────────────────────
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(
        Request $request,
        MaintenanceRepository $repo,
        MachineRepository $machineRepo
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
        $totalCout    = $repo->getTotalCout();
        $machines     = $machineRepo->findAll();

        return $this->render('admins/maintenances/index.html.twig', [
            'maintenances' => $maintenances,
            'types'        => $types,
            'totalCout'    => $totalCout,
            'search'       => $search,
            'selectedType' => $type,
            'selectedIdM'  => $idM,
            'selectedStatut' => $statut,
            'selectedPriorite' => $priorite,
            'sort'         => $sort,
            'dir'          => $dir,
            'coutFilter'   => $coutFilter,
            'machines'     => $machines,
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

        /** @var Maintenance[] $maintenances */
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

        // Génération des recommandations IA
        foreach ($maintenances as $maintenance) {
            try {
                $existingReco = $maintenance->getRecommandation();
                if (empty($existingReco) || strlen($existingReco) < 10) {
                    $recommendation = $geminiService->generateRecommendation($maintenance);
                    $maintenance->setRecommandation($recommendation);
                    $logger->info('IA Gemini recommendation generated', [
                        'maintenance_id' => $maintenance->getIdMain(),
                        'type' => $maintenance->getTypePanne()
                    ]);
                }
            } catch (\Exception $e) {
                $logger->error('Failed to generate Gemini recommendation', [
                    'maintenance_id' => $maintenance->getIdMain(),
                    'error' => $e->getMessage()
                ]);
                $maintenance->setRecommandation($this->getFallbackRecommendation($maintenance));
            }
        }

        // Regroupement par mois
        $grouped = [];
        foreach ($maintenances as $m) {
            $date = $m->getDateMain();
            if (!$date) continue;
            $grouped[$date->format('Y-m')][] = $m;
        }
        krsort($grouped);

        $totalCout = array_sum(array_map(fn(Maintenance $m) => (float) $m->getCout(), $maintenances));
        
        $years = array_unique(array_filter(array_map(fn(Maintenance $m) => $m->getDateMain()?->format('Y'), $maintenances)));
        rsort($years);

        $machines = $machineRepo->findAll();
        $urgentesCount = count(array_filter($maintenances, fn($m) => $m->getPriorite() === 'urgente'));

        return $this->render('admins/maintenances/history.html.twig', [
            'grouped' => $grouped,
            'totalCout' => $totalCout,
            'machines' => $machines,
            'years' => $years,
            'selectedIdM' => $idM,
            'selectedStatut' => $statut,
            'selectedPriorite' => $priorite,
            'selectedYear' => $year,
            'totalCount' => count($maintenances),
            'urgentesCount' => $urgentesCount,
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
        $machines = $machineRepo->findAll();
        
        $healthScores = [];
        $allAlerts = [];
        $predictions = [];
        
        foreach ($machines as $machine) {
            $healthScores[$machine->getId()] = $geminiService->calculateHealthScore($machine);
            $alerts = $geminiService->generateSmartAlerts($machine);
            $allAlerts = array_merge($allAlerts, $alerts);
            $predictions[$machine->getId()] = $geminiService->predictFailure($machine);
        }
        
        $priorities = $geminiService->prioritizeInterventions($machines);
        $schedule = $geminiService->generateOptimizedSchedule($priorities, 30);
        
        // Statistiques globales
        $avgHealthScore = !empty($healthScores) ? array_sum(array_column($healthScores, 'score')) / count($healthScores) : 0;
        $criticalAlerts = count(array_filter($allAlerts, fn($a) => $a['type'] === 'critique'));
        $highRiskMachines = count(array_filter($predictions, fn($p) => $p['risque_panne'] === 'élevé'));
        
        return $this->render('admins/maintenances/ai_dashboard.html.twig', [
            'machines' => $machines,
            'healthScores' => $healthScores,
            'alerts' => $allAlerts,
            'predictions' => $predictions,
            'priorities' => $priorities,
            'schedule' => $schedule,
            'avgHealthScore' => round($avgHealthScore),
            'criticalAlerts' => $criticalAlerts,
            'highRiskMachines' => $highRiskMachines,
            'totalMachines' => count($machines)
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
        
        $prediction = $geminiService->predictFailure($machine);
        $healthScore = $geminiService->calculateHealthScore($machine);
        $alerts = $geminiService->generateSmartAlerts($machine);
        
        return $this->json([
            'machine' => [
                'id' => $machine->getId(),
                'nom' => $machine->getNom(),
                'marque' => $machine->getMarque()
            ],
            'health_score' => $healthScore,
            'prediction' => $prediction,
            'alerts' => $alerts
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
        $machines = $machineRepo->findAll();
        $priorities = $geminiService->prioritizeInterventions($machines);
        $schedule = $geminiService->generateOptimizedSchedule($priorities, 30);
        
        return $this->json([
            'total_machines' => count($machines),
            'interventions_planifiees' => count($schedule),
            'priorities' => $priorities,
            'planning' => $schedule
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
        $machines = $machineRepo->findAll();
        $allAlerts = [];
        
        foreach ($machines as $machine) {
            $alerts = $geminiService->generateSmartAlerts($machine);
            $allAlerts = array_merge($allAlerts, $alerts);
        }
        
        return $this->json([
            'total_alerts' => count($allAlerts),
            'critical_alerts' => count(array_filter($allAlerts, fn($a) => $a['type'] === 'critique')),
            'warning_alerts' => count(array_filter($allAlerts, fn($a) => $a['type'] === 'warning')),
            'alerts' => $allAlerts
        ]);
    }

    /**
     * Recommandation de secours (fallback)
     */
    private function getFallbackRecommendation(Maintenance $m): string
    {
        $type = strtolower($m->getTypePanne());
        $priorite = $m->getPriorite();
        $km = $m->getKilometrage();
        
        $fallbacks = [
            'moteur' => '🔧 Révision moteur recommandée tous les 500h ou 10 000 km',
            'vidange' => '🛢️ Prochaine vidange dans 3 mois ou 3000 km',
            'électrique' => '⚡ Contrôle préventif du circuit électrique et batterie',
            'hydraulique' => '💧 Vérification du niveau et des flexibles hydrauliques',
            'transmission' => '⚙️ Inspection de la transmission et de l\'embrayage',
            'frein' => '🛑 Contrôle d\'usure des plaquettes et disques',
        ];
        
        foreach ($fallbacks as $key => $message) {
            if (str_contains($type, $key)) {
                return $message;
            }
        }
        
        if ($priorite === 'urgente') {
            return "⚠️ Intervention immédiate requise pour {$m->getTypePanne()}";
        }
        
        if ($km && $km > 10000) {
            return "📅 Révision programmée dans les 30 jours ou 2000 km";
        }
        
        return "🔍 Surveillance périodique recommandée pour {$m->getTypePanne()}";
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
    // NEW - Créer une nouvelle maintenance
    // ─────────────────────────────────────────────────────────────────────────
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        MachineRepository $machineRepo
    ): Response {
        $maintenance = new Maintenance();
        $machines = $machineRepo->findAll();
        $errors = [];

        $maintenance->setStatut('planifie');
        $maintenance->setPriorite('moyenne');

        if ($request->isMethod('POST')) {

            $typePanne = trim($request->request->get('typePanne', ''));
            if (empty($typePanne)) {
                $errors['typePanne'] = 'Le type de panne est obligatoire.';
            } else {
                $maintenance->setTypePanne($typePanne);
            }

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

            $dateStr = $request->request->get('dateMain');
            if (empty($dateStr)) {
                $errors['dateMain'] = 'La date de maintenance est obligatoire.';
            } else {
                try {
                    $date = new \DateTime($dateStr);
                    $today = new \DateTime('today');
                    if ($date > $today) {
                        $errors['dateMain'] = 'La date ne peut pas être dans le futur.';
                    } else {
                        $maintenance->setDateMain($date);
                    }
                } catch (\Exception $e) {
                    $errors['dateMain'] = 'Format de date invalide.';
                }
            }

            $description = trim($request->request->get('description', ''));
            if (!empty($description)) {
                if (strlen($description) > 1000) {
                    $errors['description'] = 'La description ne peut pas dépasser 1000 caractères.';
                } elseif (preg_match('/[<>{}]/', $description)) {
                    $errors['description'] = 'La description ne doit pas contenir les caractères < > { }.';
                } else {
                    $maintenance->setDescription($description);
                }
            }

            $recommandation = trim($request->request->get('recommandation', ''));
            if (!empty($recommandation)) {
                if (strlen($recommandation) > 2000) {
                    $errors['recommandation'] = 'La recommandation ne peut pas dépasser 2000 caractères.';
                } else {
                    $maintenance->setRecommandation($recommandation);
                }
            }

            $statut = $request->request->get('statut', 'planifie');
            $allowedStatuts = ['planifie', 'en_cours', 'termine'];
            if (!in_array($statut, $allowedStatuts)) {
                $errors['statut'] = 'Statut invalide.';
            } else {
                $maintenance->setStatut($statut);
            }

            $priorite = $request->request->get('priorite', 'moyenne');
            $allowedPriorites = ['faible', 'moyenne', 'haute', 'urgente'];
            if (!in_array($priorite, $allowedPriorites)) {
                $errors['priorite'] = 'Priorité invalide.';
            } else {
                $maintenance->setPriorite($priorite);
            }

            $kilometrage = $request->request->get('kilometrage');
            if (!empty($kilometrage)) {
                $kmInt = (int) $kilometrage;
                if ($kmInt < 0) {
                    $errors['kilometrage'] = 'Le kilométrage doit être positif ou nul.';
                } elseif ($kmInt > 9999999) {
                    $errors['kilometrage'] = 'Kilométrage trop élevé.';
                } else {
                    $maintenance->setKilometrage($kmInt);
                }
            }

            $idM = $request->request->get('idM');
            if (!empty($idM)) {
                $idMInt = (int) $idM;
                $machine = $machineRepo->find($idMInt);
                if ($machine) {
                    $maintenance->setIdM($idMInt);
                    $maintenance->setNom($machine->getNom());
                } else {
                    $errors['idM'] = 'La machine sélectionnée n\'existe pas.';
                }
            } else {
                $maintenance->setIdM(null);
                $maintenance->setNom(null);
            }

            if (empty($errors)) {
                $em->persist($maintenance);
                $em->flush();
                $this->addFlash('success', 'Maintenance ajoutée avec succès.');
                return $this->redirectToRoute('admin_maintenances_index');
            } else {
                foreach ($errors as $field => $error) {
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
    // EDIT - Modifier une maintenance
    // ─────────────────────────────────────────────────────────────────────────
    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(
        int $id,
        Request $request,
        MaintenanceRepository $repo,
        MachineRepository $machineRepo,
        EntityManagerInterface $em
    ): Response {
        $maintenance = $repo->find($id);
        if (!$maintenance) {
            $this->addFlash('error', 'Maintenance introuvable.');
            return $this->redirectToRoute('admin_maintenances_index');
        }

        $machines = $machineRepo->findAll();
        $errors = [];

        if ($request->isMethod('POST')) {

            $typePanne = trim($request->request->get('typePanne', ''));
            if (empty($typePanne)) {
                $errors['typePanne'] = 'Le type de panne est obligatoire.';
            } else {
                $maintenance->setTypePanne($typePanne);
            }

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

            $dateStr = $request->request->get('dateMain');
            if (empty($dateStr)) {
                $errors['dateMain'] = 'La date de maintenance est obligatoire.';
            } else {
                try {
                    $date = new \DateTime($dateStr);
                    $today = new \DateTime('today');
                    if ($date > $today) {
                        $errors['dateMain'] = 'La date ne peut pas être dans le futur.';
                    } else {
                        $maintenance->setDateMain($date);
                    }
                } catch (\Exception $e) {
                    $errors['dateMain'] = 'Format de date invalide.';
                }
            }

            $description = trim($request->request->get('description', ''));
            if (!empty($description)) {
                if (strlen($description) > 1000) {
                    $errors['description'] = 'La description ne peut pas dépasser 1000 caractères.';
                } elseif (preg_match('/[<>{}]/', $description)) {
                    $errors['description'] = 'La description ne doit pas contenir les caractères < > { }.';
                } else {
                    $maintenance->setDescription($description);
                }
            } else {
                $maintenance->setDescription(null);
            }

            $recommandation = trim($request->request->get('recommandation', ''));
            if (!empty($recommandation)) {
                if (strlen($recommandation) > 2000) {
                    $errors['recommandation'] = 'La recommandation ne peut pas dépasser 2000 caractères.';
                } else {
                    $maintenance->setRecommandation($recommandation);
                }
            } else {
                $maintenance->setRecommandation(null);
            }

            $statut = $request->request->get('statut', 'planifie');
            $allowedStatuts = ['planifie', 'en_cours', 'termine'];
            if (!in_array($statut, $allowedStatuts)) {
                $errors['statut'] = 'Statut invalide.';
            } else {
                $maintenance->setStatut($statut);
            }

            $priorite = $request->request->get('priorite', 'moyenne');
            $allowedPriorites = ['faible', 'moyenne', 'haute', 'urgente'];
            if (!in_array($priorite, $allowedPriorites)) {
                $errors['priorite'] = 'Priorité invalide.';
            } else {
                $maintenance->setPriorite($priorite);
            }

            $kilometrage = $request->request->get('kilometrage');
            if (!empty($kilometrage)) {
                $kmInt = (int) $kilometrage;
                if ($kmInt < 0) {
                    $errors['kilometrage'] = 'Le kilométrage doit être positif ou nul.';
                } elseif ($kmInt > 9999999) {
                    $errors['kilometrage'] = 'Kilométrage trop élevé.';
                } else {
                    $maintenance->setKilometrage($kmInt);
                }
            } else {
                $maintenance->setKilometrage(null);
            }

            $idM = $request->request->get('idM');
            if (!empty($idM)) {
                $idMInt = (int) $idM;
                $machine = $machineRepo->find($idMInt);
                if ($machine) {
                    $maintenance->setIdM($idMInt);
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
                $this->addFlash('success', 'Maintenance mise à jour avec succès.');
                return $this->redirectToRoute('admin_maintenances_index');
            } else {
                foreach ($errors as $field => $error) {
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
            $this->addFlash('success', 'Maintenance supprimée avec succès.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('admin_maintenances_index');
    }
    // ─────────────────────────────────────────────────────────────────────────
// API: Analyse IA complète pour une maintenance
// ─────────────────────────────────────────────────────────────────────────
#[Route('/ai/analyze/{id}', name: 'ai_analyze', methods: ['GET'])]
public function aiAnalyzeMaintenance(
    int $id,
    MaintenanceRepository $repo,
    GeminiRecommendationService $geminiService
): JsonResponse {
    $maintenance = $repo->find($id);
    if (!$maintenance) {
        return $this->json(['error' => 'Maintenance non trouvée'], 404);
    }
    
    // Générer une analyse complète
    $recommendation = $geminiService->generateRecommendation($maintenance);
    
    // Analyse supplémentaire basée sur le type de panne
    $analysis = $this->generateDetailedAnalysis($maintenance);
    
    return $this->json([
        'recommendation' => $recommendation,
        'analysis' => $analysis['description'],
        'next_maintenance' => $analysis['next_maintenance'],
        'actions' => $analysis['actions'],
        'tips' => $analysis['tips']
    ]);
}

/**
 * Génère une analyse détaillée basée sur le type de panne
 */
private function generateDetailedAnalysis(Maintenance $m): array
{
    $type = strtolower($m->getTypePanne());
    $priorite = $m->getPriorite();
    $km = $m->getKilometrage();
    
    $analyses = [
        'moteur' => [
            'description' => 'Analyse moteur: Usure normale détectée. Surveillance recommandée des niveaux d\'huile et de liquide de refroidissement.',
            'next_maintenance' => $km ? 'Dans ' . min(5000, $km * 0.5) . ' km ou 6 mois' : 'Dans 6 mois',
            'actions' => ['Contrôle du niveau d\'huile', 'Vérification des bougies', 'Inspection des courroies'],
            'tips' => 'Utilisez une huile moteur de qualité premium pour prolonger la durée de vie.'
        ],
        'vidange' => [
            'description' => 'Maintenance vidange standard. Le cycle de vidange est respecté.',
            'next_maintenance' => $km ? 'Dans ' . min(5000, $km * 0.6) . ' km' : 'Dans 5000 km',
            'actions' => ['Changement d\'huile', 'Remplacement du filtre à huile', 'Contrôle du niveau'],
            'tips' => 'Respectez scrupuleusement les intervalles de vidange pour éviter l\'usure prématurée.'
        ],
        'électrique' => [
            'description' => 'Système électrique: Diagnostic recommandé. Risque de panne batterie/alternateur.',
            'next_maintenance' => 'Dans 30 jours',
            'actions' => ['Test de batterie', 'Vérification alternateur', 'Contrôle du faisceau'],
            'tips' => 'Un voyant batterie qui s\'allume indique souvent un problème d\'alternateur.'
        ],
        'hydraulique' => [
            'description' => 'Circuit hydraulique: Contrôle préventif nécessaire. Risque de fuite modéré.',
            'next_maintenance' => 'Dans 3 mois',
            'actions' => ['Contrôle niveau huile', 'Inspection flexibles', 'Test de pression'],
            'tips' => 'Surveillez les traces d\'huile sous la machine après utilisation.'
        ],
        'transmission' => [
            'description' => 'Transmission: Point de contrôle atteint. Révision recommandée.',
            'next_maintenance' => $km ? 'Dans ' . min(10000, $km) . ' km' : 'Dans 10000 km',
            'actions' => ['Vidange transmission', 'Réglage embrayage', 'Contrôle cardans'],
            'tips' => 'Une transmission qui patine ou accroche nécessite une intervention rapide.'
        ],
        'frein' => [
            'description' => 'Système de freinage: Usure dans les normes. Surveillance continue.',
            'next_maintenance' => $km ? 'Dans ' . min(5000, $km * 0.8) . ' km' : 'Dans 5000 km',
            'actions' => ['Contrôle plaquettes', 'Vérification disques', 'Test efficacité'],
            'tips' => 'Un grincement au freinage indique des plaquettes à remplacer.'
        ]
    ];
    
    $default = [
        'description' => "Maintenance {$m->getTypePanne()} analysée. Aucune anomalie majeure détectée.",
        'next_maintenance' => 'Dans 3 mois',
        'actions' => ['Maintenance préventive standard', 'Contrôle visuel', 'Test fonctionnel'],
        'tips' => 'Documentez chaque intervention pour un meilleur suivi.'
    ];
    
    $result = $analyses[$type] ?? $default;
    
    // Ajustement selon priorité
    if ($priorite === 'urgente') {
        $result['description'] = '⚠️ URGENT: ' . $result['description'];
        $result['next_maintenance'] = 'IMMÉDIAT';
        $result['actions'][] = 'Intervention d\'urgence requise';
    }
    
    return $result;
}
}