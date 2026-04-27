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

    private const DIAG_TYPES_BY_PANNE = [
        'Mécanique' => [
            ['id' => 'vibratoire',        'icon' => '⚙️',  'name' => 'Analyse vibratoire',    'desc' => 'Vibrations anormales'],
            ['id' => 'inspection_pieces', 'icon' => '🔩',  'name' => 'Inspection des pièces',  'desc' => 'Usure, fissures, jeu'],
            ['id' => 'thermique',         'icon' => '🌡️', 'name' => 'Contrôle thermique',     'desc' => 'Surchauffe composants'],
            ['id' => 'performances',      'icon' => '📊',  'name' => 'Test de performances',   'desc' => 'Puissance et rendement'],
        ],
        'Électricité' => [
            ['id' => 'circuits',    'icon' => '⚡',  'name' => 'Test des circuits',    'desc' => 'Continuité, isolement'],
            ['id' => 'batterie',    'icon' => '🔋',  'name' => 'Contrôle batterie',    'desc' => 'Tension et capacité'],
            ['id' => 'capteurs',    'icon' => '💡',  'name' => 'Diagnostic capteurs',  'desc' => 'Capteurs et sondes'],
            ['id' => 'connexions',  'icon' => '🔌',  'name' => 'Connexions',           'desc' => 'Connecteurs et câblage'],
        ],
        'Hydraulique' => [
            ['id' => 'analyse_fluide',   'icon' => '💧', 'name' => 'Analyse du fluide',    'desc' => 'Viscosité, contamination'],
            ['id' => 'test_pression',    'icon' => '📈', 'name' => 'Test de pression',     'desc' => 'Circuit hydraulique'],
            ['id' => 'detection_fuites', 'icon' => '🔍', 'name' => 'Détection de fuites',  'desc' => 'Joints, tuyaux, raccords'],
            ['id' => 'pompe',            'icon' => '⚙️', 'name' => 'Pompe hydraulique',    'desc' => 'Débit et rendement'],
        ],
        'Moteur' => [
            ['id' => 'compression',    'icon' => '🔧',  'name' => 'Compression moteur',   'desc' => 'Cylindres et pistons'],
            ['id' => 'refroidissement','icon' => '🌡️', 'name' => 'Température refroid.', 'desc' => 'Circuit de refroid.'],
            ['id' => 'injection',      'icon' => '⛽',  'name' => 'Injection carburant',  'desc' => 'Injecteurs et pompe'],
            ['id' => 'echappement',    'icon' => '💨',  'name' => "Gaz d'échappement",    'desc' => 'Émissions anormales'],
        ],
        'Vidange & filtres' => [
            ['id' => 'analyse_huile',      'icon' => '🛢️', 'name' => 'Analyse huile moteur', 'desc' => 'Viscosité et usure'],
            ['id' => 'filtre_air',         'icon' => '🌬️', 'name' => 'Filtre à air',          'desc' => 'Colmatage, efficacité'],
            ['id' => 'filtre_carburant',   'icon' => '⛽',  'name' => 'Filtre carburant',      'desc' => 'Restrictions'],
            ['id' => 'filtre_hydraulique', 'icon' => '💧',  'name' => 'Filtre hydraulique',    'desc' => 'Particules, contamination'],
        ],
        'Transmission' => [
            ['id' => 'boite_vitesses',      'icon' => '⚙️', 'name' => 'Boîte de vitesses',        'desc' => 'Engrenages, synchron.'],
            ['id' => 'arbres_transmission', 'icon' => '🔗', 'name' => 'Arbres de transmission',    'desc' => 'Joints homo., usure'],
            ['id' => 'embrayage',           'icon' => '🔧', 'name' => 'Embrayage',                 'desc' => 'Usure garnitures'],
            ['id' => 'test_glissement',     'icon' => '📊', 'name' => 'Test de glissement',        'desc' => 'Efficacité transmission'],
        ],
        'Pneumatique' => [
            ['id' => 'pression',            'icon' => '🔵', 'name' => 'Pression pneus',      'desc' => 'Pression et uniformité'],
            ['id' => 'inspection_visuelle', 'icon' => '👁️','name' => 'Inspection visuelle',  'desc' => 'Usure, coupures'],
            ['id' => 'equilibrage',         'icon' => '⚖️', 'name' => 'Équilibrage',          'desc' => 'Vibrations, équilibre'],
            ['id' => 'geometrie',           'icon' => '📐', 'name' => 'Géométrie',            'desc' => 'Parallélisme'],
        ],
        'Logicielle' => [
            ['id' => 'diagnostic_ecu', 'icon' => '💻', 'name' => 'Diagnostic ECU',          'desc' => 'Codes défauts'],
            ['id' => 'capteurs_elec',  'icon' => '📡', 'name' => 'Capteurs électroniques',  'desc' => 'Calibration, signaux'],
            ['id' => 'firmware',       'icon' => '🔄', 'name' => 'Mise à jour firmware',    'desc' => 'Version logicielle'],
            ['id' => 'donnees_fonct',  'icon' => '📊', 'name' => 'Données de fonct.',       'desc' => 'Historique, anomalies'],
        ],
        'Révision générale' => [
            ['id' => 'inspection_complete', 'icon' => '📋', 'name' => 'Inspection complète', 'desc' => 'Tous les systèmes'],
            ['id' => 'mesures_usure',       'icon' => '📏', 'name' => "Mesures d'usure",     'desc' => 'Jeux et tolérances'],
            ['id' => 'serrage',             'icon' => '🔧', 'name' => 'Serrage et couples',  'desc' => 'Boulonnerie'],
            ['id' => 'suivi_preventif',     'icon' => '📅', 'name' => 'Suivi préventif',     'desc' => 'Planning entretien'],
        ],
    ];

    private const DEFAULT_DIAG_TYPES = [
        ['id' => 'general',     'icon' => '🔍', 'name' => 'Diagnostic général',    'desc' => 'Inspection complète'],
        ['id' => 'symptomes',   'icon' => '📊', 'name' => 'Analyse des symptômes', 'desc' => 'Identification causes'],
        ['id' => 'fonctionnel', 'icon' => '🛠️','name' => 'Test fonctionnel',       'desc' => 'Vérification fonct.'],
        ['id' => 'bilan',       'icon' => '📝', 'name' => 'Bilan technique',        'desc' => "Rapport d'état"],
    ];

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

    // ── API : Types de diagnostics ──
    #[Route('/api/diagnostics/types', name: 'agri_maintenances_api_diagnostics_types', methods: ['GET'])]
    public function getDiagnosticsTypes(Request $request): JsonResponse
    {
        $typePanne = $request->query->get('typePanne');
        $types = ($typePanne && isset(self::DIAG_TYPES_BY_PANNE[$typePanne]))
            ? self::DIAG_TYPES_BY_PANNE[$typePanne]
            : self::DEFAULT_DIAG_TYPES;

        return $this->json([
            'success'             => true,
            'types'               => $types,
            'availableCategories' => array_keys(self::DIAG_TYPES_BY_PANNE),
        ]);
    }

    // ── API : Prompt libre IA ──
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

        $context = sprintf(
            'Machine: %s, type de panne: %s, priorité: %s, kilométrage: %s km, statut: %s. Description: %s',
            $maintenance->getNom() ?? 'Machine',
            $maintenance->getTypePanne() ?? '—',
            $maintenance->getPriorite() ?? 'moyenne',
            $maintenance->getKilometrage() ?? 0,
            $maintenance->getStatut() ?? 'planifie',
            $maintenance->getDescription() ?? ''
        );

        // Réponse locale enrichie (pas besoin d'API externe)
        $response = $this->buildLocalPromptResponse($prompt, $maintenance);

        return $this->json([
            'success'  => true,
            'response' => $response,
            'context'  => $context,
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
            return "Vérifications recommandées pour une panne {$type} sur {$nom} :\n"
                . "1. Inspection visuelle générale (fuites, câbles, fixations)\n"
                . "2. Test de démarrage et fonctionnement à vide\n"
                . "3. Relevé des températures et pressions\n"
                . "4. Contrôle des niveaux (huile, liquide refroidissement, hydraulique)\n"
                . "5. Diagnostic électronique si disponible\n"
                . "6. Test de charge progressive\n"
                . ($prio === 'urgente' ? "\n⚠️ Intervention urgente : ne pas utiliser la machine avant réparation." : '');
        }

        if (str_contains($promptLc, 'pièce') || str_contains($promptLc, 'remplacer')) {
            return "Pièces à contrôler/remplacer pour une panne {$type} :\n"
                . "• Filtres (air, huile, carburant, hydraulique)\n"
                . "• Courroies et chaînes de transmission\n"
                . "• Joints et flexibles\n"
                . ($km > 5000  ? "• Huile moteur et filtre (kilométrage > 5 000 km)\n" : '')
                . ($km > 10000 ? "• Roulements et paliers (kilométrage > 10 000 km)\n" : '')
                . ($km > 15000 ? "• Pièces d'usure moteur (kilométrage > 15 000 km)\n" : '')
                . "• Bougies ou injecteurs si perte de puissance\n"
                . "• Batterie si démarrage difficile";
        }

        if (str_contains($promptLc, 'sécurité') || str_contains($promptLc, 'précaution')) {
            return "Précautions de sécurité obligatoires :\n"
                . "• EPI complets : gants, lunettes, chaussures de sécurité\n"
                . "• Couper le moteur et attendre le refroidissement complet\n"
                . "• Déconnecter la batterie avant intervention électrique\n"
                . "• Utiliser des chandelles (jamais un simple cric)\n"
                . "• Extincteur à proximité immédiate\n"
                . "• Zone bien ventilée — risque CO et vapeurs\n"
                . "• Jamais seul sur intervention lourde\n"
                . "• Consigner la machine (LOTO) si plusieurs techniciens";
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
            return "Estimation d'intervention — panne {$type} :\n"
                . "• Durée estimée : {$duree}\n"
                . "• Outils nécessaires :\n"
                . "  - Clés et douilles (jeu complet)\n"
                . "  - Multimètre et testeur de circuits\n"
                . "  - Valise de diagnostic OBD si disponible\n"
                . "  - Manomètre de compression\n"
                . "  - Thermomètre infrarouge\n"
                . "  - Matériel de vidange et bacs de récupération\n"
                . "• Technicien recommandé : mécanicien agricole spécialisé";
        }

        if (str_contains($promptLc, 'coût') || str_contains($promptLc, 'estimation') || str_contains($promptLc, 'prix')) {
            $coutPieces = match($type) {
                'Moteur'            => '300 – 800 DT',
                'Transmission'      => '250 – 600 DT',
                'Hydraulique'       => '150 – 400 DT',
                'Électricité'       => '80  – 250 DT',
                'Vidange & filtres' => '60  – 150 DT',
                default             => '100 – 400 DT',
            };
            return "Estimation des coûts — panne {$type} sur {$nom} :\n"
                . "• Pièces détachées : {$coutPieces}\n"
                . "• Main d'œuvre : 50 – 120 DT/heure\n"
                . "• Frais de déplacement technicien : 30 – 80 DT\n"
                . ($km > 10000 ? "• Révision générale conseillée : +200 à 500 DT\n" : '')
                . "• Total estimé : voir devis technicien\n"
                . "💡 Conseil : comparer 2-3 devis et vérifier la garantie des pièces.";
        }

        // Réponse générique
        return "Analyse de la panne {$type} sur {$nom} (priorité: {$prio}, {$km} km) :\n\n"
            . "1. Effectuer un diagnostic complet avant toute intervention\n"
            . "2. Identifier précisément la source du problème\n"
            . "3. Préparer les pièces et outils nécessaires\n"
            . "4. Intervenir selon les procédures constructeur\n"
            . "5. Tester le bon fonctionnement après intervention\n"
            . "6. Documenter l'intervention dans le carnet de maintenance\n\n"
            . ($prio === 'urgente' ? "⚠️ Priorité urgente : intervenir immédiatement." : "Planifier l'intervention dans les meilleurs délais.");
    }

    // ── API : Calendrier d'interventions IA ──
    #[Route('/api/calendar/{id}', name: 'agri_maintenances_api_calendar', methods: ['GET'])]
    public function generateCalendar(int $id, MaintenanceRepository $repo): JsonResponse
    {
        $maintenance = $repo->findOneWithMaterielName($id);
        if (!$maintenance) {
            return $this->json(['error' => 'Maintenance non trouvée'], 404);
        }

        $prio  = $maintenance->getPriorite() ?? 'moyenne';
        $type  = $maintenance->getTypePanne() ?? 'Général';
        $km    = $maintenance->getKilometrage() ?? 0;
        $today = new \DateTime();

        // Intervalles selon priorité (jours)
        $intervals = ['urgente' => 7, 'haute' => 21, 'moyenne' => 45, 'faible' => 90];
        $base = $intervals[$prio] ?? 45;

        $suggestions = [];

        // Intervention 1 : principale selon type
        $d1 = (clone $today)->modify("+{$base} days");
        $suggestions[] = [
            'date'        => $d1->format('d/m/Y'),
            'intervention' => $this->getMainIntervention($type),
            'priorite'    => $prio,
            'duree'       => $this->getEstimatedDuration($type),
            'technicien'  => $this->getRecommendedTechnician($type),
            'color'       => $prio === 'urgente' ? 'red' : ($prio === 'haute' ? 'amber' : 'green'),
        ];

        // Intervention 2 : vidange si km élevé
        if ($km >= 5000) {
            $d2 = (clone $today)->modify('+' . ($base + 7) . ' days');
            $suggestions[] = [
                'date'        => $d2->format('d/m/Y'),
                'intervention' => 'Vidange moteur + changement filtres',
                'priorite'    => $km >= 8000 ? 'haute' : 'normale',
                'duree'       => '1h30',
                'technicien'  => 'Mécanicien agricole',
                'color'       => $km >= 8000 ? 'amber' : 'green',
            ];
        }

        // Intervention 3 : contrôle hebdomadaire
        $d3 = (clone $today)->modify('+7 days');
        $suggestions[] = [
            'date'        => $d3->format('d/m/Y'),
            'intervention' => 'Contrôle hebdomadaire (niveaux, pression pneus)',
            'priorite'    => 'normale',
            'duree'       => '30 min',
            'technicien'  => 'Opérateur',
            'color'       => 'green',
        ];

        // Intervention 4 : révision générale si km très élevé
        if ($km >= 10000) {
            $d4 = (clone $today)->modify('+' . ($base + 30) . ' days');
            $suggestions[] = [
                'date'        => $d4->format('d/m/Y'),
                'intervention' => 'Révision générale complète',
                'priorite'    => 'haute',
                'duree'       => '6 à 8 heures',
                'technicien'  => 'Mécanicien spécialisé',
                'color'       => 'amber',
            ];
        } else {
            $d4 = (clone $today)->modify('+' . ($base + 20) . ' days');
            $suggestions[] = [
                'date'        => $d4->format('d/m/Y'),
                'intervention' => 'Inspection préventive mensuelle',
                'priorite'    => 'normale',
                'duree'       => '1 heure',
                'technicien'  => 'Technicien de maintenance',
                'color'       => 'green',
            ];
        }

        // Trier par date
        usort($suggestions, fn($a, $b) => \DateTime::createFromFormat('d/m/Y', $a['date'])
            <=> \DateTime::createFromFormat('d/m/Y', $b['date']));

        return $this->json([
            'success'     => true,
            'suggestions' => $suggestions,
            'machine'     => $maintenance->getNom() ?? 'Machine',
            'nextMainDate'=> $suggestions[0]['date'],
        ]);
    }

    private function getMainIntervention(string $type): string
    {
        return match($type) {
            'Moteur'            => 'Diagnostic complet moteur + contrôle refroidissement',
            'Électricité'       => 'Diagnostic circuit électrique + test batterie',
            'Hydraulique'       => 'Contrôle circuit hydraulique + détection fuites',
            'Transmission'      => 'Inspection embrayage + boîte de vitesses',
            'Pneumatique'       => 'Contrôle pression + usure pneus + géométrie',
            'Vidange & filtres' => 'Vidange complète + remplacement tous filtres',
            'Mécanique'         => 'Inspection pièces mécaniques + test performances',
            'Logicielle'        => 'Diagnostic ECU + mise à jour firmware',
            'Révision générale' => 'Révision complète tous systèmes',
            default             => 'Inspection générale + entretien préventif',
        };
    }

    private function getEstimatedDuration(string $type): string
    {
        return match($type) {
            'Moteur'            => '4 à 8 heures',
            'Transmission'      => '3 à 6 heures',
            'Hydraulique'       => '2 à 4 heures',
            'Électricité'       => '1 à 3 heures',
            'Vidange & filtres' => '1 à 2 heures',
            'Logicielle'        => '1 à 2 heures',
            default             => '2 à 4 heures',
        };
    }

    private function getRecommendedTechnician(string $type): string
    {
        return match($type) {
            'Électricité', 'Logicielle' => 'Électronicien agricole',
            'Hydraulique'               => 'Technicien hydraulique',
            'Moteur', 'Transmission'    => 'Mécanicien moteur spécialisé',
            default                     => 'Mécanicien agricole',
        };
    }

    // ── API : Prévision durée de vie IA ──
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

        // Calcul vie restante
        $vieRestante = max(0, min(100, (int) round((($maxKm - $km) / $maxKm) * 100)));

        // Pénalité selon priorité
        $penalite = match($prio) {
            'urgente' => 25,
            'haute'   => 15,
            'moyenne' => 8,
            default   => 3,
        };
        $vieRestante = max(0, $vieRestante - $penalite);

        // Années estimées (consommation moyenne 3 000 km/an)
        $anneesEstimees = round(max(0, ($maxKm - $km)) / 3000, 1);

        // Risque de panne
        $risque = match(true) {
            $prio === 'urgente' || $km > 15000 => 'Élevé',
            $prio === 'haute'   || $km > 10000 => 'Moyen',
            default                            => 'Faible',
        };

        // Usure par composant (calculée dynamiquement)
        $composants = [
            ['nom' => 'Moteur',       'usure' => min(100, (int) round($km / 200))],
            ['nom' => 'Transmission', 'usure' => min(100, (int) round($km / 300))],
            ['nom' => 'Hydraulique',  'usure' => min(100, (int) round($km / 400))],
            ['nom' => 'Électricité',  'usure' => min(100, (int) round($km / 600))],
            ['nom' => 'Pneumatique',  'usure' => min(100, (int) round($km / 250))],
        ];

        // Bonus/malus selon type de panne
        foreach ($composants as &$comp) {
            if (strtolower($comp['nom']) === strtolower(explode(' ', $type)[0])) {
                $comp['usure'] = min(100, $comp['usure'] + ($prio === 'urgente' ? 30 : ($prio === 'haute' ? 20 : 10)));
            }
        }
        unset($comp);

        // Recommandations
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

    /**
     * @param array<int, array{nom:string, usure:int}> $composants
     * @return array<int, string>
     */
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

        // Composant le plus usé
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

    /**
     * @param array<string, mixed> $machineData
     * @return array<int, array{name:string, priority:string, reason?:string}>
     */
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

    /**
     * @param array<string, mixed> $d
     * @param array<int, string> $options
     * @param array<int, array{name:string, priority:string, reason?:string}> $interventions
     */
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

    // ── API : Diagnostic (JSON) ──
    #[Route('/api/diagnostics/generate', name: 'agri_maintenances_api_diagnostics_generate', methods: ['POST'])]
    public function generateDiagnostics(Request $request, MaintenanceRepository $repo): JsonResponse
    {
        $data          = json_decode($request->getContent(), true);
        $maintenanceId = $data['maintenanceId'] ?? null;
        $diagTypes     = $data['diagnosticTypes'] ?? [];

        if (!$maintenanceId) return $this->json(['error' => 'ID manquant'], 400);

        $maintenance = $repo->findOneWithMaterielName($maintenanceId);
        if (!$maintenance) return $this->json(['error' => 'Maintenance non trouvée'], 404);

        return $this->json([
            'success'     => true,
            'diagnostics' => $this->generateDiagnosticReport($maintenance, $diagTypes),
        ]);
    }

    /**
     * @param array<int, string> $types
     */
    private function generateDiagnosticReport(Maintenance $m, array $types): string
    {
        $km  = $m->getKilometrage() ?? 0;
        $prio = $m->getPriorite() ?? 'moyenne';

        $report  = "# 🔍 RAPPORT DE DIAGNOSTIC\n\n## 📋 Informations\n";
        $report .= "- **Machine** : " . ($m->getNom() ?? 'Machine') . "\n";
        $report .= "- **Type** : " . ($m->getTypePanne() ?? '—') . "\n";
        $report .= "- **Priorité** : " . ucfirst($prio) . "\n";
        $report .= "- **Kilométrage** : " . ($km ? "$km km" : 'Non renseigné') . "\n\n";
        $report .= "## 🔬 Diagnostic détaillé\n\n";

        foreach ($types as $typeId) $report .= $this->getDiagnosticForType($typeId, $m->getTypePanne() ?? '', $km);

        $criticite = match($prio) { 'urgente' => 9, 'haute' => 7, 'moyenne' => 5, default => 3 };
        if ($km > 15000) $criticite = min(10, $criticite + 2);

        $report .= "## 🎯 CRITICITÉ : **{$criticite}/10**\n\n## 📅 PROCHAINES MAINTENANCES\n";
        if ($km > 0) {
            $report .= "- Vidange : dans " . (ceil($km / 5000) * 5000 - $km) . " km\n";
            $report .= "- Révision : dans " . (ceil($km / 10000) * 10000 - $km) . " km\n";
        } else {
            $report .= "- Vidange : tous les 5 000 km\n- Révision : tous les 10 000 km\n";
        }

        return $report;
    }

    private function getDiagnosticForType(string $typeId, string $typePanne, int $km): string
    {
        $map = [
            'vibratoire'       => "### ⚙️ Analyse vibratoire\n- Valeurs normales : < 5 mm/s — anomalie : > 7 mm/s\n- Action : équilibrage ou remplacement\n\n",
            'inspection_pieces'=> "### 🔩 Inspection des pièces\n- Jeu normal : < 0.5 mm\n- Action : remplacement pièces usées\n\n",
            'thermique'        => "### 🌡️ Contrôle thermique\n- Temp. normale : 70–90°C — anomalie : > 100°C\n- Action : vérifier circuit refroidissement\n\n",
            'circuits'         => "### ⚡ Test circuits électriques\n- Vérification continuité et isolation\n- Action : réparation ou remplacement câblage\n\n",
            'batterie'         => "### 🔋 Contrôle batterie\n- Tension normale : 12.6 V — charge : 14.2–14.7 V\n- Action : recharge ou remplacement\n\n",
            'analyse_fluide'   => "### 💧 Analyse fluide hydraulique\n- Contrôle viscosité et contamination\n- Action : vidange et remplacement\n\n",
            'compression'      => "### 🔧 Compression moteur\n- Valeur normale : 10–12 bars — écart max : 1 bar\n- Action : réfection moteur si compression basse\n\n",
            'refroidissement'  => "### 🌡️ Circuit de refroidissement\n- Niveau liquide, état durites, thermostat\n- Action : purge et remplacement si fuite\n\n",
            'injection'        => "### ⛽ Injection carburant\n- Pression d'injection, état injecteurs\n- Action : nettoyage ou remplacement injecteurs\n\n",
            'general'          => "### 🔍 Diagnostic général\n- Inspection visuelle + test fonctionnel\n- Action corrective selon anomalies\n\n",
        ];

        return $map[$typeId] ?? "### 📊 Diagnostic {$typeId}\n- Vérification paramètres de fonctionnement\n- Identification anomalies + actions correctives\n\n";
    }

    // ── API : Page HTML Schedule ──
    #[Route('/api/schedule/{id}', name: 'agri_maintenances_api_schedule', methods: ['GET'])]
    public function apiSchedule(int $id, MaintenanceRepository $repo): Response
    {
        $maintenance = $repo->findOneWithMaterielName($id);
        if (!$maintenance) throw $this->createNotFoundException('Maintenance non trouvée.');
        return $this->render('maintenances/api_schedule_html.html.twig', ['maintenance' => $maintenance]);
    }

    // ── API : Page HTML Diagnostics ──
    #[Route('/api/diagnostics/{id}', name: 'agri_maintenances_api_diagnostics', methods: ['GET'])]
    public function apiDiagnostics(int $id, MaintenanceRepository $repo): Response
    {
        $maintenance = $repo->findOneWithMaterielName($id);
        if (!$maintenance) throw $this->createNotFoundException('Maintenance non trouvée.');
        return $this->render('maintenances/api_diagnostics_html.html.twig', ['maintenance' => $maintenance]);
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
                    $m->getIdM() ? '#' . $m->getIdM()->getId() : '', $m->getNom() ?? ''], ';');
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