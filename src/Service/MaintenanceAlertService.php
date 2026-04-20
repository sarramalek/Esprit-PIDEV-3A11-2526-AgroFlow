<?php

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
        $daysLeft = (int) $interval->format('%r%a'); // jours restants (négatif si dépassé)

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
     * Génère une description enrichie + recommandations via API (Gemini ou fallback)
     */
    public function generateEnhancedContent(Maintenance $maintenance): array
    {
        $type = $maintenance->getTypePanne();
        $priorite = $maintenance->getPriorite();
        $statut = $maintenance->getStatut();
        $km = $maintenance->getKilometrage();
        $nomMachine = $maintenance->getNom() ?? 'Machine';

        // 1. Construction du prompt
        $prompt = "Tu es un expert en maintenance agricole. Pour une intervention de type '{$type}' (priorité {$priorite}, statut {$statut}, kilométrage {$km} km) sur la machine '{$nomMachine}', génère :
        - Une **description détaillée** des actions à réaliser (max 150 mots).
        - Une **liste de recommandations** (3 points) : pièces à vérifier, tests à faire, niveau d'urgence.
        - Un **score de criticité** (1-10).
        Format de réponse : 
        DESCRIPTION: [texte]
        RECOMMANDATIONS: - point1\n- point2\n- point3
        CRITICITE: [X/10]";

        // 2. Appel à Gemini (si clé dispo) sinon fallback local
        $content = $this->callGeminiApi($prompt);
        if (!$content) {
            if ($this->logger) {
                $this->logger->warning('Gemini API non disponible, utilisation du fallback local');
            }
            $content = $this->getFallbackContent($type, $priorite, $km, $nomMachine);
        }

        // 3. Parser la réponse
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

    private function callGeminiApi(string $prompt): ?string
    {
        if (empty($this->geminiApiKey)) {
            if ($this->logger) {
                $this->logger->warning('Clé API Gemini non configurée');
            }
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

    private function getFallbackContent(string $type, string $priorite, ?int $km, string $nomMachine): string
    {
        // Description selon le type de maintenance (plus complète)
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

        // Ajout de la priorité
        if ($priorite === 'urgente') {
            $baseDesc .= " INTERVENTION URGENTE REQUISE. La machine ne doit pas être utilisée avant réparation.";
        } elseif ($priorite === 'haute') {
            $baseDesc .= " À réaliser dès que possible pour éviter une panne majeure.";
        } elseif ($priorite === 'moyenne') {
            $baseDesc .= " À planifier dans les prochaines semaines.";
        }

        // Recommandations
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
        
        // Criticité
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
}