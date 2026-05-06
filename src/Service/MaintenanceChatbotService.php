<?php
// src/Service/MaintenanceChatbotService.php

namespace App\Service;

use App\Entity\Materiels\Maintenance;
use App\Repository\Materiels\MaintenanceRepository;
use App\Repository\Materiels\MachineRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class MaintenanceChatbotService
{
    private const GEMINI_API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';

    private const PARTS_CATALOG = [
        'Mécanique' => [
            ['name' => 'Kit joint de culasse',   'ref' => 'JC-MEC-001', 'prix' => '120-250 DT', 'compatibilite' => 'Moteurs diesel 3-6 cylindres', 'priorite' => 'critique'],
            ['name' => 'Pompe à eau',             'ref' => 'PE-MEC-002', 'prix' => '90-180 DT',  'compatibilite' => 'Refroidissement liquide',       'priorite' => 'haute'],
            ['name' => 'Thermostat',              'ref' => 'TH-MEC-003', 'prix' => '20-40 DT',   'compatibilite' => 'Circuits standard',             'priorite' => 'moyenne'],
            ['name' => "Courroie d'accessoires",  'ref' => 'CA-MEC-004', 'prix' => '25-50 DT',   'compatibilite' => 'Selon modèle',                  'priorite' => 'moyenne'],
        ],
        'Électricité' => [
            ['name' => 'Batterie 12V/100Ah',   'ref' => 'BAT-ELEC-001', 'prix' => '180-280 DT', 'compatibilite' => 'Tracteurs 12V',        'priorite' => 'critique'],
            ['name' => 'Alternateur 12V/80A',  'ref' => 'ALT-ELEC-002', 'prix' => '120-250 DT', 'compatibilite' => 'Systèmes 12V',         'priorite' => 'haute'],
            ['name' => 'Démarreur électrique', 'ref' => 'DEM-ELEC-003', 'prix' => '150-300 DT', 'compatibilite' => 'Moteurs diesel',       'priorite' => 'critique'],
            ['name' => 'Faisceau électrique',  'ref' => 'FAI-ELEC-004', 'prix' => '200-400 DT', 'compatibilite' => 'Selon marque/modèle',  'priorite' => 'moyenne'],
            ['name' => 'Relais de démarrage',  'ref' => 'REL-ELEC-005', 'prix' => '15-30 DT',   'compatibilite' => 'Universel 12V',        'priorite' => 'moyenne'],
        ],
        'Hydraulique' => [
            ['name' => 'Pompe hydraulique',       'ref' => 'PH-HYD-001', 'prix' => '350-800 DT', 'compatibilite' => 'Circuit haute pression',     'priorite' => 'critique'],
            ['name' => 'Vérin hydraulique',       'ref' => 'VE-HYD-002', 'prix' => '200-500 DT', 'compatibilite' => 'Systèmes de levage',         'priorite' => 'haute'],
            ['name' => 'Flexible haute pression', 'ref' => 'FX-HYD-003', 'prix' => '40-120 DT',  'compatibilite' => "Jusqu'à 300 bars",           'priorite' => 'haute'],
            ['name' => 'Filtre hydraulique',      'ref' => 'FH-HYD-004', 'prix' => '30-60 DT',   'compatibilite' => 'Circuits standards',         'priorite' => 'moyenne'],
            ['name' => 'Vanne de contrôle',       'ref' => 'VC-HYD-005', 'prix' => '80-200 DT',  'compatibilite' => 'Distributeurs hydrauliques', 'priorite' => 'moyenne'],
        ],
        'Logicielle' => [
            ['name' => 'Mise à jour calculateur', 'ref' => 'MAJ-LOG-001', 'prix' => '150-300 DT', 'compatibilite' => 'Selon modèle',      'priorite' => 'haute'],
            ['name' => 'Câble diagnostic OBD',    'ref' => 'CAB-LOG-002', 'prix' => '50-120 DT',  'compatibilite' => 'Prise OBD standard', 'priorite' => 'moyenne'],
            ['name' => 'Logiciel de diagnostic',  'ref' => 'LOG-LOG-003', 'prix' => '200-500 DT', 'compatibilite' => 'PC Windows',         'priorite' => 'moyenne'],
        ],
        'Transmission' => [
            ['name' => 'Embrayage complet',         'ref' => 'EM-TRA-001', 'prix' => '300-600 DT', 'compatibilite' => 'Boîtes manuelles',        'priorite' => 'critique'],
            ['name' => "Disque d'embrayage",        'ref' => 'DI-TRA-002', 'prix' => '120-250 DT', 'compatibilite' => 'Embrayage à friction',    'priorite' => 'haute'],
            ['name' => 'Synchro boîte',             'ref' => 'SY-TRA-003', 'prix' => '80-180 DT',  'compatibilite' => 'Boîtes 4/8/12 rapports', 'priorite' => 'haute'],
            ['name' => 'Kit révision boîte',        'ref' => 'KR-TRA-004', 'prix' => '200-400 DT', 'compatibilite' => 'Selon modèle',           'priorite' => 'moyenne'],
            ['name' => 'Roulement de transmission', 'ref' => 'RT-TRA-005', 'prix' => '25-80 DT',   'compatibilite' => 'Arbres de transmission', 'priorite' => 'moyenne'],
        ],
        'Moteur' => [
            ['name' => 'Kit joint de culasse',    'ref' => 'JC-MOT-001',  'prix' => '120-250 DT',  'compatibilite' => 'Moteurs diesel 3-6 cylindres', 'priorite' => 'critique'],
            ['name' => 'Injecteur diesel',        'ref' => 'INJ-MOT-002', 'prix' => '150-400 DT',  'compatibilite' => 'Common rail / Pompe en ligne', 'priorite' => 'critique'],
            ['name' => "Pompe à injection",       'ref' => 'PIP-MOT-003', 'prix' => '400-900 DT',  'compatibilite' => 'Moteurs diesel',               'priorite' => 'critique'],
            ['name' => 'Turbocompresseur',        'ref' => 'TUR-MOT-004', 'prix' => '500-1200 DT', 'compatibilite' => 'Moteurs turbo diesel',         'priorite' => 'haute'],
            ['name' => 'Courroie de distribution','ref' => 'CD-MOT-005',  'prix' => '35-75 DT',    'compatibilite' => 'Selon modèle moteur',          'priorite' => 'haute'],
            ['name' => 'Filtre à gasoil',         'ref' => 'FG-MOT-006',  'prix' => '15-35 DT',    'compatibilite' => 'Universel',                    'priorite' => 'moyenne'],
        ],
        'Vidange & filtres' => [
            ['name' => 'Huile moteur 15W40 (20L)',       'ref' => 'HV-VID-001', 'prix' => '85-120 DT', 'compatibilite' => 'Moteurs diesel',          'priorite' => 'haute'],
            ['name' => "Filtre à huile",                 'ref' => 'FO-VID-002', 'prix' => '12-25 DT',  'compatibilite' => 'Selon modèle',            'priorite' => 'haute'],
            ['name' => "Filtre à air",                   'ref' => 'FA-VID-003', 'prix' => '25-50 DT',  'compatibilite' => "Système d'admission",     'priorite' => 'haute'],
            ['name' => 'Filtre à gasoil',                'ref' => 'FG-VID-004', 'prix' => '15-35 DT',  'compatibilite' => 'Universel',               'priorite' => 'haute'],
            ['name' => 'Huile hydraulique ISO 46 (20L)', 'ref' => 'HH-VID-005', 'prix' => '75-110 DT', 'compatibilite' => 'Circuits hydrauliques',   'priorite' => 'haute'],
            ['name' => 'Liquide de refroidissement (5L)','ref' => 'LR-VID-006', 'prix' => '35-55 DT',  'compatibilite' => 'Circuits refroidissement', 'priorite' => 'moyenne'],
        ],
        'Révision générale' => [
            ['name' => 'Kit révision complet', 'ref' => 'KIT-REV-001', 'prix' => '800-1500 DT', 'compatibilite' => 'Selon modèle',   'priorite' => 'critique'],
            ['name' => 'Kit distribution',     'ref' => 'KD-REV-002',  'prix' => '150-350 DT',  'compatibilite' => 'Moteurs diesel', 'priorite' => 'haute'],
            ['name' => 'Kit filtres complets', 'ref' => 'KF-REV-003',  'prix' => '60-120 DT',   'compatibilite' => 'Universel',      'priorite' => 'haute'],
        ],
        'Pneumatique' => [
            ['name' => 'Pneu avant 9.5R20',    'ref' => 'PN-PNE-001', 'prix' => '200-350 DT', 'compatibilite' => 'Essieu avant',       'priorite' => 'haute'],
            ['name' => 'Pneu arrière 18.4R38',  'ref' => 'PN-PNE-002', 'prix' => '450-750 DT', 'compatibilite' => 'Essieu arrière',     'priorite' => 'critique'],
            ['name' => "Chambre à air",         'ref' => 'CA-PNE-003', 'prix' => '80-150 DT',  'compatibilite' => 'Pneus avec chambre', 'priorite' => 'haute'],
            ['name' => 'Valve de roue',         'ref' => 'VA-PNE-004', 'prix' => '5-15 DT',    'compatibilite' => 'Tubeless',           'priorite' => 'faible'],
            ['name' => 'Jante acier',           'ref' => 'JA-PNE-005', 'prix' => '300-600 DT', 'compatibilite' => 'Selon modèle',      'priorite' => 'haute'],
        ],
        'Autre' => [
            ['name' => 'Kit de réparation générale',   'ref' => 'GEN-001',  'prix' => '100-200 DT', 'compatibilite' => 'Universel',           'priorite' => 'moyenne'],
            ['name' => "Pièce d'origine constructeur", 'ref' => 'CONS-002', 'prix' => 'Variable',   'compatibilite' => 'Selon marque/modèle', 'priorite' => 'haute'],
        ],
    ];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly MaintenanceRepository $maintenanceRepository,
        private readonly MachineRepository $machineRepository,
        #[Autowire(env: 'GEMINI_API_KEY')]
        private readonly string $geminiApiKey,
    ) {}

    public function chat(
        string  $message,
        array   $history     = [],
        ?int    $machineId   = null,
        ?string $machineName = null,
    ): array {
        $detectedType       = $this->detectPanneType($message);
        $compatibleParts    = $detectedType ? $this->getCompatibleParts($detectedType, $machineId) : [];
        $maintenanceHistory = $machineId    ? $this->getMaintenanceHistory($machineId)             : [];
        
        // Récupérer les informations détaillées de la machine si on a l'ID
        $machineDetails = null;
        if ($machineId) {
            $machine = $this->machineRepository->find($machineId);
            if ($machine) {
                $machineDetails = [
                    'nom'     => $machine->getNom(),
                    'marque'  => $machine->getMarque(),
                    'modele'  => $machine->getModele(),
                    'etat'    => $machine->getEtatM(),
                    'km'      => $machine->getKilometrage(),
                ];
            }
        }

        $aiResponse = $this->generateGeminiResponse(
            $message,
            $history,
            $detectedType,
            $compatibleParts,
            $maintenanceHistory,
            $machineName ?? $machineDetails['nom'] ?? null,
            $machineDetails,
        );

        return [
            'success'         => true,
            'message'         => $aiResponse,
            'detectedType'    => $detectedType,
            'compatibleParts' => $compatibleParts,
            'hasHistory'      => !empty($maintenanceHistory),
            'historyCount'    => count($maintenanceHistory),
        ];
    }

    public function getPanneTypes(): array
    {
        return array_keys(self::PARTS_CATALOG);
    }

    public function getPartsForType(string $type): array
    {
        return self::PARTS_CATALOG[$type] ?? self::PARTS_CATALOG['Autre'];
    }

    public function getMachines(): array
    {
        try {
            return $this->machineRepository->findAll();
        } catch (\Throwable) {
            return [];
        }
    }

    private function detectPanneType(string $message): ?string
    {
        $normalized = mb_strtolower($message);

        $keywords = [
            'Moteur'            => ['moteur', 'injecteur', 'turbo', 'fumée', 'fumee', 'surchauffe', 'compression', 'cylindre', 'culasse', 'vilebrequin', 'bielle', 'piston', 'segment', 'distribution'],
            'Électricité'       => ['électrique', 'electrique', 'batterie', 'alternateur', 'démarreur', 'demarreur', 'fusible', 'câblage', 'cablage', 'contact', 'ampoule', 'faisceau'],
            'Hydraulique'       => ['hydraulique', 'vérin', 'verin', 'flexible', 'relevage', 'distributeur', 'pompe hydraulique'],
            'Logicielle'        => ['logiciel', 'software', 'programme', 'electronique', 'électronique', 'obd', 'code erreur', 'capteur'],
            'Transmission'      => ['transmission', 'boîte de vitesse', 'boite de vitesse', 'embrayage', 'engrenage', 'synchro', 'différentiel', 'differentiel'],
            'Mécanique'         => ['mécanique', 'mecanique', 'bruit', 'grincement', 'claquement', 'soupape', 'courroie'],
            'Vidange & filtres' => ['vidange', 'filtre', 'huile moteur', 'gasoil', 'filtration', 'niveau huile', 'vidanger'],
            'Révision générale' => ['révision générale', 'revision generale', 'check-up', 'inspection complète', 'diagnostic complet'],
            'Pneumatique'       => ['pneu', 'pneumatique', 'crevaison', 'gonflage', 'pression pneu', 'chambre à air', 'jante'],
        ];

        foreach ($keywords as $type => $words) {
            foreach ($words as $word) {
                if (str_contains($normalized, $word)) {
                    return $type;
                }
            }
        }

        if (str_contains($normalized, 'panne') || str_contains($normalized, 'problème') || str_contains($normalized, 'probleme')) {
            return 'Autre';
        }

        return null;
    }

    private function getCompatibleParts(string $type, ?int $machineId): array
    {
        $parts = self::PARTS_CATALOG[$type] ?? self::PARTS_CATALOG['Autre'];

        if ($machineId !== null && $machineId > 0) {
            try {
                $machine = $this->machineRepository->find($machineId);
                if ($machine !== null) {
                    $brand    = mb_strtolower((string) $machine->getMarque());
                    $model    = mb_strtolower((string) $machine->getModele());
                    $name     = mb_strtolower((string) $machine->getNom());
                    
                    $filtered = array_values(array_filter(
                        $parts,
                        static fn(array $p): bool =>
                            str_contains(mb_strtolower($p['compatibilite']), $brand)
                            || str_contains(mb_strtolower($p['compatibilite']), $model)
                            || str_contains(mb_strtolower($p['compatibilite']), $name)
                            || str_contains(mb_strtolower($p['compatibilite']), 'universel'),
                    ));
                    if (!empty($filtered)) {
                        $parts = $filtered;
                    }
                }
            } catch (\Throwable) {
                // Non-bloquant
            }
        }

        $priorityOrder = ['critique' => 0, 'haute' => 1, 'moyenne' => 2, 'faible' => 3];
        usort($parts, static fn(array $a, array $b): int =>
            ($priorityOrder[$a['priorite']] ?? 4) <=> ($priorityOrder[$b['priorite']] ?? 4),
        );

        return array_slice($parts, 0, 6);
    }

    private function getMaintenanceHistory(int $machineId): array
    {
        try {
            $maintenances = $this->maintenanceRepository->findBy(
                ['idM' => $machineId],
                ['dateMain' => 'DESC'],
                5,
            );

            return array_map(static fn(Maintenance $m): array => [
                'id'          => $m->getIdMain(),
                'type'        => $m->getTypePanne(),
                'date'        => $m->getDateMain()?->format('d/m/Y') ?? 'N/A',
                'cout'        => $m->getCout(),
                'statut'      => $m->getStatut(),
                'priorite'    => $m->getPriorite(),
                'description' => $m->getDescription(),
                'recommendation' => $m->getRecommendation(),
            ], $maintenances);

        } catch (\Throwable) {
            return [];
        }
    }

    private function generateGeminiResponse(
        string  $message,
        array   $history,
        ?string $detectedType,
        array   $parts,
        array   $maintenanceHistory,
        ?string $machineName,
        ?array  $machineDetails,
    ): string {
        $systemPrompt = $this->buildSystemPrompt($detectedType, $parts, $maintenanceHistory, $machineName, $machineDetails);

        $contents = [];
        foreach ($history as $msg) {
            $role       = ($msg['role'] ?? 'user') === 'user' ? 'user' : 'model';
            $content    = is_string($msg['content'] ?? null) ? $msg['content'] : '';
            $contents[] = ['role' => $role, 'parts' => [['text' => $content]]];
        }
        $contents[] = ['role' => 'user', 'parts' => [['text' => $message]]];

        try {
            $response = $this->httpClient->request(
                'POST',
                self::GEMINI_API_URL . '?key=' . $this->geminiApiKey,
                [
                    'json' => [
                        'system_instruction' => ['parts' => [['text' => $systemPrompt]]],
                        'contents'           => $contents,
                        'generationConfig'   => [
                            'temperature'     => 0.7,
                            'maxOutputTokens' => 1500,
                            'topP'            => 0.95,
                            'topK'            => 40,
                        ],
                    ],
                    'timeout' => 30,
                ],
            );

            $data       = $response->toArray();
            $aiResponse = $data['candidates'][0]['content']['parts'][0]['text']
                ?? $this->fallbackResponse($detectedType, $parts, $machineName);

            return $this->formatResponse($aiResponse, $parts);

        } catch (\Throwable $e) {
            error_log(sprintf('[AgroBot][Gemini] %s — %s', $e::class, mb_substr($e->getMessage(), 0, 200)));
            return $this->fallbackResponse($detectedType, $parts, $machineName);
        }
    }

    private function buildSystemPrompt(
        ?string $type,
        array   $parts,
        array   $maintenanceHistory,
        ?string $machineName,
        ?array  $machineDetails,
    ): string {
        $prompt = <<<PROMPT
Tu es AgroBot, un EXPERT en maintenance de matériel agricole pour AgroFlow en Tunisie.

## TON RÔLE :
1. Diagnostiquer PRÉCISÉMENT la panne décrite par l'agriculteur
2. Expliquer CLAIREMENT la cause de la panne
3. Proposer une SOLUTION DÉTAILLÉE étape par étape
4. Lister les PIÈCES DÉTACHÉES nécessaires (utilise le catalogue fourni)
5. Donner des CONSEILS DE PRÉVENTION

## FORMAT DE RÉPONSE OBLIGATOIRE :

🔍 **DIAGNOSTIC**
[Description précise du problème]

⚠️ **CAUSES POSSIBLES**
• Cause 1 : [explication détaillée]
• Cause 2 : [explication détaillée]

🔧 **SOLUTION RECOMMANDÉE**
1. [Action à entreprendre]
2. [Action à entreprendre]
3. [Action à entreprendre]

📦 **PIÈCES NÉCESSAIRES**
[Liste des pièces avec références et prix en DT]

🛡️ **PRÉVENTION**
• [Conseil 1]
• [Conseil 2]

⚠️ **URGENCE** : [Critique/Haute/Moyenne/Faible]

## RÈGLES :
- Réponds UNIQUEMENT en FRANÇAIS
- Sois PRÉCIS mais COMPRÉHENSIBLE (l'utilisateur est agriculteur)
- Prix réalistes pour le marché tunisien (Dinars Tunisiens)
- Mentionne les RISQUES si la panne n'est pas traitée rapidement
- N'invente PAS d'informations techniques
PROMPT;

        if ($machineName !== null && $machineName !== '') {
            $prompt .= "\n\n🚜 MATÉRIEL CONCERNÉ : {$machineName}";
        }
        
        if ($machineDetails !== null) {
            $prompt .= "\n\n📋 DÉTAILS DU MATÉRIEL :";
            if (!empty($machineDetails['marque'])) $prompt .= "\n- Marque : {$machineDetails['marque']}";
            if (!empty($machineDetails['modele'])) $prompt .= "\n- Modèle : {$machineDetails['modele']}";
            if (!empty($machineDetails['km'])) $prompt .= "\n- Kilométrage actuel : {$machineDetails['km']} km";
            $prompt .= "\n\n👉 ADAPTE TES RECOMMANDATIONS DE PIÈCES EN FONCTION DE LA MARQUE ET DU MODÈLE CI-DESSUS !";
        }

        if ($type !== null) {
            $prompt .= "\n\n🔧 TYPE DE PANNE DÉTECTÉ : {$type}";
        }

        if (!empty($parts)) {
            $prompt .= "\n\n📦 CATALOGUE PIÈCES DISPONIBLES (prix Tunisie) :";
            foreach ($parts as $p) {
                $prompt .= "\n- {$p['name']} (Réf: {$p['ref']}) - Prix: {$p['prix']} - Priorité: {$p['priorite']} - Compatibilité: {$p['compatibilite']}";
            }
            $prompt .= "\n\nUtilise CES PIÈCES EXACTES dans ta réponse pour la section PIÈCES NÉCESSAIRES.";
        }

        if (!empty($maintenanceHistory)) {
            $prompt .= "\n\n📋 HISTORIQUE (5 dernières interventions) :";
            foreach ($maintenanceHistory as $h) {
                $prompt .= "\n- {$h['date']} : {$h['type']} — Statut: {$h['statut']} — Coût: {$h['cout']} DT";
                if (!empty($h['recommendation'])) {
                    $prompt .= "\n  Recommandation précédente: {$h['recommendation']}";
                }
            }
            $prompt .= "\n\nPrends en compte cet historique pour éviter de répéter les mêmes interventions et pour assurer un suivi cohérent.";
        }

        return $prompt;
    }

    private function formatResponse(string $response, array $parts): string
    {
        if (str_contains($response, '🔍') || str_contains($response, 'DIAGNOSTIC')) {
            return $response;
        }

        if (!empty($parts) && !str_contains($response, 'PIÈCES')) {
            $response .= "\n\n📦 **PIÈCES NÉCESSAIRES** :\n";
            foreach ($parts as $p) {
                $emoji     = match ($p['priorite']) {
                    'critique' => '🔴',
                    'haute'    => '🟠',
                    default    => '🟢',
                };
                $response .= "\n{$emoji} **{$p['name']}**\n";
                $response .= "   • Référence : {$p['ref']}\n";
                $response .= "   • Prix : {$p['prix']}\n";
                $response .= "   • Compatibilité : {$p['compatibilite']}\n";
            }
        }

        return $response;
    }

    private function fallbackResponse(?string $type, array $parts, ?string $machineName = null): string
    {
        $machineInfo = $machineName ? " sur votre {$machineName}" : "";
        
        if ($type === null) {
            return <<<TXT
🔍 **DIAGNOSTIC EN ATTENTE**

Je n'ai pas pu identifier précisément votre panne{$machineInfo}.

📝 **Pouvez-vous me donner plus de détails ?**
• Quel bruit entendez-vous ?
• Y a-t-il de la fumée ? De quelle couleur ?
• Depuis quand le problème persiste-t-il ?
• Quels voyants sont allumés au tableau de bord ?

💡 Plus vous serez précis, plus mon diagnostic sera fiable.
TXT;
        }

        $causes = [
            'Mécanique'         => "• Usure mécanique des pièces mobiles\n• Défaut de lubrification\n• Pièce desserrée ou mal fixée\n• Jeu anormal dans les roulements",
            'Électricité'       => "• Batterie faible ou déchargée\n• Alternateur défaillant\n• Fusible grillé ou câble oxydé\n• Démarreur usé ou bloqué",
            'Hydraulique'       => "• Niveau d'huile hydraulique insuffisant\n• Fuite sur flexible ou joint\n• Pompe hydraulique usée\n• Filtre hydraulique obstrué",
            'Logicielle'        => "• Mise à jour du calculateur nécessaire\n• Capteur défectueux\n• Erreur de paramétrage\n• Problème de communication OBD",
            'Transmission'      => "• Embrayage usé\n• Niveau d'huile de boîte bas\n• Synchros fatigués\n• Roulements endommagés",
            'Moteur'            => "• Filtres encrassés\n• Injecteurs obstrués\n• Compression basse\n• Turbocompresseur défaillant",
            'Vidange & filtres' => "• Huile usée ou niveau bas\n• Filtre à huile saturé\n• Filtre à air obstrué\n• Filtre à gasoil colmaté",
            'Révision générale' => "• Entretien préventif non réalisé\n• Usure normale des composants\n• Manque de contrôle régulier",
            'Pneumatique'       => "• Pression insuffisante\n• Crevaison ou fuite lente\n• Usure anormale des pneus\n• Jante endommagée",
            'Autre'             => "• Panne non identifiée\n• Problème nécessitant un diagnostic approfondi\n• Consultez un technicien spécialisé",
        ];

        $urgenceLevels = [
            'Moteur'            => 'Haute — Intervention sous 48 h',
            'Électricité'       => 'Haute — Intervention sous 48 h',
            'Hydraulique'       => 'Haute — Intervention sous 72 h',
            'Transmission'      => 'Moyenne — Planifier sous 7 jours',
            'Mécanique'         => 'Moyenne — Planifier sous 7 jours',
            'Logicielle'        => 'Faible — Programmer un diagnostic',
            'Vidange & filtres' => "Faible — Programmer l'entretien",
            'Révision générale' => 'Faible — Planifier la maintenance',
            'Pneumatique'       => 'Haute — Vérifier immédiatement',
            'Autre'             => 'Moyenne — Faire vérifier par un technicien',
        ];

        $causeText   = $causes[$type]        ?? $causes['Autre'];
        $urgenceText = $urgenceLevels[$type]  ?? 'Moyenne — Faire vérifier rapidement';

        $response  = "🔍 **DIAGNOSTIC : Panne de type {$type}{$machineInfo}**\n\n";
        $response .= "⚠️ **CAUSES POSSIBLES**\n{$causeText}\n\n";
        $response .= "🔧 **SOLUTION RECOMMANDÉE**\n";
        $response .= "1. Effectuer un contrôle visuel complet\n";
        $response .= "2. Vérifier les points mentionnés ci-dessus\n";
        $response .= "3. Remplacer les pièces défectueuses\n";
        $response .= "4. Tester le fonctionnement après intervention\n\n";

        if (!empty($parts)) {
            $response .= "📦 **PIÈCES NÉCESSAIRES**\n";
            foreach ($parts as $p) {
                $emoji     = match ($p['priorite']) {
                    'critique' => '🔴',
                    'haute'    => '🟠',
                    default    => '🟢',
                };
                $response .= "\n{$emoji} **{$p['name']}** — {$p['prix']}\n";
                $response .= "   • Réf : {$p['ref']}\n";
                $response .= "   • Compatible : {$p['compatibilite']}\n";
            }
            $response .= "\n";
        }

        $response .= "🛡️ **PRÉVENTION**\n";
        $response .= "• Respectez les intervalles de maintenance préventive\n";
        $response .= "• Utilisez des pièces de qualité d'origine\n";
        $response .= "• Faites des contrôles réguliers\n\n";
        $response .= "⚠️ **URGENCE** : {$urgenceText}";

        return $response;
    }
}