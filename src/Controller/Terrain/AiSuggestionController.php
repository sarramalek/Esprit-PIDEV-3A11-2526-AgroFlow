<?php

namespace App\Controller\Terrain;

use App\Repository\Terrain\TerrainRepository;
use App\Repository\Terrain\PlanteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AiSuggestionController extends AbstractController
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private TerrainRepository   $terrainRepository,
        private PlanteRepository    $planteRepository,
    ) {}

    #[Route('/api/ai-suggestion', name: 'api_ai_suggestion', methods: ['POST'])]
    public function suggest(Request $request): JsonResponse
    {
        $apiKey = $_ENV['GROQ_API_KEY'] ?? '';
        if (!$apiKey) {
            return $this->json(['error' => 'Clé API Groq manquante.'], 500);
        }

        // Récupérer toutes les plantes et terrains depuis la BDD
        $toutesPlantes = $this->planteRepository->findAll();
        $tousTerrains  = $this->terrainRepository->findAll();

        $listePlantes  = implode(', ', array_map(fn($p) => $p->getNomP(), $toutesPlantes));
        $listeTerrains = implode(', ', array_map(fn($t) => $t->getNomTerrain(), $tousTerrains));

        try {
            $response = $this->httpClient->request('POST', 'https://api.groq.com/openai/v1/chat/completions', [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $apiKey,
                ],
                'json' => [
                    'model'       => 'llama-3.3-70b-versatile',
                    'temperature' => 0.7,
                    'max_tokens'  => 1000,
                    'messages'    => [
                        [
                            'role'    => 'system',
                            'content' => 'Tu es un expert agronome. Réponds UNIQUEMENT en JSON valide, sans texte autour, sans backticks.',
                        ],
                        [
                            'role'    => 'user',
                            'content' => sprintf(
                                'Voici les données réelles d\'une exploitation agricole :

Terrains disponibles : %s
Plantes disponibles : %s

En te basant uniquement sur ces données, propose la meilleure rotation de culture complète.
Tu dois recommander :
- Le meilleur terrain (parmi la liste ci-dessus)
- La meilleure plante à cultiver (parmi la liste ci-dessus)
- Une durée optimale
- Des alternatives de plantes (parmi la liste ci-dessus)

Réponds UNIQUEMENT en JSON valide avec exactement ce format :
{
  "terrain_recommande": "nom exact d\'un terrain de la liste",
  "prochaine_culture": "nom exact d\'une plante de la liste",
  "conseil": "explication agronomique en 1-2 phrases claires",
  "raison": "pourquoi ce terrain et cette plante sont compatibles",
  "duree_mois": 6,
  "alternatives": ["plante1 de la liste", "plante2 de la liste"]
}',
                                $listeTerrains,
                                $listePlantes
                            ),
                        ],
                    ],
                ],
            ]);

            $body    = $response->toArray();
            $rawText = $body['choices'][0]['message']['content'] ?? '{}';
            $clean   = trim(preg_replace('/```json|```/', '', $rawText));
            $result  = json_decode($clean, true);

            if (!$result) {
                return $this->json(['error' => 'Réponse invalide de l\'IA.'], 500);
            }

            return $this->json($result);

        } catch (\Throwable $e) {
            return $this->json(['error' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }
}