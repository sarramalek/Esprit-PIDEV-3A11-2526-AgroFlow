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
     * Prédit les pannes futures pour une machine
     */
    /**
     * @return array{risque_panne:string, probabilite:int|float, delai_estime:string, type_panne_probable:string, recommandations:array<int, string>}
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
            $type = $m->getTypePanne();
            $frequences[$type] = ($frequences[$type] ?? 0) + 1;
        }
        
        // Trouver la panne la plus fréquente
        arsort($frequences);
        $predictedType = array_key_first($frequences);
        $probability = min(95, round(($frequences[$predictedType] / count($maintenances)) * 100));
        
        // Calculer le délai estimé
        $dates = array_values(array_filter(array_map(fn($m) => $m->getDateMain(), $maintenances)));
        usort($dates, fn(\DateTimeInterface $a, \DateTimeInterface $b) => $a <=> $b);
        if (!empty($dates)) {
            $intervals = [];
            for ($i = 1; $i < count($dates); $i++) {
                $intervals[] = $dates[$i - 1]->diff($dates[$i])->days;
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
    /**
     * @return array{score:int, niveau:string, couleur:string, details:array{total_maintenances:int, urgences:int, cout_total:float, derniere_maintenance:string, jours_sans_maintenance:int}}
     */
    public function calculateHealthScore(Machine $machine): array
    {
        $maintenances = $this->maintenanceRepo->findBy(['idM' => $machine->getId()]);
        
        if (empty($maintenances)) {
            return [
                'score' => 100,
                'niveau' => 'excellent',
                'couleur' => '#4caf50',
                'details' => [
                    'total_maintenances' => 0,
                    'urgences' => 0,
                    'cout_total' => 0.0,
                    'derniere_maintenance' => 'Jamais',
                    'jours_sans_maintenance' => 365,
                ],
            ];
        }
        
        $totalCost = array_sum(array_map(fn($m) => $m->getCout(), $maintenances));
        $urgentCount = count(array_filter($maintenances, fn($m) => $m->getPriorite() === 'urgente'));
        
        // Dernière maintenance
        $lastMaintenance = $maintenances[0];
        $daysSinceLast = $lastMaintenance->getDateMain()
            ? (new \DateTime())->diff($lastMaintenance->getDateMain())->days
            : 365;
        
        // Calcul du score
        $score = 100;
        $score -= min(30, $totalCost / 100);  // -30 max pour coût élevé
        $score -= min(40, $urgentCount * 10); // -40 max pour urgences
        $score -= min(20, $daysSinceLast / 10); // -20 max pour ancienneté
        $score -= min(20, count($maintenances) * 2); // -2 par maintenance
        
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
                'derniere_maintenance' => $lastMaintenance->getDateMain() ? $lastMaintenance->getDateMain()->format('d/m/Y') : 'Jamais',
                'jours_sans_maintenance' => $daysSinceLast
            ]
        ];
    }

    /**
     * Génère des alertes intelligentes
     */
    /**
     * @return array<int, array{type:string, message:string, actions:array<int, string>, urgence:string}>
     */
    public function generateSmartAlerts(Machine $machine): array
    {
        $alerts = [];
        $healthScore = $this->calculateHealthScore($machine);
        $prediction = $this->predictFailure($machine);
        
        // Alerte critique
        if ($healthScore['score'] < 40) {
            $alerts[] = [
                'type' => 'critique',
                'message' => sprintf("🚨 Machine %s en état critique (score: %d/100)", 
                    $machine->getNom(), $healthScore['score']),
                'actions' => $prediction['recommandations'],
                'urgence' => 'immediate'
            ];
        }
        
        // Alerte risque de panne élevé
        if ($prediction['risque_panne'] === 'élevé') {
            $alerts[] = [
                'type' => 'warning',
                'message' => sprintf("⚠️ Risque élevé de panne %s sur %s (probabilité: %d%%)",
                    $prediction['type_panne_probable'], $machine->getNom(), $prediction['probabilite']),
                'actions' => ['Planifier une intervention préventive'],
                'urgence' => 'programmee'
            ];
        }
        
        // Alerte maintenance overdue
        $daysSince = $healthScore['details']['jours_sans_maintenance'];
        if ($daysSince > 180) {
            $alerts[] = [
                'type' => 'warning',
                'message' => sprintf("📅 Plus de %d jours sans maintenance pour %s", $daysSince, $machine->getNom()),
                'actions' => ['Effectuer une maintenance complète'],
                'urgence' => 'haute'
            ];
        }
        
        return $alerts;
    }

    /**
     * Priorise les interventions sur toutes les machines
     *
     * @param array<int, Machine> $machines
     * @return array<int, array<string, mixed>>
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
     *
     * @param array<int, array<string, mixed>> $priorities
     * @return array<int, array<string, mixed>>
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

    /**
     * @return array<string, mixed>
     */
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
                'maxOutputTokens' => 150,
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

    /**
     * @param array<string, mixed> $response
     */
    private function extractTextFromResponse(array $response): string
    {
        return $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }

    private function buildPrompt(Maintenance $m): string
    {
        $type = $m->getTypePanne();
        $statut = $m->getStatut();
        $priorite = $m->getPriorite();
        $km = $m->getKilometrage();
        $description = $m->getDescription();
        
        $kmText = $km ? "{$km} km" : "non renseigné";
        $descText = $description ? "Description: {$description}" : "";
        
        return "Tu es un expert en maintenance industrielle et agricole.
        
        Voici les informations d'une intervention de maintenance:
        - Type de panne: {$type}
        - Statut: {$statut}
        - Priorité: {$priorite}
        - Kilométrage: {$kmText}
        - {$descText}
        
        Donne une recommandation précise et actionable (max 150 caractères) concernant:
        - La prochaine intervention recommandée
        - Les vérifications à effectuer
        - Le délai ou kilométrage avant la prochaine maintenance
        
        Réponse courte et professionnelle, sans texte inutile.";
    }

    private function getFallbackRecommendation(Maintenance $m): string
    {
        $type = strtolower($m->getTypePanne());
        $priorite = $m->getPriorite();
        $km = $m->getKilometrage();
        
        $fallbacks = [
            'moteur' => '🔧 Révision moteur recommandée tous les 500h ou 10 000 km',
            'vidange' => '🛢️ Prochaine vidange dans 3 mois ou 3000 km',
            'électrique' => '⚡ Contrôle préventif du circuit électrique et batterie',
            'electricité' => '⚡ Contrôle préventif du circuit électrique et batterie',
            'hydraulique' => '💧 Vérification du niveau et des flexibles hydrauliques',
            'transmission' => '⚙️ Inspection de la transmission et de l\'embrayage',
            'frein' => '🛑 Contrôle d\'usure des plaquettes et disques',
            'freins' => '🛑 Contrôle d\'usure des plaquettes et disques',
            'pneumatique' => '🎈 Vérification de la pression et des fuites',
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

    /**
     * @return array<int, string>
     */
    private function getPredictionRecommendations(string $type, string $risque): array
    {
        $recommendations = [
            'moteur' => ['Contrôle du niveau d\'huile', 'Vérification des injecteurs', 'Test de compression'],
            'vidange' => ['Changement d\'huile moteur', 'Remplacement du filtre à huile', 'Contrôle du niveau'],
            'électrique' => ['Test de la batterie', 'Vérification de l\'alternateur', 'Inspection du faisceau'],
            'hydraulique' => ['Contrôle du niveau d\'huile', 'Inspection des flexibles', 'Test de pression'],
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