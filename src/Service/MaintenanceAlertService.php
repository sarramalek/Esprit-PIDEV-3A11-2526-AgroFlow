<?php
// src/Service/MaintenanceAlertService.php

namespace App\Service;

use App\Entity\Materiels\Maintenance;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class MaintenanceAlertService
{
    private HttpClientInterface $httpClient;
    private string $geminiApiKey;
    private ?LoggerInterface $logger;

    public function __construct(HttpClientInterface $httpClient, string $geminiApiKey, ?LoggerInterface $logger = null)
    {
        $this->httpClient = $httpClient;
        $this->geminiApiKey = $geminiApiKey;
        $this->logger = $logger;
    }

    /**
     * Calcule le statut d'alerte d'une maintenance
     * Retourne : 'overdue' (dépassé), 'warning' (proche), 'ok' (dans les temps)
     */
    public function getAlertStatus(Maintenance $maintenance): array
    {
        $dateMain = $maintenance->getDateMain();
        if (!$dateMain) {
            return ['status' => 'no_date', 'message' => '📅 Date non définie', 'class' => 'info'];
        }

        $now = new \DateTime();
        $interval = $now->diff($dateMain);
        $daysLeft = (int) $interval->format('%r%a');

        if ($daysLeft < 0) {
            $daysOverdue = abs($daysLeft);
            return [
                'status' => 'overdue',
                'message' => sprintf("⚠️ EN RETARD de %d jour%s", $daysOverdue, $daysOverdue > 1 ? 's' : ''),
                'class' => 'danger',
                'days' => $daysOverdue
            ];
        } elseif ($daysLeft <= 3) {
            return [
                'status' => 'warning',
                'message' => sprintf("⏰ Échéance dans %d jour%s", $daysLeft, $daysLeft > 1 ? 's' : ''),
                'class' => 'warning',
                'days' => $daysLeft
            ];
        } else {
            return [
                'status' => 'ok',
                'message' => sprintf("✅ Dans les temps (%d jours restants)", $daysLeft),
                'class' => 'success',
                'days' => $daysLeft
            ];
        }
    }

    /**
     * Génère des alertes IA intelligentes pour une maintenance
     * @return array<int, array<string, mixed>>
     */
    public function generateIntelligentAlerts(Maintenance $maintenance): array
    {
        $alerts = [];
        
        // 1. Alerte de base selon le statut
        $statusAlert = $this->getAlertStatus($maintenance);
        if ($statusAlert['status'] !== 'ok') {
            $alerts[] = [
                'icon' => $statusAlert['status'] === 'overdue' ? '🚨' : '⏰',
                'title' => $statusAlert['status'] === 'overdue' ? 'INTERVENTION DÉPASSÉE' : 'ÉCHÉANCE PROCHE',
                'message' => $statusAlert['message'],
                'priority' => $statusAlert['status'] === 'overdue' ? 'urgente' : 'haute',
                'action' => $statusAlert['status'] === 'overdue' ? 'Intervention immédiate requise' : 'Planifier sous 48h',
                'color' => $statusAlert['class'] === 'danger' ? '#a32d2d' : '#e67e22'
            ];
        }
        
        // 2. Alerte basée sur la priorité
        $priorite = $maintenance->getPriorite();
        if ($priorite === 'urgente') {
            $alerts[] = [
                'icon' => '🔴',
                'title' => 'PRIORITÉ URGENTE',
                'message' => 'Cette maintenance nécessite une intervention immédiate pour éviter une panne critique.',
                'priority' => 'urgente',
                'action' => 'Contacter un technicien d\'urgence',
                'color' => '#a32d2d'
            ];
        } elseif ($priorite === 'haute') {
            $alerts[] = [
                'icon' => '🟠',
                'title' => 'PRIORITÉ HAUTE',
                'message' => 'Planifier cette maintenance rapidement pour prévenir toute dégradation.',
                'priority' => 'haute',
                'action' => 'Planifier sous 7 jours',
                'color' => '#e67e22'
            ];
        }
        
        // 3. Alerte kilométrage
        $km = $maintenance->getKilometrage();
        if ($km && $km >= 15000) {
            $alerts[] = [
                'icon' => '⚠️',
                'title' => 'KILOMÉTRAGE CRITIQUE',
                'message' => "La machine a atteint {$km} km. Une révision majeure est obligatoire pour éviter la casse moteur.",
                'priority' => 'urgente',
                'action' => 'Révision générale immédiate',
                'color' => '#a32d2d'
            ];
        } elseif ($km && $km >= 10000) {
            $alerts[] = [
                'icon' => '📊',
                'title' => 'KILOMÉTRAGE ÉLEVÉ',
                'message' => "Kilométrage élevé ({$km} km). Maintenance préventive recommandée.",
                'priority' => 'haute',
                'action' => 'Vidange et contrôle général',
                'color' => '#e67e22'
            ];
        } elseif ($km && $km >= 5000) {
            $alerts[] = [
                'icon' => '📈',
                'title' => 'KILOMÉTRAGE INTERMÉDIAIRE',
                'message' => "Vidange et entretien courant recommandés à {$km} km.",
                'priority' => 'moyenne',
                'action' => 'Planifier vidange',
                'color' => '#f39c12'
            ];
        }
        
        // 4. Alerte spécifique au type de panne
        $typeAlert = $this->getTypeSpecificAlert($maintenance);
        if ($typeAlert) {
            $alerts[] = $typeAlert;
        }
        
        // 5. Alerte basée sur la date de dernière maintenance
        $dateMain = $maintenance->getDateMain();
        if ($dateMain) {
            $now = new \DateTime();
            $yearsSince = $now->diff($dateMain)->y;
            $daysSince = $now->diff($dateMain)->days;
            
            if ($yearsSince >= 2) {
                $alerts[] = [
                    'icon' => '🔴',
                    'title' => 'ALERTE MAJEURE - 2 ANS SANS MAINTENANCE',
                    'message' => "La dernière maintenance date de plus de 2 ans. Risque de défaillance sévère.",
                    'priority' => 'urgente',
                    'action' => 'Révision complète immédiate',
                    'color' => '#a32d2d'
                ];
            } elseif ($yearsSince >= 1) {
                $alerts[] = [
                    'icon' => '📅',
                    'title' => 'RAPPEL ANNUEL',
                    'message' => "Plus d'un an sans maintenance. Inspection vivement conseillée.",
                    'priority' => 'haute',
                    'action' => 'Planifier inspection',
                    'color' => '#e67e22'
                ];
            } elseif ($daysSince > 180) {
                $alerts[] = [
                    'icon' => '⏰',
                    'title' => 'MAINTENANCE À PRÉVOIR',
                    'message' => "Plus de 6 mois sans maintenance. La performance pourrait se dégrader.",
                    'priority' => 'moyenne',
                    'action' => 'Effectuer inspection',
                    'color' => '#f39c12'
                ];
            }
        }
        
        // 6. Génération d'alerte IA avancée (si pas assez d'alertes)
        if (count($alerts) < 2 && !empty($this->geminiApiKey)) {
            $iaAlert = $this->generateGeminiAlert($maintenance);
            if ($iaAlert) {
                $alerts[] = $iaAlert;
            }
        }
        
        // 7. Alerte par défaut si aucune alerte
        if (empty($alerts)) {
            $alerts[] = [
                'icon' => '✅',
                'title' => 'MAINTENANCE À JOUR',
                'message' => 'Aucune alerte critique. Surveillance normale recommandée.',
                'priority' => 'faible',
                'action' => 'Programmer prochaine révision dans 6 mois',
                'color' => '#2d6a2d'
            ];
        }
        
        return $alerts;
    }

    /**
     * Compte les alertes par niveau de criticité
     */
    public function countAlertsByLevel(Maintenance $maintenance): array
    {
        $alerts = $this->generateIntelligentAlerts($maintenance);
        return [
            'urgente' => count(array_filter($alerts, fn($a) => $a['priority'] === 'urgente')),
            'haute' => count(array_filter($alerts, fn($a) => $a['priority'] === 'haute')),
            'moyenne' => count(array_filter($alerts, fn($a) => $a['priority'] === 'moyenne')),
            'faible' => count(array_filter($alerts, fn($a) => $a['priority'] === 'faible')),
            'total' => count($alerts)
        ];
    }

    /**
     * Vérifie si des alertes critiques existent
     */
    public function hasCriticalAlerts(Maintenance $maintenance): bool
    {
        $alerts = $this->generateIntelligentAlerts($maintenance);
        return !empty(array_filter($alerts, fn($a) => in_array($a['priority'], ['urgente', 'haute'])));
    }

    /**
     * Génère un résumé textuel des alertes
     */
    public function getAlertSummary(Maintenance $maintenance): string
    {
        $alerts = $this->generateIntelligentAlerts($maintenance);
        $critical = array_filter($alerts, fn($a) => in_array($a['priority'], ['urgente', 'haute']));
        
        if (empty($critical)) {
            return "✅ Aucune alerte critique";
        }
        
        $summary = "⚠️ " . count($critical) . " alerte(s) : ";
        foreach ($critical as $alert) {
            $summary .= " • {$alert['title']}";
        }
        return $summary;
    }

    /**
     * Génère une description enrichie + recommandations via API
     */
    public function generateEnhancedContent(Maintenance $maintenance): array
    {
        $type = $maintenance->getTypePanne();
        $priorite = $maintenance->getPriorite();
        $statut = $maintenance->getStatut();
        $km = $maintenance->getKilometrage();
        $nomMachine = $this->getMachineName($maintenance);

        $prompt = "Tu es un expert en maintenance agricole. Pour une intervention de type '{$type}' (priorité {$priorite}, statut {$statut}, kilométrage {$km} km) sur la machine '{$nomMachine}', génère :
        - Une **description détaillée** des actions à réaliser (max 150 mots).
        - Une **liste de recommandations** (3 points) : pièces à vérifier, tests à faire, niveau d'urgence.
        - Un **score de criticité** (1-10).
        Format de réponse : 
        DESCRIPTION: [texte]
        RECOMMANDATIONS: - point1\n- point2\n- point3
        CRITICITE: [X/10]";

        $content = $this->callGeminiApi($prompt);
        if (!$content) {
            if ($this->logger) {
                $this->logger->warning('Gemini API non disponible, utilisation du fallback local');
            }
            $content = $this->getFallbackContent($type, $priorite, $km, $nomMachine);
        }

        $description = "Maintenance {$type} sur {$nomMachine}";
        $recommandations = "Suivre le planning préventif.";
        $criticite = 5;

        if (preg_match('/DESCRIPTION:\s*(.+?)(?=RECOMMANDATIONS:|$)/s', $content, $descMatch)) {
            $description = trim($descMatch[1]);
        }
        if (preg_match('/RECOMMANDATIONS:\s*(.+?)(?=CRITICITE:|$)/s', $content, $recMatch)) {
            $recommandations = trim($recMatch[1]);
        }
        if (preg_match('/CRITICITE:\s*(\d+)/', $content, $critMatch)) {
            $criticite = (int) $critMatch[1];
        }

        return [
            'description' => $description,
            'recommandation' => $recommandations,
            'criticite' => $criticite,
            'raw' => $content
        ];
    }

    /**
     * Génère une alerte via l'API Gemini
     */
    private function generateGeminiAlert(Maintenance $maintenance): ?array
    {
        if (empty($this->geminiApiKey)) {
            return null;
        }
        
        $type = $maintenance->getTypePanne() ?? 'Général';
        $nomMachine = $this->getMachineName($maintenance);
        $km = $maintenance->getKilometrage() ?? 0;
        
        $prompt = "Génère une alerte de maintenance agricole en français pour: Type={$type}, Machine={$nomMachine}, Km={$km}. Format JSON: {\"title\":\"titre alerte\",\"message\":\"message alerte\",\"action\":\"action recommandée\"}";
        
        try {
            $response = $this->httpClient->request('POST', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key=' . $this->geminiApiKey, [
                'json' => [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => ['temperature' => 0.7, 'maxOutputTokens' => 300]
                ],
                'timeout' => 15
            ]);
            
            $data = $response->toArray();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            
            if (preg_match('/\{[^}]+\}/', $text, $matches)) {
                $alertData = json_decode($matches[0], true);
                if ($alertData && isset($alertData['title'], $alertData['message'])) {
                    return [
                        'icon' => '🤖',
                        'title' => $alertData['title'],
                        'message' => $alertData['message'],
                        'priority' => 'moyenne',
                        'action' => $alertData['action'] ?? 'Consulter technicien',
                        'color' => '#534AB7'
                    ];
                }
            }
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Erreur Gemini alert', ['error' => $e->getMessage()]);
            }
        }
        
        return null;
    }

    /**
     * Alerte spécifique au type de panne
     */
    private function getTypeSpecificAlert(Maintenance $maintenance): ?array
    {
        $type = mb_strtolower($maintenance->getTypePanne() ?? '');
        $nomMachine = $this->getMachineName($maintenance);
        
        if (str_contains($type, 'moteur')) {
            return [
                'icon' => '🔧',
                'title' => 'ALERTE MOTEUR',
                'message' => "Problème moteur sur {$nomMachine}. Risque de surchauffe ou perte de puissance.",
                'priority' => 'haute',
                'action' => 'Diagnostic moteur immédiat',
                'color' => '#e67e22'
            ];
        }
        
        if (str_contains($type, 'hydraulique')) {
            return [
                'icon' => '💧',
                'title' => 'RISQUE HYDRAULIQUE',
                'message' => "Circuit hydraulique à surveiller sur {$nomMachine}. Baisse de pression possible.",
                'priority' => 'haute',
                'action' => 'Vérifier flexibles et raccords',
                'color' => '#e67e22'
            ];
        }
        
        if (str_contains($type, 'electrique') || str_contains($type, 'électrique')) {
            return [
                'icon' => '⚡',
                'title' => 'ALERTE ÉLECTRIQUE',
                'message' => "Circuit électrique à contrôler sur {$nomMachine}. Risque de dysfonctionnement.",
                'priority' => 'moyenne',
                'action' => 'Diagnostic électrique',
                'color' => '#f39c12'
            ];
        }
        
        if (str_contains($type, 'transmission')) {
            return [
                'icon' => '🔄',
                'title' => 'USURE TRANSMISSION',
                'message' => "Transmission à vérifier sur {$nomMachine}. Bruits anormaux possibles.",
                'priority' => 'haute',
                'action' => 'Contrôle boîte de vitesses',
                'color' => '#e67e22'
            ];
        }
        
        return null;
    }

    /**
     * Appel à l'API Gemini
     */
    private function callGeminiApi(string $prompt): ?string
    {
        if (empty($this->geminiApiKey)) {
            return null;
        }

        try {
            $response = $this->httpClient->request('POST', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key=' . $this->geminiApiKey, [
                'json' => [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 800,
                        'topP' => 0.9,
                        'topK' => 40
                    ],
                    'safetySettings' => [
                        ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                        ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                        ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                        ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE']
                    ]
                ],
                'timeout' => 30
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                if ($this->logger) {
                    $this->logger->error('Erreur API Gemini', ['status_code' => $statusCode]);
                }
                return null;
            }

            $data = $response->toArray();
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Exception lors de l\'appel Gemini', ['error' => $e->getMessage()]);
            }
            return null;
        }
    }

    /**
     * Contenu de fallback local
     */
    private function getFallbackContent(string $type, string $priorite, ?int $km, string $nomMachine): string
    {
        $baseDesc = match($type) {
            'Mécanique' => "Vérifier l'usure des pièces mobiles, contrôler les jeux et lubrifier les roulements. Inspecter les courroies et chaînes de transmission.",
            'Électricité' => "Tester la batterie, l'alternateur et les faisceaux. Rechercher des courts-circuits et vérifier le fonctionnement des capteurs.",
            'Hydraulique' => "Contrôler le niveau d'huile, détecter les fuites, tester la pression du circuit. Vérifier l'état des flexibles et raccords.",
            'Moteur' => "Analyse des compressions, contrôle du refroidissement, vidange et changement des filtres. Vérifier le système d'injection.",
            'Vidange & filtres' => "Effectuer la vidange moteur, remplacer le filtre à huile, filtre à air et filtre à gasoil. Contrôler le niveau des fluides.",
            'Transmission' => "Contrôler l'embrayage, la boîte de vitesses et les différentiels. Vérifier les niveaux d'huile et l'absence de bruits anormaux.",
            'Pneumatique' => "Vérifier la pression des pneus, l'usure de la bande de roulement, l'équilibrage et la géométrie.",
            'Révision générale' => "Inspection complète de tous les systèmes : moteur, transmission, hydraulique, électricité, pneumatique. Vidange générale et réglages.",
            default => "Inspection générale et entretien préventif selon les recommandations constructeur. Vérifier les niveaux et l'état général."
        };

        if ($priorite === 'urgente') {
            $baseDesc .= " INTERVENTION URGENTE REQUISE. La machine ne doit pas être utilisée avant réparation.";
        } elseif ($priorite === 'haute') {
            $baseDesc .= " À réaliser dès que possible pour éviter une panne majeure.";
        } elseif ($priorite === 'moyenne') {
            $baseDesc .= " À planifier dans les prochaines semaines.";
        }

        $rec = [];
        $rec[] = "- Vérifier les niveaux et l'absence de fuites avant toute intervention.";
        
        if ($km && $km > 5000) {
            $rec[] = "- Vidange moteur et changement des filtres recommandés (kilométrage > 5000 km).";
        }
        if ($km && $km > 10000) {
            $rec[] = "- Révision générale nécessaire (kilométrage > 10000 km).";
        }
        
        $rec[] = "- Tester le fonctionnement à vide avant remise en service.";
        $rec[] = "- Documenter toutes les interventions dans le carnet de maintenance.";
        
        $criticite = match($priorite) {
            'urgente' => 9,
            'haute' => 7,
            'moyenne' => 5,
            'faible' => 3,
            default => 5
        };
        
        if ($km && $km > 15000) $criticite = min(10, $criticite + 2);

        return "DESCRIPTION: {$baseDesc}\nRECOMMANDATIONS:\n" . implode("\n", $rec) . "\nCRITICITE: {$criticite}/10";
    }

    /**
     * Récupère le nom de la machine
     */
    private function getMachineName(Maintenance $m): string
    {
        if (method_exists($m, 'getNomMateriel') && $m->getNomMateriel()) {
            return $m->getNomMateriel();
        }
        if (method_exists($m, 'getNom') && $m->getNom()) {
            return $m->getNom();
        }
        if (method_exists($m, 'getIdM') && $m->getIdM()) {
            return "Machine #{$m->getIdM()}";
        }
        return 'Machine';
    }
}