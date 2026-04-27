<?php

namespace App\Controller\Materiels;

use App\Entity\Materiels\Maintenance;
use App\Form\Materiels\MaintenanceType;
use App\Repository\Materiels\MaintenanceRepository;
use App\Service\MaintenanceAlertService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

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
    public function index(Request $request, MaintenanceRepository $repo): Response
    {
        $search = $request->query->get('search', '');
        $type   = $request->query->get('type', '');
        $sort   = $request->query->get('sort', 'dateMain');
        $dir    = $request->query->get('dir', 'DESC');

        $maintenances = $repo->searchWithMaterielName($search, $type, $sort, $dir);

        $maintenancesWithAlerts = [];
        foreach ($maintenances as $m) {
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
            'countByType'     => $repo->countByTypePanne(),
            'coutByMonth'     => $repo->getCoutByMonth(),
            'totalCout'       => $repo->getTotalCout(),
            'countByStatut'   => $repo->countByStatut(),
            'countByPriorite' => $repo->countByPriorite(),
        ]);
    }

    // ────────────────────────────────────────────────────────
    // CREATE
    // ────────────────────────────────────────────────────────
    #[Route('/new', name: 'agri_maintenances_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $maintenance = new Maintenance();
        
        // Générer une recommandation IA par défaut
        $defaultReco = $this->generateAIRecommendation('', 'moyenne', null, 'planifie', '');
        $maintenance->setRecommandation($defaultReco);
        
        $form = $this->createForm(MaintenanceType::class, $maintenance);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Générer la recommandation IA automatiquement
            $typePanne = $maintenance->getTypePanne();
            $priorite = $maintenance->getPriorite();
            $kilometrage = $maintenance->getKilometrage();
            $statut = $maintenance->getStatut();
            $description = $maintenance->getDescription();
            
            $iaRecommendation = $this->generateAIRecommendation($typePanne, $priorite, $kilometrage, $statut, $description);
            $maintenance->setRecommandation($iaRecommendation);
            
            $em->persist($maintenance);
            $em->flush();
            $this->addFlash('success', 'Maintenance ajoutée avec succès (recommandation IA générée).');
            return $this->redirectToRoute('agri_maintenances_index');
        }
        return $this->render('maintenances/new.html.twig', ['form' => $form->createView()]);
    }

    // ────────────────────────────────────────────────────────
    // EXPORT EXCEL
    // ────────────────────────────────────────────────────────
    #[Route('/export/excel', name: 'agri_maintenances_export_excel', methods: ['GET'])]
    public function exportExcel(MaintenanceRepository $repo): StreamedResponse
    {
        $maintenances = $repo->findAllOrderedByDate();
        $response = new StreamedResponse(function () use ($maintenances) {
            $handle = fopen('php://output', 'w+');
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
                    number_format($m->getCout(), 2, ',', ' '),
                    $m->getDateMain()?->format('d/m/Y') ?? '',
                    $m->getStatut() ?? '',
                    $m->getPriorite() ?? '',
                    $m->getKilometrage() ?? '',
                    $m->getDescription() ?? '',
                    $m->getRecommandation() ?? '',
                    $m->getIdM() ? '#' . $m->getIdM() : '',
                    $this->getMachineName($m),
                ], ';');
                $total += $m->getCout();
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
    // EXPORT PDF
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
    // API : GÉNÉRATION RECOMMANDATION IA (pour AJAX dans formulaire)
    // ────────────────────────────────────────────────────────
    #[Route('/api/generate-recommendation', name: 'agri_maintenances_api_generate_reco', methods: ['POST'])]
    public function generateRecommendationAPI(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            $typePanne = $data['typePanne'] ?? '';
            $priorite = $data['priorite'] ?? 'moyenne';
            $kilometrage = $data['kilometrage'] ?? 0;
            $statut = $data['statut'] ?? 'planifie';
            $description = $data['description'] ?? '';
            
            $recommendation = $this->generateAIRecommendation($typePanne, $priorite, $kilometrage, $statut, $description);
            
            return $this->json(['success' => true, 'recommendation' => $recommendation]);
            
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ────────────────────────────────────────────────────────
    // API : CALENDRIER COMPLET (TOUTES LES MAINTENANCES)
    // ────────────────────────────────────────────────────────
    #[Route('/api/calendar/all-events', name: 'agri_maintenances_api_calendar_events', methods: ['GET'])]
    public function getAllCalendarEvents(MaintenanceRepository $repo): JsonResponse
    {
        try {
            $maintenances = $repo->findAll();
            $events = [];
            $now = new \DateTime();

            foreach ($maintenances as $m) {
                $dateMain = $m->getDateMain();
                if (!$dateMain) continue;

                $machineName = $this->getMachineName($m);
                $type = $m->getTypePanne() ?? 'Générale';
                $priorite = $m->getPriorite() ?? 'moyenne';
                $statut = $m->getStatut() ?? 'planifie';

                $backgroundColor = $this->getEventColor($priorite, $statut);
                $borderColor = $this->getBorderColor($priorite, $statut);

                $events[] = [
                    'id' => $m->getIdMain(),
                    'title' => $machineName . ' - ' . $type,
                    'start' => $dateMain->format('Y-m-d'),
                    'allDay' => true,
                    'backgroundColor' => $backgroundColor,
                    'borderColor' => $borderColor,
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'id' => $m->getIdMain(),
                        'machineName' => $machineName,
                        'type' => $type,
                        'priorite' => $priorite,
                        'statut' => $statut,
                        'km' => $m->getKilometrage() ?? 0,
                        'cout' => $m->getCout() ?? 0,
                        'description' => $m->getDescription(),
                        'dateMain' => $dateMain->format('d/m/Y'),
                    ]
                ];
            }

            return $this->json(['success' => true, 'events' => $events, 'total' => count($events)]);

        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ────────────────────────────────────────────────────────
    // API : DÉTAIL MAINTENANCE AVEC RECOMMANDATION IA
    // ────────────────────────────────────────────────────────
    #[Route('/api/maintenance/{id}/detail', name: 'agri_maintenances_api_detail', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getMaintenanceDetail(int $id, MaintenanceRepository $repo): JsonResponse
    {
        try {
            $maintenance = $this->findMaintenance($id, $repo);
            if (!$maintenance) {
                return $this->json(['success' => false, 'error' => 'Maintenance non trouvée'], 404);
            }

            $machineName = $this->getMachineName($maintenance);
            $now = new \DateTime();
            $dateMain = $maintenance->getDateMain();
            $km = $maintenance->getKilometrage() ?? 0;
            $priorite = $maintenance->getPriorite() ?? 'moyenne';
            $statut = $maintenance->getStatut() ?? 'planifie';
            $type = $maintenance->getTypePanne() ?? 'générale';
            $cout = $maintenance->getCout() ?? 0;
            $description = $maintenance->getDescription() ?? '';

            $daysSince = $dateMain ? $now->diff($dateMain)->days : null;
            $yearsSince = $dateMain ? $now->diff($dateMain)->y : 0;

            // Générer la recommandation IA dynamique
            $iaRecommendation = $this->generateDynamicAIRecommendation(
                $machineName, $type, $priorite, $statut, $km, $cout, $description, $daysSince, $yearsSince
            );

            return $this->json([
                'success' => true,
                'id' => $maintenance->getIdMain(),
                'machineName' => $machineName,
                'type' => $type,
                'priorite' => $priorite,
                'statut' => $statut,
                'dateMain' => $dateMain?->format('d/m/Y'),
                'cout' => $cout,
                'km' => $km,
                'description' => $description,
                'daysSince' => $daysSince,
                'yearsSince' => $yearsSince,
                'iaRecommendation' => $iaRecommendation,
                'urgencyLevel' => $this->calculateUrgencyLevel($priorite, $statut, $km, $daysSince),
            ]);

        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ────────────────────────────────────────────────────────
    // API : SUGGESTION AUTOMATIQUE IA
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
            return $this->json(['success' => false, 'error' => 'Erreur serveur : ' . $e->getMessage()], 500);
        }
    }

    // ────────────────────────────────────────────────────────
    // API : PROMPT LIBRE IA
    // ────────────────────────────────────────────────────────
    #[Route('/api/generate-custom-prompt/{id}', name: 'agri_maintenances_api_custom_prompt', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function generateCustomPrompt(int $id, Request $request, MaintenanceRepository $repo): JsonResponse
    {
        try {
            $maintenance = $this->findMaintenance($id, $repo);
            if (!$maintenance) {
                return $this->json(['success' => false, 'error' => 'Maintenance non trouvée'], 404);
            }

            $data = json_decode($request->getContent(), true);
            $prompt = trim($data['prompt'] ?? '');
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
    // API : DURÉE DE VIE IA
    // ────────────────────────────────────────────────────────
    #[Route('/api/lifetime/{id}', name: 'agri_maintenances_api_lifetime', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function generateLifetime(int $id, MaintenanceRepository $repo): JsonResponse
    {
        try {
            $maintenance = $this->findMaintenance($id, $repo);
            if (!$maintenance) {
                return $this->json(['success' => false, 'error' => 'Maintenance non trouvée'], 404);
            }

            $km = $maintenance->getKilometrage() ?? 0;
            $prio = $maintenance->getPriorite() ?? 'moyenne';
            $type = $maintenance->getTypePanne() ?? 'Général';
            $maxKm = 20000;

            $vieRestante = max(0, min(100, (int) round((($maxKm - $km) / $maxKm) * 100)));
            $penalite = match($prio) { 'urgente' => 25, 'haute' => 15, 'moyenne' => 8, default => 3 };
            $vieRestante = max(0, $vieRestante - $penalite);
            $anneesEstimees = round(max(0, ($maxKm - $km)) / 3000, 1);
            $risque = match(true) { $prio === 'urgente' || $km > 15000 => 'Élevé', $prio === 'haute' || $km > 10000 => 'Moyen', default => 'Faible' };

            $composants = [
                ['nom' => 'Moteur', 'usure' => min(100, (int) round($km / 200))],
                ['nom' => 'Transmission', 'usure' => min(100, (int) round($km / 300))],
                ['nom' => 'Hydraulique', 'usure' => min(100, (int) round($km / 400))],
                ['nom' => 'Électricité', 'usure' => min(100, (int) round($km / 600))],
                ['nom' => 'Pneumatique', 'usure' => min(100, (int) round($km / 250))],
            ];

            return $this->json([
                'success' => true,
                'vieRestante' => $vieRestante,
                'anneesEstimees' => $anneesEstimees,
                'risquePanne' => $risque,
                'composants' => $composants,
                'recommandations' => $this->buildLifetimeRecommendations($type, $prio, $km, $composants),
            ]);

        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ────────────────────────────────────────────────────────
    // SHOW
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
            // Regénérer la recommandation IA automatiquement
            $typePanne = $maintenance->getTypePanne();
            $priorite = $maintenance->getPriorite();
            $kilometrage = $maintenance->getKilometrage();
            $statut = $maintenance->getStatut();
            $description = $maintenance->getDescription();
            
            $iaRecommendation = $this->generateAIRecommendation($typePanne, $priorite, $kilometrage, $statut, $description);
            $maintenance->setRecommandation($iaRecommendation);
            
            $em->flush();
            $this->addFlash('success', 'Maintenance mise à jour avec succès (recommandation IA régénérée).');
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
        if ($this->isCsrfTokenValid('delete_maintenance_' . $maintenance->getIdMain(), $request->request->get('_token'))) {
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
        } catch (\Throwable $e) {}
        return $repo->findOneBy(['idMain' => $id]);
    }

    private function getMachineName(Maintenance $m): string
    {
        if (method_exists($m, 'getNomMateriel') && $m->getNomMateriel()) {
            return $m->getNomMateriel();
        }
        if (method_exists($m, 'getNom') && $m->getNom()) {
            return $m->getNom();
        }
        return 'Machine non définie';
    }

    // ────────────────────────────────────────────────────────
    // GÉNÉRATION RECOMMANDATION IA (POUR STOCKAGE EN BASE)
    // ────────────────────────────────────────────────────────
    private function generateAIRecommendation(string $typePanne, string $priorite, ?int $kilometrage, string $statut, ?string $description): string
    {
        $recommendation = "";
        $km = $kilometrage ?? 0;
        
        // 1. Analyse de la priorité
        if ($priorite === 'urgente') {
            $recommendation .= "🚨 INTERVENTION URGENTE REQUISE !\n";
            $recommendation .= "• Ne pas utiliser la machine avant réparation complète\n";
            $recommendation .= "• Contacter immédiatement le service technique\n";
            $recommendation .= "• Prévoir les pièces de rechange en urgence\n\n";
        } elseif ($priorite === 'haute') {
            $recommendation .= "⚠️ HAUTE PRIORITÉ - Intervention sous 48h\n";
            $recommendation .= "• Planifier l'intervention rapidement\n";
            $recommendation .= "• Préparer les pièces nécessaires\n\n";
        } elseif ($priorite === 'moyenne') {
            $recommendation .= "🟡 PRIORITÉ MOYENNE - Planifier sous 15 jours\n";
            $recommendation .= "• Programmer la maintenance préventive\n";
            $recommendation .= "• Effectuer des contrôles visuels réguliers\n\n";
        } else {
            $recommendation .= "🟢 PRIORITÉ FAIBLE - Maintenance préventive\n";
            $recommendation .= "• Maintenir le planning d'entretien régulier\n";
            $recommendation .= "• Inspections périodiques standards\n\n";
        }
        
        // 2. Analyse du kilométrage
        if ($km >= 15000) {
            $recommendation .= "📊 KILOMÉTRAGE CRITIQUE ({$km} km) :\n";
            $recommendation .= "• Révision majeure OBLIGATOIRE immédiate\n";
            $recommendation .= "• Vidange complète + tous les filtres\n";
            $recommendation .= "• Contrôle exhaustif moteur et transmission\n\n";
        } elseif ($km >= 10000) {
            $recommendation .= "📊 KILOMÉTRAGE ÉLEVÉ ({$km} km) :\n";
            $recommendation .= "• Révision générale conseillée\n";
            $recommendation .= "• Vidange moteur et changement filtres\n\n";
        } elseif ($km >= 5000) {
            $recommendation .= "📊 KILOMÉTRAGE INTERMÉDIAIRE ({$km} km) :\n";
            $recommendation .= "• Vidange et entretien courant requis\n";
            $recommendation .= "• Vérification des niveaux et courroies\n\n";
        }
        
        // 3. Recommandation spécifique au type de panne
        $recommendation .= $this->getTypeSpecificReco($typePanne, $priorite);
        
        // 4. Analyse de la description
        if (!empty($description)) {
            $descLower = strtolower($description);
            if (str_contains($descLower, 'fuite')) {
                $recommendation .= "💧 FUITE DÉTECTÉE :\n• Identifier et localiser la fuite\n• Vérifier joints, flexibles et raccords\n\n";
            }
            if (str_contains($descLower, 'bruit') || str_contains($descLower, 'claquement')) {
                $recommendation .= "🔊 BRUIT ANORMAL :\n• Inspecter les roulements et pièces mobiles\n\n";
            }
            if (str_contains($descLower, 'fumée') || str_contains($descLower, 'fumee')) {
                $recommendation .= "💨 FUMÉE ANORMALE :\n• Contrôler l'injection et la combustion\n\n";
            }
        }
        
        // 5. Recommandations de sécurité
        $recommendation .= "\n⚠️ RECOMMANDATIONS DE SÉCURITÉ :\n";
        $recommendation .= "• Port des EPI obligatoires\n";
        $recommendation .= "• Couper le moteur avant intervention\n";
        $recommendation .= "• Déconnecter la batterie (pôle négatif)\n";
        $recommendation .= "• Extincteur à portée de main\n\n";
        
        // 6. Maintenance préventive
        $recommendation .= "📋 MAINTENANCE PRÉVENTIVE :\n";
        if ($km > 0) {
            $nextKm = ceil(($km + 1) / 5000) * 5000;
            $recommendation .= "• Prochaine vidange : tous les 5000 km (" . ($nextKm - $km) . " km restants)\n";
        }
        $recommendation .= "• Contrôle hebdomadaire : niveaux, pression pneus\n";
        $recommendation .= "• Tenir à jour le carnet de maintenance\n";
        
        return $recommendation;
    }

    private function getTypeSpecificReco(string $typePanne, string $priorite): string
    {
        $urgence = ($priorite === 'urgente') ? "URGENT - " : "";
        
        return match($typePanne) {
            'Moteur' => "🔧 PANNE MOTEUR :\n• {$urgence}Vérifier la compression\n• Contrôler les niveaux d'huile\n• Inspecter les injecteurs\n\n",
            'Électricité' => "⚡ PANNE ÉLECTRIQUE :\n• {$urgence}Tester la batterie\n• Vérifier les fusibles\n• Diagnostic OBD\n\n",
            'Hydraulique' => "💧 PANNE HYDRAULIQUE :\n• {$urgence}Contrôler le niveau de fluide\n• Inspecter les flexibles\n• Rechercher les fuites\n\n",
            'Transmission' => "⚙️ PANNE TRANSMISSION :\n• {$urgence}Vérifier l'huile de boîte\n• Contrôler les embrayages\n\n",
            'Pneumatique' => "🔵 PROBLÈME PNEUMATIQUE :\n• Contrôler la pression\n• Vérifier l'usure des pneus\n\n",
            'Vidange & filtres' => "🛢️ ENTRETIEN VIDANGE :\n• Vidange moteur complète\n• Remplacer tous les filtres\n\n",
            'Révision générale' => "📋 RÉVISION GÉNÉRALE :\n• Révision complète moteur\n• Contrôle transmission\n• Inspection hydraulique\n\n",
            default => "🔍 DIAGNOSTIC GÉNÉRAL :\n• Réaliser un diagnostic complet\n• Identifier la source du problème\n\n",
        };
    }

    // ────────────────────────────────────────────────────────
    // GÉNÉRATION RECOMMANDATION IA DYNAMIQUE (POUR POPUP CALENDRIER)
    // ────────────────────────────────────────────────────────
    private function generateDynamicAIRecommendation(string $machineName, string $type, string $priorite, string $statut, int $km, float $cout, string $description, ?int $daysSince, int $yearsSince): array
    {
        $recommendations = [];
        $actions = [];
        $summary = '';
        $priorityMessage = '';

        if ($priorite === 'urgente') {
            $priorityMessage = "🚨 **URGENCE ABSOLUE** - Intervention immédiate requise !";
            $actions[] = "🔧 Déployer un technicien d'urgence sans délai";
            $actions[] = "⛔ Ne pas utiliser la machine avant réparation";
        } elseif ($priorite === 'haute') {
            $priorityMessage = "⚠️ **HAUTE PRIORITÉ** - Planifier l'intervention sous 48h";
            $actions[] = "📅 Planifier une intervention dans les 48h";
        } elseif ($priorite === 'moyenne') {
            $priorityMessage = "🟡 **PRIORITÉ MOYENNE** - Planifier sous 15 jours";
            $actions[] = "📅 Programmer la maintenance sous 15 jours";
        } else {
            $priorityMessage = "🟢 **PRIORITÉ FAIBLE** - Maintenance préventive";
        }

        if ($km >= 15000) {
            $recommendations[] = "📊 Kilométrage critique ({$km} km) - Révision majeure obligatoire";
            $actions[] = "⚙️ Révision complète du moteur";
        } elseif ($km >= 10000) {
            $recommendations[] = "📊 Kilométrage élevé ({$km} km) - Révision générale conseillée";
        } elseif ($km >= 5000) {
            $recommendations[] = "📊 Kilométrage intermédiaire ({$km} km) - Entretien courant";
        }

        if ($yearsSince >= 2) {
            $recommendations[] = "⏰ Plus de 2 ans sans maintenance - Audit complet requis";
        } elseif ($yearsSince >= 1) {
            $recommendations[] = "📅 Maintenance annuelle recommandée";
        }

        if ($statut === 'en_cours') {
            $actions[] = "✅ Finaliser l'intervention en cours";
        } elseif ($statut === 'termine') {
            $actions[] = "📝 Mettre à jour le carnet de maintenance";
        }

        $summary = match(true) {
            $priorite === 'urgente' => "🔴 **ACTION IMMÉDIATE** : {$machineName} nécessite une intervention urgente !",
            $statut === 'en_cours' => "🔧 **INTERVENTION EN COURS** sur {$machineName}",
            $statut === 'termine' => "✅ **MAINTENANCE TERMINÉE** sur {$machineName}",
            default => "📋 **PLANIFICATION** : Maintenance programmée pour {$machineName}"
        };

        return [
            'summary' => $summary,
            'priorityMessage' => $priorityMessage,
            'recommendations' => array_slice($recommendations, 0, 5),
            'actions' => array_slice($actions, 0, 5),
        ];
    }

    // ────────────────────────────────────────────────────────
    // COULEURS POUR CALENDRIER
    // ────────────────────────────────────────────────────────
    private function getEventColor(string $priorite, string $statut): string
    {
        if ($priorite === 'urgente') return '#e74a3b';
        if ($statut === 'termine') return '#27ae60';
        if ($statut === 'en_cours') return '#f39c12';
        if ($priorite === 'haute') return '#e67e22';
        if ($priorite === 'moyenne') return '#3498db';
        if ($priorite === 'faible') return '#95a5a6';
        return '#2d6a2d';
    }

    private function getBorderColor(string $priorite, string $statut): string
    {
        if ($priorite === 'urgente') return '#c0392b';
        if ($statut === 'termine') return '#1e8449';
        if ($statut === 'en_cours') return '#e67e22';
        return '#2d6a2d';
    }

    private function calculateUrgencyLevel(string $priorite, string $statut, int $km, ?int $daysSince): array
    {
        $score = 0;
        if ($priorite === 'urgente') $score += 50;
        elseif ($priorite === 'haute') $score += 30;
        if ($km > 15000) $score += 30;
        elseif ($km > 10000) $score += 20;
        if ($daysSince && $daysSince > 365) $score += 40;

        if ($score >= 80) return ['level' => 'critique', 'label' => '🔴 CRITIQUE - Action immédiate', 'color' => '#e74a3b'];
        if ($score >= 60) return ['level' => 'elevé', 'label' => '🟠 ÉLEVÉ - Intervention sous 48h', 'color' => '#e67e22'];
        if ($score >= 40) return ['level' => 'moyen', 'label' => '🟡 MOYEN - Planifier sous 15j', 'color' => '#f39c12'];
        return ['level' => 'faible', 'label' => '🟢 FAIBLE - Surveillance normale', 'color' => '#27ae60'];
    }

    private function getMachineHistory(Maintenance $maintenance, MaintenanceRepository $repo): array
    {
        if (method_exists($maintenance, 'getIdM') && $maintenance->getIdM()) {
            try {
                return $repo->findBy(['idM' => $maintenance->getIdM()], ['dateMain' => 'DESC']);
            } catch (\Throwable $e) {}
        }
        return [$maintenance];
    }

    private function analyzeMaintenanceFrequency(array $history): array
    {
        $count = count($history);
        if ($count < 2) {
            return ['hasHistory' => false, 'totalMaintenances' => $count, 'averageInterval' => null, 'frequency' => 'première maintenance', 'recommendedInterval' => 90];
        }

        $totalInterval = 0;
        for ($i = 0; $i < $count - 1; $i++) {
            $d1 = $history[$i]->getDateMain();
            $d2 = $history[$i + 1]->getDateMain();
            if ($d1 && $d2) {
                $totalInterval += abs($d1->diff($d2)->days);
            }
        }

        $avgInterval = $count > 1 ? round($totalInterval / ($count - 1)) : 0;
        return ['hasHistory' => true, 'totalMaintenances' => $count, 'averageInterval' => $avgInterval, 'frequency' => 'modérée', 'recommendedInterval' => max(30, min(365, $avgInterval))];
    }

    private function analyzeMachineState(Maintenance $m): array
    {
        $km = $m->getKilometrage() ?? 0;
        $prio = $m->getPriorite() ?? 'moyenne';
        $statut = $m->getStatut() ?? 'planifie';
        $lastDate = $m->getDateMain();

        return [
            'km' => $km,
            'priority' => $prio,
            'status' => $statut,
            'daysSinceLastMaintenance' => $lastDate ? (new \DateTime())->diff($lastDate)->days : null,
            'healthScore' => $this->calculateHealthScore($km, $prio, $statut),
        ];
    }

    private function calculateHealthScore(int $km, string $prio, string $statut): int
    {
        $score = 100;
        $score -= match(true) { $km >= 15000 => 40, $km >= 10000 => 25, $km >= 5000 => 10, default => 0 };
        $score -= match($prio) { 'urgente' => 30, 'haute' => 20, 'moyenne' => 10, default => 0 };
        $score += match($statut) { 'termine' => 10, 'en_cours' => -15, default => 0 };
        return max(0, min(100, $score));
    }

    private function generateAISuggestions(Maintenance $m, array $frequency, array $state): array
    {
        $suggestions = [];
        $machineName = $this->getMachineName($m);
        $km = $state['km'];
        $daysSince = $state['daysSinceLastMaintenance'];
        $type = $m->getTypePanne() ?? '';

        $typeSpecificMap = [
            'Moteur' => ['icon' => '🔧', 'title' => 'Panne Moteur détectée', 'message' => "Vérifier la compression et les niveaux d'huile.", 'action' => 'Diagnostic moteur'],
            'Électricité' => ['icon' => '⚡', 'title' => 'Problème électrique', 'message' => "Tester la batterie et l'alternateur.", 'action' => 'Diagnostic électrique'],
            'Hydraulique' => ['icon' => '💧', 'title' => 'Défaillance hydraulique', 'message' => "Contrôler le niveau de fluide.", 'action' => 'Inspection hydraulique'],
        ];

        if (isset($typeSpecificMap[$type])) {
            $suggestions[] = array_merge($typeSpecificMap[$type], ['priority' => $state['priority'] === 'urgente' ? 'urgente' : 'moyenne']);
        }

        if ($daysSince !== null && $daysSince > ($frequency['recommendedInterval'] ?? 90)) {
            $suggestions[] = ['icon' => '⏰', 'title' => 'Maintenance dépassée', 'message' => "Dernière maintenance il y a {$daysSince} jours.", 'priority' => 'haute', 'action' => 'Planifier'];
        }

        if ($km >= 10000) {
            $suggestions[] = ['icon' => '📊', 'title' => 'Kilométrage élevé', 'message' => "{$machineName} a atteint {$km} km.", 'priority' => 'urgente', 'action' => 'Révision'];
        }

        if (empty($suggestions)) {
            $suggestions[] = ['icon' => '✅', 'title' => 'Maintenance préventive', 'message' => "{$machineName} semble en bon état.", 'priority' => 'basse', 'action' => 'Planifier'];
        }

        return $suggestions;
    }

    private function calculateNextRecommendedDate(Maintenance $m, array $frequency): ?\DateTime
    {
        $lastDate = $m->getDateMain();
        if (!$lastDate) return null;

        $interval = clone $lastDate;
        $daysToAdd = $frequency['recommendedInterval'] ?? 90;
        $km = $m->getKilometrage() ?? 0;
        $prio = $m->getPriorite() ?? 'moyenne';

        if ($km > 10000) $daysToAdd = max(30, $daysToAdd - 30);
        if ($prio === 'urgente') $daysToAdd = 7;
        elseif ($prio === 'haute') $daysToAdd = 30;

        return $interval->modify("+{$daysToAdd} days");
    }

    private function calculateUrgencyScore(Maintenance $m, array $frequency, array $state): int
    {
        $score = 0;
        $daysSince = $state['daysSinceLastMaintenance'] ?? 0;
        $recommended = $frequency['recommendedInterval'] ?? 90;

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
        return match(true) { $score >= 70 => 'critique', $score >= 50 => 'élevée', $score >= 30 => 'modérée', default => 'faible' };
    }

    private function getRecommendedActions(int $score, Maintenance $m): array
    {
        $actions = [];
        $machineName = $this->getMachineName($m);

        if ($score >= 70) {
            $actions[] = "🚨 INTERVENTION IMMÉDIATE — {$machineName}";
        } elseif ($score >= 50) {
            $actions[] = "⚠️ Planifier une intervention sous 48h";
        } elseif ($score >= 30) {
            $actions[] = "📅 Programmer une maintenance sous 15 jours";
        } else {
            $actions[] = "✅ Maintenir le planning régulier";
        }
        return $actions;
    }

    private function buildLocalPromptResponse(string $prompt, Maintenance $maintenance): string
    {
        $type = $maintenance->getTypePanne() ?? 'générale';
        $prio = $maintenance->getPriorite() ?? 'moyenne';
        $km = $maintenance->getKilometrage() ?? 0;
        $nom = $this->getMachineName($maintenance);
        $promptLc = strtolower($prompt);

        if (str_contains($promptLc, 'vérif')) {
            return "📋 Vérifications pour {$nom} :\n1. Inspection visuelle\n2. Test démarrage\n3. Contrôle niveaux\n" . ($prio === 'urgente' ? "\n⚠️ URGENT" : '');
        }
        if (str_contains($promptLc, 'coût')) {
            return "💰 Estimation pour {$nom} :\n- Pièces : 100-500 DT\n- Main d'œuvre : 50-120 DT/h";
        }
        return "📋 Recommandation pour {$nom} :\n1. Diagnostic complet\n2. Préparer les pièces\n3. Intervenir\n" . ($prio === 'urgente' ? "\n⚠️ ACTION IMMÉDIATE" : "");
    }

    private function buildLifetimeRecommendations(string $type, string $prio, int $km, array $composants): array
    {
        $recs = [];
        if ($prio === 'urgente') $recs[] = "Intervention immédiate requise";
        if ($km >= 15000) $recs[] = "Révision majeure obligatoire";
        elseif ($km >= 10000) $recs[] = "Révision générale conseillée";
        elseif ($km >= 5000) $recs[] = "Vidange moteur requise";

        usort($composants, fn($a, $b) => $b['usure'] - $a['usure']);
        if ($composants[0]['usure'] > 70) {
            $recs[] = "Composant critique : {$composants[0]['nom']} ({$composants[0]['usure']}%)";
        }
        return array_slice($recs, 0, 5);
    }
}