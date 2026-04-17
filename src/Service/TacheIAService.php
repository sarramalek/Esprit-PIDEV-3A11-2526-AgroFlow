<?php

namespace App\Service;

use App\Repository\User\UserRepository;
use App\Repository\User\TacheRepository;
use App\Repository\Terrain\TerrainRepository;
use App\Repository\Materiels\MachineRepository;
use App\Repository\Animals\AnimauxRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TacheIAService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private UserRepository      $userRepo,
        private TacheRepository     $tacheRepo,
        private TerrainRepository   $terrainRepo,
        private MachineRepository   $machineRepo,
        private AnimauxRepository   $animauxRepo,
        private string              $groqApiKey
    ) {}

    public function genererSuggestion(int $cinAgriculteur): array
    {
        // ── 1. Terrains ──────────────────────────────────────────────────────
        $terrains     = $this->terrainRepo->findByAgriculteur($cinAgriculteur);
        $terrainsData = array_map(fn($t) => [
            'nom'          => $t->getNomTerrain(),
            'surface'      => $t->getSurface(),
            'localisation' => $t->getLocalisation(),
        ], $terrains);

        // ── 2. Ouvriers ──────────────────────────────────────────────────────
       // ── 2. Ouvriers ──────────────────────────────────────────────────────
$ouvriers = $this->userRepo->findOuvriersByAgriculteur($cinAgriculteur);

// Collecte des tâches actives par terrain (pour éviter les doublons inter-ouvriers)
$tachesParTerrain = []; // ['NomTerrain' => ['tache1', 'tache2', ...]]
foreach ($ouvriers as $o) {
    $nomTerrain = $o->getTerrain()?->getNomTerrain() ?? 'non assigné';
    foreach ($this->tacheRepo->findByAssignee($o) as $t) {
        if ($t->getEtat() !== 'terminée') {
            $tachesParTerrain[$nomTerrain][] = strtolower(trim($t->getNomTache()));
        }
    }
}

$ouvriersData = array_map(function ($o) use ($tachesParTerrain) {
    $nomTerrain    = $o->getTerrain()?->getNomTerrain() ?? 'non assigné';
    $taches        = $this->tacheRepo->findByAssignee($o);
    $tachesEnCours = array_filter($taches, fn($t) => $t->getEtat() !== 'terminée');
    return [
        'nom'            => $o->getNom() . ' ' . $o->getPrenom(),
        'terrain'        => $nomTerrain,
        'taches_actives' => count($tachesEnCours),
    ];
}, $ouvriers);

        // ── 3. Machines ──────────────────────────────────────────────────────
        $machines     = $this->machineRepo->findByCin($cinAgriculteur);
        $machinesData = array_map(fn($m) => [
            'nom'    => $m->getNom(),
            'marque' => $m->getMarque(),
            'modele' => $m->getModele(),
            'etat'   => $m->getEtatM(),
        ], $machines);

        // ── 4. Animaux ───────────────────────────────────────────────────────
        $user         = $this->userRepo->findByCin($cinAgriculteur);
        $animaux      = $user ? $this->animauxRepo->searchDashboard(null, 'id', 'DESC', $user) : [];
        $animauxData  = array_map(fn($a) => [
            'nom'    => $a->getNom(),
            'espece' => $a->getEspece(),
            'sexe'   => $a->getSexe(),
            'poids'  => $a->getPoids(),
        ], $animaux);

        // ── 5. Prompt ────────────────────────────────────────────────────────
        $today   = (new \DateTime())->format('Y-m-d');
        $rand    = rand(1, 9999);
        $saison  = $this->getSaison();

        $prompt  = "Tu es un expert en agriculture tunisienne. Date : {$today}. Saison : {$saison}. Seed: {$rand}.\n\n";

        $prompt .= "TERRAINS (" . count($terrainsData) . ") :\n"
                 . json_encode($terrainsData, JSON_UNESCAPED_UNICODE) . "\n\n";

        $prompt .= "OUVRIERS (" . count($ouvriersData) . ") :\n"
                 . json_encode($ouvriersData, JSON_UNESCAPED_UNICODE) . "\n\n";

        if (!empty($machinesData)) {
            $prompt .= "MACHINES/ÉQUIPEMENTS (" . count($machinesData) . ") :\n"
                     . json_encode($machinesData, JSON_UNESCAPED_UNICODE) . "\n\n";
        }

        if (!empty($animauxData)) {
            $prompt .= "ANIMAUX (" . count($animauxData) . ") :\n"
                     . json_encode($animauxData, JSON_UNESCAPED_UNICODE) . "\n\n";
        }

        // ── Catégorie forcée par rotation ────────────────────────────────────
        $categories = [];
        if (!empty($animauxData))  $categories[] = 'ANIMAUX';
        if (!empty($machinesData)) $categories[] = 'MACHINES';
        $categories[] = 'TERRAINS';
        $categorieForcee = $categories[$rand % count($categories)];
// ── Injection des tâches déjà existantes par terrain ─────────────────
if (!empty($tachesParTerrain)) {
    $prompt .= "TÂCHES DÉJÀ ASSIGNÉES PAR TERRAIN (à ne PAS reproduire) :\n";
    foreach ($tachesParTerrain as $terrain => $noms) {
        $unique = array_unique($noms);
        $prompt .= "- {$terrain} : " . implode(', ', $unique) . "\n";
    }
    $prompt .= "\n";
}
        $prompt .= "⚠️ CONSIGNE STRICTE : Tu DOIS proposer une tâche concernant UNIQUEMENT la catégorie « {$categorieForcee} ». ";
        $prompt .= "Ignore les autres catégories pour le choix de la tâche.\n\n";

        if ($categorieForcee === 'ANIMAUX') {
            $prompt .= "Propose une tâche liée aux animaux : vaccination, alimentation, soins vétérinaires, ";
            $prompt .= "pesée, traite, nettoyage des enclos, vermifugation, surveillance sanitaire, etc.\n\n";
        } elseif ($categorieForcee === 'MACHINES') {
            $prompt .= "Propose une tâche liée aux machines/équipements : révision, vidange, nettoyage, ";
            $prompt .= "réparation, vérification des pneus, graissage, contrôle des filtres, etc.\n\n";
        } else {
            $prompt .= "Propose une tâche liée aux terrains/cultures : labour, irrigation, désherbage, ";
            $prompt .= "fertilisation, semis, récolte, taille, traitement phytosanitaire, etc.\n\n";
        }

        $prompt .= "La tâche doit être urgente, variée et adaptée à la saison ({$saison}). ";
        $prompt .= "Ne répète pas toujours la même tâche.\n\n";

        $prompt .= "Réponds UNIQUEMENT en JSON valide sans markdown :\n";
        $prompt .= '{"nom_tache":"...","description":"...","priorite":"basse|normale|haute|urgente","etat":"à faire","date_echeancee":"YYYY-MM-DD","raison":"..."}';
        $prompt .= "\ndate_echeancee dans les 7 prochains jours. Description max 200 caractères. Raison max 150 caractères.";

        // ── 6. Appel Groq ────────────────────────────────────────────────────
        $response = $this->httpClient->request('POST', 'https://api.groq.com/openai/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->groqApiKey,
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'model'       => 'llama-3.3-70b-versatile',
                'temperature' => 0.9,
                'max_tokens'  => 300,
                'messages'    => [
                    [
                        'role'    => 'system',
                        'content' => 'Tu es un assistant agricole tunisien expert. Tu réponds UNIQUEMENT en JSON valide, sans texte autour, sans balises markdown.',
                    ],
                    [
                        'role'    => 'user',
                        'content' => $prompt,
                    ],
                ],
            ],
        ]);

        $data = $response->toArray();
        $text = $data['choices'][0]['message']['content'] ?? '{}';
        $text = preg_replace('/```json\s*|\s*```/', '', trim($text));

        $suggestion = json_decode($text, true);

        if (!$suggestion || !isset($suggestion['nom_tache'])) {
            return $this->fallback();
        }

        return $suggestion;
    }

    // ── Saison tunisienne ────────────────────────────────────────────────────
    private function getSaison(): string
    {
        $mois = (int) (new \DateTime())->format('n');
        return match (true) {
            $mois >= 3 && $mois <= 5  => 'printemps',
            $mois >= 6 && $mois <= 8  => 'été',
            $mois >= 9 && $mois <= 11 => 'automne',
            default                   => 'hiver',
        };
    }

    // ── Suggestion de secours ────────────────────────────────────────────────
    private function fallback(): array
    {
        return [
            'nom_tache'      => 'Inspection générale de l\'exploitation',
            'description'    => 'Vérifier l\'état des cultures, machines et animaux.',
            'priorite'       => 'normale',
            'etat'           => 'à faire',
            'date_echeancee' => (new \DateTime('+3 days'))->format('Y-m-d'),
            'raison'         => 'Suggestion par défaut.',
        ];
    }
}