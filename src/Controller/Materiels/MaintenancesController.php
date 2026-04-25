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

    // ── INDEX ──
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

    // ── API : Prompt libre IA (Recommandations) ──
    #[Route('/api/generate-custom-prompt/{id}', name: 'agri_maintenances_api_custom_prompt', methods: ['POST'])]
    public function generateCustomPrompt(int $id, Request $request, MaintenanceRepository $repo): JsonResponse
    {
        $maintenance = $repo->findOneWithMaterielName($id);
        if (!$maintenance) {
            return $this->json(['error' => 'Maintenance non trouvée'], 404);
        }

        $data   = json_decode($request->getContent(), true);
        $prompt = trim($data['prompt'] ?? '');
        if (!$prompt) {
            return $this->json(['error' => 'Prompt vide'], 400);
        }

        $response = $this->buildLocalPromptResponse($prompt, $maintenance);

        return $this->json([
            'success'  => true,
            'response' => $response,
        ]);
    }

    private function buildLocalPromptResponse(string $prompt, Maintenance $maintenance): string
    {
        $type     = $maintenance->getTypePanne() ?? 'générale';
        $prio     = $maintenance->getPriorite() ?? 'moyenne';
        $km       = $maintenance->getKilometrage() ?? 0;
        $nom      = $maintenance->getNom() ?? 'la machine';
        $promptLc = strtolower($prompt);

        if (str_contains($promptLc, 'vérif') || str_contains($promptLc, 'étape')) {
            return "📋 **RECOMMANDATIONS - Vérifications** pour {$nom} (panne {$type}) :\n\n"
                . "1. 🔍 Inspection visuelle générale (fuites, câbles, fixations)\n"
                . "2. 🚀 Test de démarrage et fonctionnement à vide\n"
                . "3. 🌡️ Relevé des températures et pressions\n"
                . "4. 💧 Contrôle des niveaux (huile, liquide refroidissement, hydraulique)\n"
                . "5. 💻 Diagnostic électronique si disponible\n"
                . "6. ⚡ Test de charge progressive\n"
                . ($prio === 'urgente' ? "\n⚠️ **RECOMMANDATION URGENTE** : Ne pas utiliser la machine avant réparation." : '');
        }

        if (str_contains($promptLc, 'pièce') || str_contains($promptLc, 'remplacer')) {
            $rec = "🔧 **RECOMMANDATIONS - Pièces à contrôler/remplacer** pour {$nom} :\n\n";
            $rec .= "| Pièce | Action | Priorité |\n";
            $rec .= "|-------|--------|----------|\n";
            $rec .= "| Filtres (air, huile, carburant) | Vérifier et remplacer si nécessaire | Haute |\n";
            $rec .= "| Courroies et chaînes | Contrôler l'usure | Haute |\n";
            $rec .= "| Joints et flexibles | Inspection fuites | Moyenne |\n";
            if ($km > 5000) $rec .= "| Huile moteur + filtre | Vidange obligatoire (km > 5000) | Haute |\n";
            if ($km > 10000) $rec .= "| Roulements et paliers | Inspection approfondie (km > 10000) | Haute |\n";
            if ($km > 15000) $rec .= "| Pièces d'usure moteur | Remplacement préventif (km > 15000) | Urgente |\n";
            $rec .= "| Bougies/injecteurs | Nettoyage ou remplacement si perte puissance | Moyenne |\n";
            $rec .= "| Batterie | Test et recharge si démarrage difficile | Basse |\n";
            return $rec;
        }

        if (str_contains($promptLc, 'sécurité') || str_contains($promptLc, 'précaution')) {
            return "⚠️ **RECOMMANDATIONS DE SÉCURITÉ** pour l'intervention sur {$nom} :\n\n"
                . "**Équipements obligatoires** :\n"
                . "• Casque, gants résistants, lunettes de protection\n"
                . "• Chaussures de sécurité avec semelle anti-perforation\n"
                . "• Gilet haute visibilité si intervention en extérieur\n\n"
                . "**Procédures avant intervention** :\n"
                . "• Couper le moteur et attendre le refroidissement complet\n"
                . "• Déconnecter la batterie (pôle négatif en premier)\n"
                . "• Caler les roues pour éviter tout mouvement\n"
                . "• Utiliser des chandelles certifiées\n\n"
                . "**Pendant l'intervention** :\n"
                . "• Extincteur à portée de main\n"
                . "• Zone bien ventilée\n"
                . "• Ne jamais travailler seul sur une intervention lourde\n\n"
                . "**Après intervention** :\n"
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
            return "⏱️ **RECOMMANDATIONS - Temps et outils** pour panne {$type} :\n\n"
                . "**Durée estimée** : {$duree}\n\n"
                . "**Outils nécessaires** :\n"
                . "• Clés à douilles (jeu complet 8-24mm)\n"
                . "• Multimètre digital\n"
                . "• Valise de diagnostic OBD\n"
                . "• Manomètre de compression\n"
                . "• Thermomètre infrarouge\n"
                . "• Bac de récupération\n\n"
                . "**Technicien recommandé** : Spécialiste en maintenance agricole";
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
            return "💰 **RECOMMANDATIONS - Estimation des coûts** pour {$nom} :\n\n"
                . "| Poste | Coût estimé (DT) |\n"
                . "|-------|------------------|\n"
                . "| Pièces détachées | {$coutPieces} |\n"
                . "| Main d'œuvre (taux horaire) | 50 – 120 |\n"
                . "| Forfait déplacement technicien | 30 – 80 |\n"
                . ($km > 10000 ? "| Révision générale supplémentaire | +200 à 500 |\n" : '')
                . "| **Total estimé** | **À partir de " . explode(' – ', $coutPieces)[0] . " DT** |\n\n"
                . "💡 **Recommandations** : Demander 2-3 devis, vérifier la garantie des pièces";
        }

        // Recommandation générique par défaut
        return "📋 **RECOMMANDATION GÉNÉRALE** pour {$nom} (panne {$type}, priorité {$prio}) :\n\n"
            . "**Plan d'action recommandé** :\n"
            . "1. 🔍 Réaliser un diagnostic complet avant toute intervention\n"
            . "2. 🎯 Identifier précisément la source du problème\n"
            . "3. 📦 Préparer les pièces et outils nécessaires\n"
            . "4. 🔧 Intervenir selon les procédures constructeur\n"
            . "5. ✅ Tester le bon fonctionnement après intervention\n"
            . "6. 📝 Documenter l'intervention dans le carnet de maintenance\n\n"
            . ($prio === 'urgente' 
                ? "⚠️ **ACTION IMMÉDIATE** : Priorité urgente - intervenir sans délai." 
                : "📅 **PLANIFICATION** : Planifier l'intervention dans les meilleurs délais.");
    }

    // ── API : Prévision durée de vie IA (conservé) ──
    #[Route('/api/lifetime/{id}', name: 'agri_maintenances_api_lifetime', methods: ['GET'])]
    public function generateLifetime(int $id, MaintenanceRepository $repo): JsonResponse
    {
        $maintenance = $repo->findOneWithMaterielName($id);
        if (!$maintenance) {
            return $this->json(['error' => 'Maintenance non trouvée'], 404);
        }

        $km    = $maintenance->getKilometrage() ?? 0;
        $prio  = $maintenance->getPriorite() ?? 'moyenne';
        $type  = $maintenance->getTypePanne() ?? 'Général';
        $maxKm = 20000;

        $vieRestante = max(0, min(100, (int) round((($maxKm - $km) / $maxKm) * 100)));

        $penalite = match($prio) {
            'urgente' => 25,
            'haute'   => 15,
            'moyenne' => 8,
            default   => 3,
        };
        $vieRestante = max(0, $vieRestante - $penalite);

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

        foreach ($composants as &$comp) {
            if (strtolower($comp['nom']) === strtolower(explode(' ', $type)[0])) {
                $comp['usure'] = min(100, $comp['usure'] + ($prio === 'urgente' ? 30 : ($prio === 'haute' ? 20 : 10)));
            }
        }
        unset($comp);

        $recommandations = $this->buildLifetimeRecommendations($type, $prio, $km, $composants);

        return $this->json([
            'success'         => true,
            'vieRestante'     => $vieRestante,
            'anneesEstimees'  => $anneesEstimees,
            'risquePanne'     => $risque,
            'composants'      => $composants,
            'recommandations' => $recommandations,
            'kilometrage'     => $km,
            'prochaine_vidange' => max(0, (int) ceil($km / 5000) * 5000 - $km) . ' km',
            'prochaine_revision'=> max(0, (int) ceil($km / 10000) * 10000 - $km) . ' km',
        ]);
    }

    private function buildLifetimeRecommendations(string $type, string $prio, int $km, array $composants): array
    {
        $recs = [];

        if ($prio === 'urgente') {
            $recs[] = "Intervention immédiate requise — risque d'aggravation si non traité.";
        } elseif ($prio === 'haute') {
            $recs[] = "Planifier l'intervention sous 2 semaines maximum.";
        }

        if ($km >= 5000) {
            $recs[] = "Vidange moteur et remplacement des filtres requis (kilométrage > 5 000 km).";
        }
        if ($km >= 10000) {
            $recs[] = "Révision générale conseillée — contrôle complet de tous les systèmes.";
        }
        if ($km >= 15000) {
            $recs[] = "Inspection approfondie de la transmission et du moteur (kilométrage critique).";
        }

        usort($composants, fn($a, $b) => $b['usure'] - $a['usure']);
        if ($composants[0]['usure'] > 70) {
            $recs[] = "Composant critique : {$composants[0]['nom']} ({$composants[0]['usure']}% d'usure) — prévoir remplacement.";
        }

        $recs[] = "Effectuer des inspections visuelles quotidiennes et noter toute anomalie.";
        $recs[] = "Tenir à jour le carnet de maintenance pour assurer la traçabilité.";

        return array_slice($recs, 0, 5);
    }

    // ── API : Plan de maintenance (JSON) ──
    #[Route('/api/schedules/generate', name: 'agri_maintenances_api_schedule_generate', methods: ['POST'])]
    public function generateSchedule(Request $request, MaintenanceRepository $repo): JsonResponse
    {
        $data        = json_decode($request->getContent(), true);
        $maintenance = null;

        if (isset($data['maintenanceId'])) {
            $maintenance = $repo->findOneWithMaterielName($data['maintenanceId']);
            if (!$maintenance) {
                return $this->json(['error' => 'Maintenance non trouvée'], 404);
            }
        }

        $options = $data['options'] ?? ['intervention', 'prevention', 'pieces', 'optimisation', 'securite', 'controle'];

        $machineData = [
            'type'        => $maintenance ? $maintenance->getTypePanne()   : ($data['typePanne']   ?? 'Non spécifié'),
            'priorite'    => $maintenance ? $maintenance->getPriorite()    : ($data['priorite']    ?? 'moyenne'),
            'statut'      => $maintenance ? $maintenance->getStatut()      : ($data['statut']      ?? 'planifie'),
            'kilometrage' => $maintenance ? $maintenance->getKilometrage() : ($data['kilometrage'] ?? null),
            'nom'         => $maintenance ? ($maintenance->getNom() ?? 'Machine non spécifiée') : ($data['nomMachine'] ?? 'Machine non spécifiée'),
            'description' => $maintenance ? $maintenance->getDescription() : ($data['description'] ?? ''),
            'recommandation' => $maintenance ? $maintenance->getRecommandation() : ($data['recommandation'] ?? ''),
            'cout'        => $maintenance ? $maintenance->getCout()        : ($data['cout']        ?? 0),
            'dateMain'    => $maintenance && $maintenance->getDateMain()   ? $maintenance->getDateMain()->format('Y-m-d') : ($data['dateMain'] ?? null),
        ];

        $interventions = $this->calculateRecommendedInterventions($machineData);
        $plan          = $this->generateSchedulePlan($machineData, $options, $interventions);

        return $this->json(['success' => true, 'plan' => $plan, 'generatedBy' => 'local_algorithm']);
    }

    private function calculateRecommendedInterventions(array $machineData): array
    {
        $interventions = [];
        $km            = $machineData['kilometrage'] ?? 0;
        $typePanne     = $machineData['type'] ?? '';
        $priorite      = $machineData['priorite'] ?? 'moyenne';

        if ($km >= 5000)  $interventions[] = ['name' => '🛢️ Vidange moteur',          'reason' => 'Kilométrage > 5 000 km',  'priority' => $km >= 8000 ? 'haute' : 'moyenne'];
        if ($km >= 10000) $interventions[] = ['name' => '🔧 Révision générale',        'reason' => 'Kilométrage > 10 000 km', 'priority' => 'haute'];
        if ($km >= 15000) $interventions[] = ['name' => '⚙️ Remplacement transmission','reason' => 'Kilométrage > 15 000 km', 'priority' => 'urgente'];

        $typeInterventions = [
            'Mécanique'   => [['name' => '🔧 Contrôle des pièces mécaniques', 'priority' => 'haute'],  ['name' => '📊 Test de performance moteur',       'priority' => 'moyenne']],
            'Électricité' => [['name' => '⚡ Diagnostic circuit électrique',  'priority' => 'haute'],  ['name' => '🔋 Test batterie et alternateur',      'priority' => 'moyenne']],
            'Hydraulique' => [['name' => '💧 Contrôle circuit hydraulique',   'priority' => 'haute'],  ['name' => '🔍 Détection des fuites',              'priority' => 'haute']],
            'Moteur'      => [['name' => '🔧 Diagnostic complet moteur',      'priority' => 'urgente'],['name' => '🌡️ Contrôle du refroidissement',       'priority' => 'haute']],
            'Vidange & filtres' => [['name' => '🛢️ Vidange et changement filtres','priority' => 'moyenne'],['name' => '🌬️ Nettoyage filtre à air','priority' => 'basse']],
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

    private function generateSchedulePlan(array $d, array $options, array $interventions): string
    {
        $plan  = "# 📋 PLAN DE MAINTENANCE\n\n## Récapitulatif\n";
        $plan .= "- **Machine** : {$d['nom']}\n";
        $plan .= "- **Type** : {$d['type']}\n";
        $plan .= "- **Priorité** : " . ucfirst($d['priorite']) . "\n";
        $plan .= "- **Kilométrage** : " . ($d['kilometrage'] ? $d['kilometrage'] . ' km' : 'Non renseigné') . "\n";
        $plan .= "- **Date prévue** : " . ($d['dateMain'] ?? 'Non renseignée') . "\n";
        $plan .= "- **Coût estimé** : " . number_format($d['cout'], 2, ',', ' ') . " DT\n\n";

        if (in_array('intervention', $options)) {
            $plan .= "## 🔍 DIAGNOSTIC PRÉLIMINAIRE\n1. Inspection visuelle générale\n2. Test à vide\n3. Relevé des paramètres\n4. Identification des anomalies\n\n## 📝 ÉTAPES D'INTERVENTION\n";
            foreach ($interventions as $idx => $i) {
                $plan .= ($idx + 1) . ". **{$i['name']}**\n";
                if (isset($i['reason'])) $plan .= "   - Motif : {$i['reason']}\n";
                $plan .= "   - Priorité : " . ucfirst($i['priority']) . "\n";
            }
            $plan .= "\n";
        }

        if (in_array('prevention', $options)) {
            $days = ['urgente' => 30, 'haute' => 60, 'moyenne' => 90, 'faible' => 180][$d['priorite']] ?? 90;
            $plan .= "## 📅 PLANNING PRÉVENTIF\n- Prochaine maintenance : dans {$days} jours\n";
            if ($d['kilometrage'] > 0) {
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

        $score = 100;
        if ($d['priorite'] === 'urgente') $score -= 30;
        elseif ($d['priorite'] === 'haute') $score -= 20;
        elseif ($d['priorite'] === 'moyenne') $score -= 10;
        if ($d['kilometrage'] > 10000) $score -= 15;
        if ($d['kilometrage'] > 15000) $score -= 10;

        $plan .= "## 📊 SCORE DE SANTÉ : **" . max(0, $score) . "/100**\n\n";
        $plan .= "## 💡 RECOMMANDATIONS\n1. Suivre rigoureusement le planning d'entretien\n2. Former les opérateurs aux bonnes pratiques\n3. Tenir un carnet de bord à jour\n4. Maintenir un stock minimum de pièces consommables\n5. Inspections visuelles quotidiennes\n";

        return $plan;
    }

    // ── API : Page HTML Schedule ──
    #[Route('/api/schedule/{id}', name: 'agri_maintenances_api_schedule', methods: ['GET'])]
    public function apiSchedule(int $id, MaintenanceRepository $repo): Response
    {
        $maintenance = $repo->findOneWithMaterielName($id);
        if (!$maintenance) throw $this->createNotFoundException('Maintenance non trouvée.');
        return $this->render('maintenances/api_schedule_html.html.twig', ['maintenance' => $maintenance]);
    }

    // ── CREATE ──
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

    // ── SHOW ──
    #[Route('/{id}', name: 'agri_maintenances_show', methods: ['GET'])]
    public function show(int $id, MaintenanceRepository $repo): Response
    {
        $maintenance = $repo->findOneWithMaterielName($id);
        if (!$maintenance) throw $this->createNotFoundException('Maintenance non trouvée.');
        return $this->render('maintenances/show.html.twig', ['maintenance' => $maintenance]);
    }

    // ── EDIT ──
    #[Route('/{id}/edit', name: 'agri_maintenances_edit', methods: ['GET', 'POST'])]
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

    // ── EXPORT EXCEL ──
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
                fputcsv($handle, [$idx++, $m->getTypePanne(), number_format($m->getCout(), 2, ',', ' '),
                    $m->getDateMain()?->format('d/m/Y') ?? '', $m->getStatut() ?? '', $m->getPriorite() ?? '',
                    $m->getKilometrage() ?? '', $m->getDescription() ?? '', $m->getRecommandation() ?? '',
                    $m->getIdM() ? '#'.$m->getIdM() : '', $m->getNom() ?? ''], ';');
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

    // ── EXPORT PDF ──
    #[Route('/export/pdf', name: 'agri_maintenances_export_pdf', methods: ['GET'])]
    public function exportPdf(MaintenanceRepository $repo): Response
    {
        return $this->render('maintenances/pdf.html.twig', [
            'maintenances' => $repo->findAllOrderedByDate(),
            'generatedAt'  => new \DateTime(),
            'totalCout'    => $repo->getTotalCout(),
        ]);
    }

    // ── DELETE ──
    #[Route('/{id}/delete', name: 'agri_maintenances_delete', methods: ['POST'])]
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
}