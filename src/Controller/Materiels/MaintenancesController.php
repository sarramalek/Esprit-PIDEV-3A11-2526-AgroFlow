<?php

namespace App\Controller\Materiels;

use App\Entity\Materiels\Maintenance;
use App\Form\Materiels\MaintenanceType;
use App\Repository\Materiels\MaintenanceRepository;
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
    // ── Types de diagnostics disponibles ──
    private const DIAG_TYPES_BY_PANNE = [
        'Mécanique' => [
            ['id' => 'vibratoire', 'icon' => '⚙️', 'name' => 'Analyse vibratoire', 'desc' => 'Vibrations anormales'],
            ['id' => 'inspection_pieces', 'icon' => '🔩', 'name' => 'Inspection des pièces', 'desc' => 'Usure, fissures, jeu'],
            ['id' => 'thermique', 'icon' => '🌡️', 'name' => 'Contrôle thermique', 'desc' => 'Surchauffe composants'],
            ['id' => 'performances', 'icon' => '📊', 'name' => 'Test de performances', 'desc' => 'Puissance et rendement']
        ],
        'Électricité' => [
            ['id' => 'circuits', 'icon' => '⚡', 'name' => 'Test des circuits', 'desc' => 'Continuité, isolement'],
            ['id' => 'batterie', 'icon' => '🔋', 'name' => 'Contrôle batterie', 'desc' => 'Tension et capacité'],
            ['id' => 'capteurs', 'icon' => '💡', 'name' => 'Diagnostic capteurs', 'desc' => 'Capteurs et sondes'],
            ['id' => 'connexions', 'icon' => '🔌', 'name' => 'Connexions', 'desc' => 'Connecteurs et câblage']
        ],
        'Hydraulique' => [
            ['id' => 'analyse_fluide', 'icon' => '💧', 'name' => 'Analyse du fluide', 'desc' => 'Viscosité, contamination'],
            ['id' => 'test_pression', 'icon' => '📈', 'name' => 'Test de pression', 'desc' => 'Circuit hydraulique'],
            ['id' => 'detection_fuites', 'icon' => '🔍', 'name' => 'Détection de fuites', 'desc' => 'Joints, tuyaux, raccords'],
            ['id' => 'pompe', 'icon' => '⚙️', 'name' => 'Pompe hydraulique', 'desc' => 'Débit et rendement']
        ],
        'Moteur' => [
            ['id' => 'compression', 'icon' => '🔧', 'name' => 'Compression moteur', 'desc' => 'Cylindres et pistons'],
            ['id' => 'refroidissement', 'icon' => '🌡️', 'name' => 'Température refroid.', 'desc' => 'Circuit de refroid.'],
            ['id' => 'injection', 'icon' => '⛽', 'name' => 'Injection carburant', 'desc' => 'Injecteurs et pompe'],
            ['id' => 'echappement', 'icon' => '💨', 'name' => "Gaz d'échappement", 'desc' => 'Émissions anormales']
        ],
        'Vidange & filtres' => [
            ['id' => 'analyse_huile', 'icon' => '🛢️', 'name' => 'Analyse huile moteur', 'desc' => 'Viscosité et usure'],
            ['id' => 'filtre_air', 'icon' => '🌬️', 'name' => 'Filtre à air', 'desc' => 'Colmatage, efficacité'],
            ['id' => 'filtre_carburant', 'icon' => '⛽', 'name' => 'Filtre carburant', 'desc' => 'Restrictions'],
            ['id' => 'filtre_hydraulique', 'icon' => '💧', 'name' => 'Filtre hydraulique', 'desc' => 'Particules, contamination']
        ],
        'Transmission' => [
            ['id' => 'boite_vitesses', 'icon' => '⚙️', 'name' => 'Boîte de vitesses', 'desc' => 'Engrenages, synchron.'],
            ['id' => 'arbres_transmission', 'icon' => '🔗', 'name' => 'Arbres de transmission', 'desc' => 'Joints homo., usure'],
            ['id' => 'embrayage', 'icon' => '🔧', 'name' => 'Embrayage', 'desc' => 'Usure garnitures'],
            ['id' => 'test_glissement', 'icon' => '📊', 'name' => 'Test de glissement', 'desc' => 'Efficacité transmission']
        ],
        'Pneumatique' => [
            ['id' => 'pression', 'icon' => '🔵', 'name' => 'Pression pneus', 'desc' => 'Pression et uniformité'],
            ['id' => 'inspection_visuelle', 'icon' => '👁️', 'name' => 'Inspection visuelle', 'desc' => 'Usure, coupures'],
            ['id' => 'equilibrage', 'icon' => '⚖️', 'name' => 'Équilibrage', 'desc' => 'Vibrations, équilibre'],
            ['id' => 'geometrie', 'icon' => '📐', 'name' => 'Géométrie', 'desc' => 'Parallélisme']
        ],
        'Logicielle' => [
            ['id' => 'diagnostic_ecu', 'icon' => '💻', 'name' => 'Diagnostic ECU', 'desc' => 'Codes défauts'],
            ['id' => 'capteurs_elec', 'icon' => '📡', 'name' => 'Capteurs électroniques', 'desc' => 'Calibration, signaux'],
            ['id' => 'firmware', 'icon' => '🔄', 'name' => 'Mise à jour firmware', 'desc' => 'Version logicielle'],
            ['id' => 'donnees_fonct', 'icon' => '📊', 'name' => 'Données de fonct.', 'desc' => 'Historique, anomalies']
        ],
        'Révision générale' => [
            ['id' => 'inspection_complete', 'icon' => '📋', 'name' => 'Inspection complète', 'desc' => 'Tous les systèmes'],
            ['id' => 'mesures_usure', 'icon' => '📏', 'name' => "Mesures d'usure", 'desc' => 'Jeux et tolérances'],
            ['id' => 'serrage', 'icon' => '🔧', 'name' => 'Serrage et couples', 'desc' => 'Boulonnerie'],
            ['id' => 'suivi_preventif', 'icon' => '📅', 'name' => 'Suivi préventif', 'desc' => 'Planning entretien']
        ],
    ];

    private const DEFAULT_DIAG_TYPES = [
        ['id' => 'general', 'icon' => '🔍', 'name' => 'Diagnostic général', 'desc' => 'Inspection complète'],
        ['id' => 'symptomes', 'icon' => '📊', 'name' => 'Analyse des symptômes', 'desc' => 'Identification causes'],
        ['id' => 'fonctionnel', 'icon' => '🛠️', 'name' => 'Test fonctionnel', 'desc' => 'Vérification fonct.'],
        ['id' => 'bilan', 'icon' => '📝', 'name' => 'Bilan technique', 'desc' => "Rapport d'état"]
    ];

    #[Route('', name: 'agri_maintenances_index', methods: ['GET'])]
    public function index(Request $request, MaintenanceRepository $repo): Response
    {
        $search = $request->query->get('search', '');
        $type   = $request->query->get('type', '');
        $sort   = $request->query->get('sort', 'dateMain');
        $dir    = $request->query->get('dir', 'DESC');

        $maintenances   = $repo->searchWithMaterielName($search, $type, $sort, $dir);
        $countByType    = $repo->countByTypePanne();
        $coutByMonth    = $repo->getCoutByMonth();
        $totalCout      = $repo->getTotalCout();
        $countByStatut  = $repo->countByStatut();
        $countByPriorite = $repo->countByPriorite();

        return $this->render('maintenances/index.html.twig', [
            'maintenances'    => $maintenances,
            'search'          => $search,
            'type'            => $type,
            'sort'            => $sort,
            'dir'             => $dir,
            'countByType'     => $countByType,
            'coutByMonth'     => $coutByMonth,
            'totalCout'       => $totalCout,
            'countByStatut'   => $countByStatut,
            'countByPriorite' => $countByPriorite,
        ]);
    }

    // ── API : Generate content via Google Gemini DIRECT ──
    #[Route('/api/gemini/generate', name: 'agri_maintenances_api_gemini_generate', methods: ['POST'])]
    public function generateWithGemini(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $prompt = $data['prompt'] ?? '';
        
        if (empty($prompt)) {
            return $this->json(['error' => 'Prompt manquant'], 400);
        }

        $apiKey = $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY');
        $model = $_ENV['GEMINI_MODEL'] ?? 'gemini-2.0-flash-exp';
        $apiUrl = $_ENV['GEMINI_API_URL'] ?? 'https://generativelanguage.googleapis.com/v1beta/models';
        $temperature = $_ENV['GEMINI_TEMPERATURE'] ?? 0.7;
        $maxTokens = $_ENV['GEMINI_MAX_TOKENS'] ?? 4000;
        
        if (empty($apiKey)) {
            return $this->json(['error' => 'Clé API Gemini non configurée. Ajoutez GEMINI_API_KEY dans .env.local'], 500);
        }

        $url = "{$apiUrl}/{$model}:generateContent?key={$apiKey}";

        $requestData = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => (float)$temperature,
                'maxOutputTokens' => (int)$maxTokens,
                'topP' => 0.9,
                'topK' => 40
            ],
            'safetySettings' => [
                ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE']
            ]
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($requestData)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return $this->json(['error' => 'Erreur CURL: ' . $error], 500);
        }
        
        curl_close($ch);

        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMsg = $errorData['error']['message'] ?? $response;
            return $this->json(['error' => 'Erreur Gemini API: ' . $errorMsg], $httpCode);
        }

        $result = json_decode($response, true);
        $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

        if (empty($text)) {
            return $this->json(['error' => 'Réponse vide de l\'API Gemini'], 500);
        }

        return $this->json(['text' => $text]);
    }

    // ── API : GET /diagnostics/types - Récupérer les types de diagnostics disponibles ──
    #[Route('/api/diagnostics/types', name: 'agri_maintenances_api_diagnostics_types', methods: ['GET'])]
    public function getDiagnosticsTypes(Request $request): JsonResponse
    {
        $typePanne = $request->query->get('typePanne');
        
        if ($typePanne && isset(self::DIAG_TYPES_BY_PANNE[$typePanne])) {
            $types = self::DIAG_TYPES_BY_PANNE[$typePanne];
        } else {
            $types = self::DEFAULT_DIAG_TYPES;
        }
        
        return $this->json([
            'success' => true,
            'types' => $types,
            'availableCategories' => array_keys(self::DIAG_TYPES_BY_PANNE)
        ]);
    }

    // ── API : POST /schedules/generate - Générer un plan de maintenance intelligent ──
    #[Route('/api/schedules/generate', name: 'agri_maintenances_api_schedule_generate', methods: ['POST'])]
    public function generateSchedule(Request $request, MaintenanceRepository $repo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        // Récupérer la maintenance si un ID est fourni
        $maintenance = null;
        if (isset($data['maintenanceId'])) {
            $maintenance = $repo->findOneWithMaterielName($data['maintenanceId']);
            if (!$maintenance) {
                return $this->json(['error' => 'Maintenance non trouvée'], 404);
            }
        }
        
        // Options du plan sélectionnées
        $options = $data['options'] ?? ['intervention', 'prevention', 'pieces', 'optimisation', 'securite', 'controle'];
        
        // Données de la machine
        $machineData = [
            'type' => $maintenance ? $maintenance->getTypePanne() : ($data['typePanne'] ?? 'Non spécifié'),
            'priorite' => $maintenance ? $maintenance->getPriorite() : ($data['priorite'] ?? 'moyenne'),
            'statut' => $maintenance ? $maintenance->getStatut() : ($data['statut'] ?? 'planifie'),
            'kilometrage' => $maintenance ? $maintenance->getKilometrage() : ($data['kilometrage'] ?? null),
            'nom' => $maintenance ? ($maintenance->getNom() ?? 'Machine non spécifiée') : ($data['nomMachine'] ?? 'Machine non spécifiée'),
            'description' => $maintenance ? $maintenance->getDescription() : ($data['description'] ?? ''),
            'recommandation' => $maintenance ? $maintenance->getRecommandation() : ($data['recommandation'] ?? ''),
            'cout' => $maintenance ? $maintenance->getCout() : ($data['cout'] ?? 0),
            'dateMain' => $maintenance && $maintenance->getDateMain() ? $maintenance->getDateMain()->format('Y-m-d') : ($data['dateMain'] ?? null)
        ];
        
        // Calculer les interventions recommandées en fonction des données
        $interventions = $this->calculateRecommendedInterventions($machineData);
        
        // Construire le prompt pour Gemini
        $prompt = $this->buildSchedulePrompt($machineData, $options, $interventions);
        
        // Appeler Gemini
        $apiKey = $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY');
        if (empty($apiKey)) {
            // Fallback : retourner un plan calculé localement
            return $this->json([
                'success' => true,
                'plan' => $this->generateFallbackSchedule($machineData, $options, $interventions),
                'generatedBy' => 'local_algorithm'
            ]);
        }
        
        $model = $_ENV['GEMINI_MODEL'] ?? 'gemini-2.0-flash-exp';
        $apiUrl = $_ENV['GEMINI_API_URL'] ?? 'https://generativelanguage.googleapis.com/v1beta/models';
        $url = "{$apiUrl}/{$model}:generateContent?key={$apiKey}";
        
        $requestData = [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => ['temperature' => 0.7, 'maxOutputTokens' => 4000]
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($requestData)
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            // Fallback local
            return $this->json([
                'success' => true,
                'plan' => $this->generateFallbackSchedule($machineData, $options, $interventions),
                'generatedBy' => 'local_algorithm'
            ]);
        }
        
        $result = json_decode($response, true);
        $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        if (empty($text)) {
            return $this->json([
                'success' => true,
                'plan' => $this->generateFallbackSchedule($machineData, $options, $interventions),
                'generatedBy' => 'local_algorithm'
            ]);
        }
        
        return $this->json([
            'success' => true,
            'plan' => $text,
            'generatedBy' => 'gemini_ai'
        ]);
    }
    
    /**
     * Calcule les interventions recommandées en fonction des données de la machine
     */
    private function calculateRecommendedInterventions(array $machineData): array
    {
        $interventions = [];
        $kilometrage = $machineData['kilometrage'] ?? 0;
        $typePanne = $machineData['type'] ?? '';
        $priorite = $machineData['priorite'] ?? 'moyenne';
        
        // Interventions basées sur le kilométrage
        if ($kilometrage && $kilometrage > 0) {
            if ($kilometrage >= 5000) {
                $interventions[] = [
                    'name' => '🛢️ Vidange moteur',
                    'reason' => 'Kilométrage élevé (>5000 km)',
                    'priority' => $kilometrage >= 8000 ? 'haute' : 'moyenne'
                ];
            }
            if ($kilometrage >= 10000) {
                $interventions[] = [
                    'name' => '🔧 Révision générale',
                    'reason' => 'Kilométrage très élevé (>10000 km)',
                    'priority' => 'haute'
                ];
            }
            if ($kilometrage >= 15000) {
                $interventions[] = [
                    'name' => '⚙️ Remplacement transmission',
                    'reason' => 'Kilométrage critique (>15000 km)',
                    'priority' => 'urgente'
                ];
            }
        }
        
        // Interventions basées sur le type de panne
        $typeInterventions = [
            'Mécanique' => [
                ['name' => '🔧 Contrôle des pièces mécaniques', 'priority' => 'haute'],
                ['name' => '📊 Test de performance moteur', 'priority' => 'moyenne']
            ],
            'Électricité' => [
                ['name' => '⚡ Diagnostic du circuit électrique', 'priority' => 'haute'],
                ['name' => '🔋 Test de batterie et alternateur', 'priority' => 'moyenne']
            ],
            'Hydraulique' => [
                ['name' => '💧 Contrôle du circuit hydraulique', 'priority' => 'haute'],
                ['name' => '🔍 Détection des fuites', 'priority' => 'haute']
            ],
            'Moteur' => [
                ['name' => '🔧 Diagnostic complet moteur', 'priority' => 'urgente'],
                ['name' => '🌡️ Contrôle du refroidissement', 'priority' => 'haute']
            ],
            'Vidange & filtres' => [
                ['name' => '🛢️ Vidange et changement filtres', 'priority' => 'moyenne'],
                ['name' => '🌬️ Nettoyage filtre à air', 'priority' => 'basse']
            ]
        ];
        
        if (isset($typeInterventions[$typePanne])) {
            foreach ($typeInterventions[$typePanne] as $interv) {
                $interventions[] = $interv;
            }
        }
        
        // Intervention standard si aucune n'a été ajoutée
        if (empty($interventions)) {
            $interventions[] = [
                'name' => '🔍 Diagnostic général',
                'reason' => 'Inspection préventive',
                'priority' => 'moyenne'
            ];
        }
        
        // Ajouter une intervention selon la priorité
        if ($priorite === 'urgente') {
            $interventions[] = [
                'name' => '🚨 Intervention d\'urgence',
                'reason' => 'Panne prioritaire détectée',
                'priority' => 'urgente'
            ];
        }
        
        return $interventions;
    }
    
    /**
     * Construit le prompt pour Gemini
     */
    private function buildSchedulePrompt(array $machineData, array $options, array $interventions): string
    {
        $interventionsText = '';
        foreach ($interventions as $i) {
            $interventionsText .= "\n- {$i['name']} (Priorité: {$i['priority']}) - {$i['reason']}";
        }
        
        $optionsText = '';
        $optionLabels = [
            'intervention' => '1. 🔍 Diagnostic préliminaire + étapes détaillées d\'intervention',
            'prevention' => '2. 📅 Planning préventif : intervalles selon km et type de panne',
            'pieces' => '3. 🔩 Pièces détachées : liste, disponibilité estimée et coûts (DT)',
            'optimisation' => '4. ⚡ Optimisation : regroupement des interventions',
            'securite' => '5. ⚠️ Précautions de sécurité et EPI requis',
            'controle' => '6. ✅ Points de contrôle post-intervention'
        ];
        
        foreach ($options as $opt) {
            if (isset($optionLabels[$opt])) {
                $optionsText .= "\n✅ " . $optionLabels[$opt];
            }
        }
        
        return "Tu es un expert en maintenance de machines agricoles.

=== CONTEXTE ===
Machine : {$machineData['nom']}
Type de panne/intervention : {$machineData['type']}
Priorité : {$machineData['priorite']}
Statut actuel : {$machineData['statut']}
Kilométrage : " . ($machineData['kilometrage'] ? $machineData['kilometrage'] . ' km' : 'Non renseigné') . "
Date de la maintenance : " . ($machineData['dateMain'] ?? 'Non renseignée') . "
Coût estimé : {$machineData['cout']} DT
Description : " . ($machineData['description'] ?: 'Aucune') . "
Recommandations existantes : " . ($machineData['recommandation'] ?: 'Aucune') . "

=== INTERVENTIONS RECOMMANDÉES PAR L'ANALYSE ===
{$interventionsText}

=== SECTIONS DEMANDÉES ===
{$optionsText}

En fin de réponse, ajoute :
## 📊 Score de santé estimé
Note de 0 à 100 représentant l'état de la machine après cette intervention.

## 💡 Recommandations intelligentes
3 recommandations clés pour éviter la récurrence de cette panne.

Sois précis, structuré et adapté au contexte agricole tunisien.";
    }
    
    /**
     * Génère un plan de maintenance local (fallback)
     */
    private function generateFallbackSchedule(array $machineData, array $options, array $interventions): string
    {
        $plan = "# 🤖 PLAN DE MAINTENANCE INTELLIGENT\n\n";
        $plan .= "## 📋 Récapitulatif\n";
        $plan .= "- **Machine** : {$machineData['nom']}\n";
        $plan .= "- **Type** : {$machineData['type']}\n";
        $plan .= "- **Priorité** : {$machineData['priorite']}\n";
        $plan .= "- **Kilométrage** : " . ($machineData['kilometrage'] ?: 'Non renseigné') . "\n\n";
        
        if (in_array('intervention', $options)) {
            $plan .= "## 🔍 DIAGNOSTIC PRÉLIMINAIRE\n";
            $plan .= "1. Vérification visuelle de l'état général\n";
            $plan .= "2. Test de fonctionnement à vide\n";
            $plan .= "3. Relevé des paramètres (température, pression, vibrations)\n\n";
            
            $plan .= "## 📝 ÉTAPES DÉTAILLÉES D'INTERVENTION\n";
            foreach ($interventions as $idx => $interv) {
                $plan .= ($idx + 1) . ". **{$interv['name']}**\n";
                $plan .= "   - {$interv['reason']}\n";
            }
            $plan .= "\n";
        }
        
        if (in_array('prevention', $options)) {
            $intervals = ['urgente' => 30, 'haute' => 60, 'moyenne' => 90, 'faible' => 180];
            $days = $intervals[$machineData['priorite']] ?? 90;
            $plan .= "## 📅 PLANNING PRÉVENTIF\n";
            $plan .= "- **Prochaine maintenance** : dans {$days} jours\n";
            if ($machineData['kilometrage'] && $machineData['kilometrage'] > 5000) {
                $plan .= "- **Vidange** : tous les 5000 km\n";
            }
            if ($machineData['kilometrage'] && $machineData['kilometrage'] > 10000) {
                $plan .= "- **Révision générale** : tous les 10000 km\n";
            }
            $plan .= "\n";
        }
        
        if (in_array('pieces', $options)) {
            $plan .= "## 🔩 PIÈCES DÉTACHÉES\n";
            $plan .= "| Pièce | Disponibilité | Coût estimé (DT) |\n";
            $plan .= "|-------|---------------|------------------|\n";
            $plan .= "| Filtres | En stock | 50-100 |\n";
            $plan .= "| Huile moteur | En stock | 80-150 |\n";
            $plan .= "| Courroies | Sur commande | 30-80 |\n\n";
        }
        
        if (in_array('optimisation', $options)) {
            $plan .= "## ⚡ OPTIMISATION\n";
            $plan .= "- Regrouper la vidange avec le changement des filtres\n";
            $plan .= "- Planifier la révision générale avec le contrôle de la transmission\n";
            $plan .= "- Réaliser le diagnostic électrique en même temps que le test moteur\n\n";
        }
        
        if (in_array('securite', $options)) {
            $plan .= "## ⚠️ PRÉCAUTIONS DE SÉCURITÉ\n";
            $plan .= "- Porter des gants de protection et lunettes\n";
            $plan .= "- Couper le moteur et attendre le refroidissement\n";
            $plan .= "- Utiliser des chandelles pour tout travail sous la machine\n";
            $plan .= "- Disposer d'un extincteur à proximité\n\n";
        }
        
        if (in_array('controle', $options)) {
            $plan .= "## ✅ POINTS DE CONTRÔLE POST-INTERVENTION\n";
            $plan .= "- [ ] Vérifier l'absence de fuites\n";
            $plan .= "- [ ] Contrôler le niveau des fluides\n";
            $plan .= "- [ ] Tester le fonctionnement à chaud\n";
            $plan .= "- [ ] Vérifier l'absence de voyants d'alerte\n\n";
        }
        
        // Score de santé
        $score = 100;
        if ($machineData['priorite'] === 'urgente') $score -= 30;
        elseif ($machineData['priorite'] === 'haute') $score -= 20;
        elseif ($machineData['priorite'] === 'moyenne') $score -= 10;
        if ($machineData['kilometrage'] && $machineData['kilometrage'] > 10000) $score -= 15;
        $score = max(0, min(100, $score));
        
        $plan .= "## 📊 SCORE DE SANTÉ ESTIMÉ\n";
        $plan .= "**{$score}/100** - " . ($score >= 80 ? "État satisfaisant" : ($score >= 60 ? "Surveillance recommandée" : "Intervention nécessaire")) . "\n\n";
        
        $plan .= "## 💡 RECOMMANDATIONS INTELLIGENTES\n";
        $plan .= "1. Suivre rigoureusement le planning d'entretien préventif\n";
        $plan .= "2. Former les opérateurs aux bonnes pratiques d'utilisation\n";
        $plan .= "3. Tenir un carnet de bord pour tracer toutes les interventions\n";
        
        return $plan;
    }

    // ── API : Generate maintenance schedule (page HTML) ──
    #[Route('/api/schedule/{id}', name: 'agri_maintenances_api_schedule', methods: ['GET'])]
    public function apiSchedule(int $id, MaintenanceRepository $repo): Response
    {
        $maintenance = $repo->findOneWithMaterielName($id);
        if (!$maintenance) {
            throw $this->createNotFoundException('Maintenance non trouvée.');
        }
        return $this->render('maintenances/api_schedule_html.html.twig', [
            'maintenance' => $maintenance,
        ]);
    }

    // ── API : Diagnostics (page HTML) ──
    #[Route('/api/diagnostics/{id}', name: 'agri_maintenances_api_diagnostics', methods: ['GET'])]
    public function apiDiagnostics(int $id, MaintenanceRepository $repo): Response
    {
        $maintenance = $repo->findOneWithMaterielName($id);
        if (!$maintenance) {
            throw $this->createNotFoundException('Maintenance non trouvée.');
        }
        return $this->render('maintenances/api_diagnostics_html.html.twig', [
            'maintenance' => $maintenance,
        ]);
    }

    // ── API : Endpoint pour les diagnostics intelligents ──
    #[Route('/api/diagnostics/generate', name: 'agri_maintenances_api_diagnostics_generate', methods: ['POST'])]
    public function generateDiagnostics(Request $request, MaintenanceRepository $repo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $maintenanceId = $data['maintenanceId'] ?? null;
        $diagnosticTypes = $data['diagnosticTypes'] ?? [];
        $prompt = $data['prompt'] ?? null;
        
        if ($maintenanceId) {
            $maintenance = $repo->findOneWithMaterielName($maintenanceId);
            if (!$maintenance) {
                return $this->json(['error' => 'Maintenance non trouvée'], 404);
            }
            
            $machineData = [
                'nom' => $maintenance->getNom() ?? 'Machine non spécifiée',
                'typePanne' => $maintenance->getTypePanne(),
                'priorite' => $maintenance->getPriorite(),
                'statut' => $maintenance->getStatut(),
                'kilometrage' => $maintenance->getKilometrage(),
                'description' => $maintenance->getDescription(),
                'cout' => $maintenance->getCout(),
                'dateMain' => $maintenance->getDateMain() ? $maintenance->getDateMain()->format('Y-m-d') : null
            ];
            
            // Construire le prompt si non fourni
            if (!$prompt) {
                $prompt = $this->buildDiagnosticsPrompt($machineData, $diagnosticTypes);
            }
        } elseif ($prompt === null) {
            return $this->json(['error' => 'Données insuffisantes. Fournissez maintenanceId ou prompt.'], 400);
        }
        
        // Appeler Gemini
        $apiKey = $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY');
        if (empty($apiKey)) {
            return $this->json(['error' => 'Clé API Gemini non configurée'], 500);
        }
        
        $model = $_ENV['GEMINI_MODEL'] ?? 'gemini-2.0-flash-exp';
        $apiUrl = $_ENV['GEMINI_API_URL'] ?? 'https://generativelanguage.googleapis.com/v1beta/models';
        $url = "{$apiUrl}/{$model}:generateContent?key={$apiKey}";
        
        $requestData = [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => ['temperature' => 0.7, 'maxOutputTokens' => 4000]
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($requestData)
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMsg = $errorData['error']['message'] ?? $response;
            return $this->json(['error' => 'Erreur Gemini API: ' . $errorMsg], $httpCode);
        }
        
        $result = json_decode($response, true);
        $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        return $this->json(['success' => true, 'diagnostics' => $text]);
    }
    
    private function buildDiagnosticsPrompt(array $machineData, array $diagnosticTypes): string
    {
        $typesText = '';
        $allTypes = self::DIAG_TYPES_BY_PANNE[$machineData['typePanne']] ?? self::DEFAULT_DIAG_TYPES;
        
        $selectedTypes = [];
        foreach ($diagnosticTypes as $typeId) {
            foreach ($allTypes as $type) {
                if ($type['id'] === $typeId) {
                    $selectedTypes[] = $type;
                    break;
                }
            }
        }
        
        if (empty($selectedTypes)) {
            $selectedTypes = $allTypes;
        }
        
        foreach ($selectedTypes as $type) {
            $typesText .= "\n- {$type['icon']} {$type['name']} : {$type['desc']}";
        }
        
        return "Tu es un expert en diagnostic de machines agricoles.

=== CONTEXTE DE LA MACHINE ===
Machine : {$machineData['nom']}
Type de panne : {$machineData['typePanne']}
Priorité : {$machineData['priorite']}
Statut actuel : {$machineData['statut']}
Kilométrage : " . ($machineData['kilometrage'] ? $machineData['kilometrage'] . ' km' : 'Non renseigné') . "
Coût estimé : {$machineData['cout']} DT
Date : " . ($machineData['dateMain'] ?? 'Non renseignée') . "
Description : " . ($machineData['description'] ?: 'Aucune') . "

=== TYPES DE DIAGNOSTICS DEMANDÉS ===
{$typesText}

=== CONSIGNES ===
Pour CHAQUE type de diagnostic, fournis :
1. **Description** : Explication du diagnostic dans ce contexte précis
2. **Procédure étape par étape** : Comment effectuer ce diagnostic
3. **Outils nécessaires** : Équipements et matériel requis
4. **Valeurs normales** : Seuils et plages acceptables
5. **Signes d'anomalie** : Ce qui indique un problème
6. **Actions correctives** : Mesures à prendre si anomalie
7. **Fréquence recommandée** : Intervalles basés sur le kilométrage/usage

En fin de réponse, ajoute une section :
## 🎯 Score de criticité
Évalue la criticité (1-10) de la panne \"{$machineData['typePanne']}\" sur ce type de machine et justifie.

## 📅 Prochaine maintenance recommandée
Basé sur le kilométrage et le type de panne, indique la prochaine échéance.

## 🔩 Pièces détachées potentielles
Liste les pièces susceptibles d'être remplacées avec estimation de coût.

Sois précis, pratique et adapté au contexte agricole tunisien.";
    }

    // EXPORT EXCEL
    #[Route('/export/excel', name: 'agri_maintenances_export_excel', methods: ['GET'])]
    public function exportExcel(MaintenanceRepository $repo): StreamedResponse
    {
        $maintenances = $repo->findAllOrderedByDate();

        $response = new StreamedResponse(function () use ($maintenances) {
            $handle = fopen('php://output', 'w+');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['AGROFLOW — Rapport Maintenances'], ';');
            fputcsv($handle, ['Exporté le : ' . (new \DateTime())->format('d/m/Y H:i')], ';');
            fputcsv($handle, ['Nombre d\'enregistrements : ' . count($maintenances)], ';');
            fputcsv($handle, [], ';');
            fputcsv($handle, ['#', 'Type de panne', 'Coût (DT)', 'Date', 'Statut', 'Priorité', 'Kilométrage', 'Description', 'Recommandation', 'ID Matériel', 'Nom Matériel'], ';');

            $index = 1;
            $totalCout = 0.0;
            foreach ($maintenances as $m) {
                fputcsv($handle, [
                    $index++,
                    $m->getTypePanne(),
                    number_format($m->getCout(), 2, ',', ' '),
                    $m->getDateMain()?->format('d/m/Y') ?? '',
                    $m->getStatut() ?? '',
                    $m->getPriorite() ?? '',
                    $m->getKilometrage() ?? '',
                    $m->getDescription() ?? '',
                    $m->getRecommandation() ?? '',
                    $m->getIdM() ? '#' . $m->getIdM() : '',
                    $m->getNom() ?? '',
                ], ';');
                $totalCout += $m->getCout();
            }

            fputcsv($handle, [], ';');
            fputcsv($handle, ['', 'TOTAL', number_format($totalCout, 2, ',', ' ') . ' DT'], ';');
            fclose($handle);
        });

        $filename = 'maintenances_' . (new \DateTime())->format('Ymd_Hi') . '.csv';
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        return $response;
    }

    // EXPORT PDF
    #[Route('/export/pdf', name: 'agri_maintenances_export_pdf', methods: ['GET'])]
    public function exportPdf(MaintenanceRepository $repo): Response
    {
        return $this->render('maintenances/pdf.html.twig', [
            'maintenances' => $repo->findAllOrderedByDate(),
        ]);
    }

    // CREATE
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

        return $this->render('maintenances/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // SHOW
    #[Route('/{id}', name: 'agri_maintenances_show', methods: ['GET'])]
    public function show(int $id, MaintenanceRepository $repo): Response
    {
        $maintenance = $repo->findOneWithMaterielName($id);
        if (!$maintenance) {
            throw $this->createNotFoundException('Maintenance non trouvée.');
        }
        return $this->render('maintenances/show.html.twig', [
            'maintenance' => $maintenance,
        ]);
    }

    // EDIT
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

        return $this->render('maintenances/edit.html.twig', [
            'form'        => $form->createView(),
            'maintenance' => $maintenance,
        ]);
    }

    // DELETE
    #[Route('/{id}/delete', name: 'agri_maintenances_delete', methods: ['POST'])]
    public function delete(Request $request, Maintenance $maintenance, EntityManagerInterface $em): Response
    {
        $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete_maintenance_' . $maintenance->getIdMain(), $token)) {
            $em->remove($maintenance);
            $em->flush();
            $this->addFlash('success', 'Maintenance supprimée avec succès.');
        } else {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
        }
        return $this->redirectToRoute('agri_maintenances_index');
    }
}