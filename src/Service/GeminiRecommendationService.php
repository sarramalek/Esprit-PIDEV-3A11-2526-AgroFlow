<?php
// src/Service/GeminiRecommendationService.php

namespace App\Service;

use App\Entity\Materiels\Maintenance;
use App\Entity\Materiels\Machine;
use App\Repository\Materiels\MaintenanceRepository;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeminiRecommendationService
{
    private string $apiKey;
    private string $model;
    private LoggerInterface $logger;
    private HttpClientInterface $httpClient;
    private MaintenanceRepository $maintenanceRepo;

    public function __construct(
        string $apiKey,
        string $model,
        LoggerInterface $logger,
        HttpClientInterface $httpClient,
        MaintenanceRepository $maintenanceRepo
    ) {
        if (empty($apiKey)) {
            throw new \InvalidArgumentException('Gemini API key is required');
        }
        $this->apiKey = $apiKey;
        $this->model = $model;
        $this->logger = $logger;
        $this->httpClient = $httpClient;
        $this->maintenanceRepo = $maintenanceRepo;
    }

    /**
     * Génère une recommandation IA via Gemini
     */
    public function generateRecommendation(Maintenance $maintenance): string
    {
        try {
            $prompt = $this->buildPrompt($maintenance);
            $response = $this->callGeminiApi($prompt);
            $recommendation = $this->extractTextFromResponse($response);

            $recommendation = trim(strip_tags($recommendation));

            if (strlen($recommendation) > 500) {
                $recommendation = substr($recommendation, 0, 497) . '...';
            }

            return $recommendation ?: $this->getFallbackRecommendation($maintenance);

        } catch (\Exception $e) {
            $this->logger->error('Gemini API error: ' . $e->getMessage());
            return $this->getFallbackRecommendation($maintenance);
        }
    }

    /**
     * Génère une réponse personnalisée via Gemini
     */
    public function generateCustomResponse(string $prompt): string
    {
        try {
            $response = $this->callGeminiApi($prompt);
            $text = $this->extractTextFromResponse($response);
            $text = trim(strip_tags($text));

            if (strlen($text) > 1000) {
                $text = substr($text, 0, 997) . '...';
            }

            return $text ?: "Je n'ai pas pu générer de réponse pour cette demande.";

        } catch (\Exception $e) {
            $this->logger->error('Gemini custom prompt error: ' . $e->getMessage());
            return "Désolé, une erreur est survenue lors de la génération de la réponse.";
        }
    }

    /**
     * Prédit les pannes futures pour une machine
     */
    public function predictFailure(Machine $machine): array
    {
        $maintenances = $this->maintenanceRepo->findBy(['idM' => $machine->getId()]);

        if (empty($maintenances)) {
            return [
                'risque_panne' => 'faible',
                'probabilite' => 5,
                'delai_estime' => '6 mois',
                'type_panne_probable' => 'Aucune',
                'recommandations' => ['Maintenance préventive standard']
            ];
        }

        // Compter les fréquences des types de panne
        $frequences = [];
        foreach ($maintenances as $m) {
            $type = $m->getTypePanne() ?? 'Non spécifié';
            $frequences[$type] = ($frequences[$type] ?? 0) + 1;
        }

        // Trouver la panne la plus fréquente
        arsort($frequences);
        $predictedType = array_key_first($frequences);
        $probability = min(95, round(($frequences[$predictedType] / count($maintenances)) * 100));

        // Calculer le délai estimé
        $dates = array_filter(array_map(fn($m) => $m->getDateMain(), $maintenances));
        if (!empty($dates)) {
            $intervals = [];
            for ($i = 1; $i < count($dates); $i++) {
                if ($dates[$i] && $dates[$i-1]) {
                    $intervals[] = $dates[$i-1]->diff($dates[$i])->days;
                }
            }
            $avgInterval = !empty($intervals) ? array_sum($intervals) / count($intervals) : 90;
            $delai = round($avgInterval / 30) . ' mois';
        } else {
            $delai = '3 mois';
        }

        $risque = $probability > 70 ? 'élevé' : ($probability > 40 ? 'moyen' : 'faible');

        return [
            'risque_panne' => $risque,
            'probabilite' => $probability,
            'delai_estime' => $delai,
            'type_panne_probable' => $predictedType,
            'recommandations' => $this->getPredictionRecommendations($predictedType, $risque)
        ];
    }

    /**
     * Calcule le score de santé d'une machine (0-100)
     */
    public function calculateHealthScore(Machine $machine): array
    {
        $maintenances = $this->maintenanceRepo->findBy(['idM' => $machine->getId()]);

        if (empty($maintenances)) {
            return ['score' => 100, 'niveau' => 'excellent', 'couleur' => '#4caf50', 'details' => []];
        }

        $totalCost = array_sum(array_map(fn($m) => $m->getCout(), $maintenances));
        $urgentCount = count(array_filter($maintenances, fn($m) => $m->getPriorite() === 'urgente'));

        // Dernière maintenance
        $lastMaintenance = $maintenances[0] ?? null;
        $daysSinceLast = $lastMaintenance?->getDateMain() ?
            (new \DateTime())->diff($lastMaintenance->getDateMain())->days : 365;

        // Calcul du score
        $score = 100;
        $score -= min(30, $totalCost / 100);
        $score -= min(40, $urgentCount * 10);
        $score -= min(20, $daysSinceLast / 10);
        $score -= min(20, count($maintenances) * 2);

        $score = max(0, min(100, round($score)));

        $niveau = match(true) {
            $score >= 80 => 'excellent',
            $score >= 60 => 'bon',
            $score >= 40 => 'moyen',
            $score >= 20 => 'critique',
            default => 'danger'
        };

        $couleur = match($niveau) {
            'excellent' => '#4caf50',
            'bon' => '#8bc34a',
            'moyen' => '#ff9800',
            'critique' => '#f44336',
            'danger' => '#d32f2f'
        };

        return [
            'score' => $score,
            'niveau' => $niveau,
            'couleur' => $couleur,
            'details' => [
                'total_maintenances' => count($maintenances),
                'urgences' => $urgentCount,
                'cout_total' => round($totalCost, 2),
                'derniere_maintenance' => $lastMaintenance?->getDateMain()?->format('d/m/Y') ?? 'Jamais',
                'jours_sans_maintenance' => $daysSinceLast
            ]
        ];
    }

    /**
     * Génère des alertes intelligentes
     */
    public function generateSmartAlerts(Machine $machine): array
    {
        $alerts = [];

        try {
            $healthScore = $this->calculateHealthScore($machine);
            $prediction = $this->predictFailure($machine);

            // Alerte critique
            if ($healthScore['score'] < 40) {
                $alerts[] = [
                    'icon' => '🚨',
                    'title' => 'État critique',
                    'message' => sprintf("Machine %s en état critique (score: %d/100)",
                        $machine->getNom(), $healthScore['score']),
                    'priority' => 'urgente',
                    'action' => 'Intervention immédiate'
                ];
            }

            // Alerte risque de panne élevé
            if ($prediction['risque_panne'] === 'élevé') {
                $alerts[] = [
                    'icon' => '⚠️',
                    'title' => 'Risque de panne élevé',
                    'message' => sprintf("Risque élevé de panne %s sur %s (probabilité: %d%%)",
                        $prediction['type_panne_probable'], $machine->getNom(), $prediction['probabilite']),
                    'priority' => 'haute',
                    'action' => 'Planifier intervention préventive'
                ];
            }

            // Alerte maintenance overdue
            $daysSince = $healthScore['details']['jours_sans_maintenance'] ?? 365;
            if ($daysSince > 180) {
                $alerts[] = [
                    'icon' => '📅',
                    'title' => 'Maintenance dépassée',
                    'message' => sprintf("Plus de %d jours sans maintenance pour %s", $daysSince, $machine->getNom()),
                    'priority' => 'haute',
                    'action' => 'Effectuer maintenance complète'
                ];
            }
        } catch (\Exception $e) {
            $this->logger->warning('Failed to generate smart alerts: ' . $e->getMessage());
        }

        return $alerts;
    }

    /**
     * Priorise les interventions sur toutes les machines
     */
    public function prioritizeInterventions(array $machines): array
    {
        $priorities = [];

        foreach ($machines as $machine) {
            $healthScore = $this->calculateHealthScore($machine);
            $prediction = $this->predictFailure($machine);

            $priorityScore = (100 - $healthScore['score']) * 0.6;
            if ($prediction['risque_panne'] === 'élevé') $priorityScore += 30;
            if ($prediction['risque_panne'] === 'moyen') $priorityScore += 15;
            $priorityScore = round(min(100, $priorityScore));

            $niveauUrgence = match(true) {
                $priorityScore >= 70 => 'CRITIQUE',
                $priorityScore >= 40 => 'HAUTE',
                default => 'NORMALE'
            };

            $priorities[] = [
                'machine_id' => $machine->getId(),
                'machine_nom' => $machine->getNom(),
                'priority_score' => $priorityScore,
                'niveau_urgence' => $niveauUrgence,
                'health_score' => $healthScore['score'],
                'risque_panne' => $prediction['risque_panne'],
                'type_panne_probable' => $prediction['type_panne_probable'],
                'actions_recommandees' => $prediction['recommandations']
            ];
        }

        // Trier par score décroissant
        usort($priorities, fn($a, $b) => $b['priority_score'] <=> $a['priority_score']);

        return $priorities;
    }

    /**
     * Génère un planning optimisé
     */
    public function generateOptimizedSchedule(array $priorities, int $daysHorizon = 30): array
    {
        $schedule = [];
        $currentDate = new \DateTime();

        foreach ($priorities as $index => $priority) {
            $interval = match($priority['niveau_urgence']) {
                'CRITIQUE' => 1,
                'HAUTE' => 3,
                'NORMALE' => 7,
                default => 14
            };

            $plannedDate = clone $currentDate;
            $plannedDate->modify("+{$interval} days");

            if ($plannedDate <= (clone $currentDate)->modify("+{$daysHorizon} days")) {
                $schedule[] = [
                    'machine_id' => $priority['machine_id'],
                    'machine_nom' => $priority['machine_nom'],
                    'date_planifiee' => $plannedDate->format('Y-m-d'),
                    'urgence' => $priority['niveau_urgence'],
                    'actions' => $priority['actions_recommandees'],
                    'duree_estimee_heures' => $priority['niveau_urgence'] === 'CRITIQUE' ? 8 : 4,
                    'priorite_globale' => $index + 1
                ];
            }
        }

        return $schedule;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MÉTHODES PRIVÉES
    // ─────────────────────────────────────────────────────────────────────────

    private function callGeminiApi(string $prompt): array
    {
        $url = sprintf(
            'https://generativelanguage.googleapis.com/v1/models/%s:generateContent?key=%s',
            $this->model,
            $this->apiKey
        );

        $payload = [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => [
                'temperature' => 0.3,
                'maxOutputTokens' => 500,
                'topP' => 0.95,
                'topK' => 40,
            ]
        ];

        $response = $this->httpClient->request('POST', $url, [
            'json' => $payload,
            'headers' => ['Content-Type' => 'application/json'],
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('API returned status ' . $response->getStatusCode());
        }

        return $response->toArray();
    }

    private function extractTextFromResponse(array $response): string
    {
        return $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PROMPT ENRICHI — génère une recommandation structurée et professionnelle
    // ─────────────────────────────────────────────────────────────────────────
    private function buildPrompt(Maintenance $m): string
    {
        $type        = $m->getTypePanne()   ?? 'Non spécifié';
        $statut      = $m->getStatut()      ?? 'planifie';
        $priorite    = $m->getPriorite()    ?? 'moyenne';
        $km          = $m->getKilometrage();
        $description = $m->getDescription();
        $machineName = method_exists($m, 'getNomMateriel') && $m->getNomMateriel()
            ? $m->getNomMateriel()
            : (method_exists($m, 'getNom') && $m->getNom() ? $m->getNom() : 'Machine agricole');

        $kmText   = $km         ? "{$km} km"             : "non renseigné";
        $descText = $description ? "Description : {$description}" : "";

        $statutLabel   = match($statut) {
            'en_cours' => 'En cours',
            'termine'  => 'Terminée',
            default    => 'Planifiée',
        };
        $prioriteLabel = match($priorite) {
            'urgente' => 'URGENTE 🔴',
            'haute'   => 'Haute 🟠',
            'moyenne' => 'Moyenne 🟢',
            default   => 'Faible 🔵',
        };

        return "Tu es un expert en maintenance industrielle et agricole. Tu génères des recommandations professionnelles, précises et directement actionnables.

Voici les données d'une intervention de maintenance :
- Machine       : {$machineName}
- Type de panne : {$type}
- Statut        : {$statutLabel}
- Priorité      : {$prioriteLabel}
- Kilométrage   : {$kmText}
- {$descText}

Génère UNE recommandation courte (max 160 caractères) sous la forme :
[emoji] [Action principale]. [Détail technique court ou délai recommandé].

Exemples de bon format :
🔧 Vérifier le niveau et la qualité de l'huile moteur avant la vidange. Une huile dégradée accélère l'usure.
💧 Inspecter les flexibles hydrauliques et purger le circuit. Refaire la pression à 180 bar après réparation.
⚡ Tester la batterie (tension < 12,4V = remplacement) et vérifier l'alternateur sous charge.

Réponds uniquement avec la recommandation, sans explication ni texte supplémentaire.";
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FALLBACK ENRICHI — recommandation locale si l'API échoue
    // ─────────────────────────────────────────────────────────────────────────
    private function getFallbackRecommendation(Maintenance $m): string
    {
        $type     = mb_strtolower($m->getTypePanne() ?? '');
        $priorite = $m->getPriorite();
        $km       = $m->getKilometrage() ?? 0;

        // ── Matrice type de panne → recommandation enrichie ──
        $fallbacks = [
            'moteur'       => '🔧 Vérifier le niveau et la qualité de l\'huile moteur avant la vidange. Une huile dégradée accélère l\'usure du moteur.',
            'vidange'      => '🛢️ Effectuer la vidange moteur complète (huile + filtre). Utiliser une huile 15W40 STOU — intervalle max 500h ou 1 an.',
            'électricité'  => '⚡ Tester la batterie (tension cible ≥ 12,4V) et l\'alternateur sous charge. Vérifier les masses et fusibles.',
            'electrique'   => '⚡ Tester la batterie (tension cible ≥ 12,4V) et l\'alternateur sous charge. Vérifier les masses et fusibles.',
            'hydraulique'  => '💧 Inspecter les flexibles HP et le niveau d\'huile hydraulique. Purger le circuit si nécessaire — pression cible : 180 bar.',
            'transmission' => '⚙️ Contrôler l\'huile de boîte (aspect + niveau) et l\'embrayage. Graisser les cardans selon le carnet constructeur.',
            'frein'        => '🛑 Mesurer l\'épaisseur des plaquettes et disques (min légal : 2 mm). Purger le circuit de freinage tous les 2 ans.',
            'freins'       => '🛑 Mesurer l\'épaisseur des plaquettes et disques (min légal : 2 mm). Purger le circuit de freinage tous les 2 ans.',
            'pneumatique'  => '🎈 Vérifier la pression des pneus (1,6–2,4 bar selon charge) et l\'état des flancs. Permuter si usure asymétrique.',
            'refroidissement' => '🌡️ Contrôler le niveau de liquide de refroidissement et l\'état du radiateur. Rinçage complet tous les 2 ans recommandé.',
            'injection'    => '⛽ Tester les injecteurs (débit + atomisation) et remplacer les filtres gasoil. Purger l\'air du circuit d\'alimentation.',
            'courroie'     => '🔩 Contrôler la tension et l\'usure de la courroie. Remplacement préventif tous les 1 000h ou 4 ans selon constructeur.',
            'filtre'       => '🔍 Remplacer les filtres (huile, air, gasoil) selon le planning constructeur. Nettoyer le pré-filtre à air à chaque utilisation.',
            'embrayage'    => '⚙️ Régler la garde d\'embrayage et vérifier l\'usure du disque. Si glissement détecté → remplacement immédiat.',
        ];

        foreach ($fallbacks as $key => $message) {
            if (str_contains($type, $key)) {
                return $message;
            }
        }

        // ── Par priorité ──
        if ($priorite === 'urgente') {
            return "🚨 Intervention immédiate requise — arrêter la machine et contacter un technicien. Type de panne : {$m->getTypePanne()}.";
        }

        if ($priorite === 'haute') {
            return "⚠️ Intervention à planifier sous 48h. Inspecter les composants liés à : {$m->getTypePanne()}.";
        }

        // ── Par kilométrage ──
        if ($km >= 15000) {
            return "🔧 Kilométrage critique ({$km} km) — révision majeure obligatoire : moteur, transmission, freins et filtres complets.";
        }

        if ($km >= 10000) {
            return "📊 Révision générale recommandée à {$km} km — vidange complète, filtres, courroies et contrôle des niveaux.";
        }

        if ($km >= 5000) {
            return "📅 Vidange moteur et changement des filtres requis à {$km} km. Vérifier aussi la pression des pneus et les niveaux.";
        }

        return "🔍 Surveillance périodique recommandée pour : {$m->getTypePanne()}. Effectuer une inspection visuelle complète avant la prochaine utilisation.";
    }

    private function getPredictionRecommendations(string $type, string $risque): array
    {
        $recommendations = [
            'moteur'       => ['Contrôle du niveau d\'huile', 'Vérification des injecteurs', 'Test de compression'],
            'vidange'      => ['Changement d\'huile moteur', 'Remplacement du filtre à huile', 'Contrôle du niveau'],
            'électrique'   => ['Test de la batterie', 'Vérification de l\'alternateur', 'Inspection du faisceau'],
            'hydraulique'  => ['Contrôle du niveau d\'huile', 'Inspection des flexibles', 'Test de pression'],
            'transmission' => ['Vidange de la transmission', 'Réglage de l\'embrayage', 'Contrôle des cardans'],
        ];

        $baseRecos = $recommendations[strtolower($type)] ?? ['Inspection générale', 'Maintenance préventive'];

        if ($risque === 'élevé') {
            return array_merge($baseRecos, ['Intervention immédiate', 'Diagnostic complet']);
        }

        if ($risque === 'moyen') {
            return array_merge($baseRecos, ['Planifier intervention sous 15 jours']);
        }

        return $baseRecos;
    }
}