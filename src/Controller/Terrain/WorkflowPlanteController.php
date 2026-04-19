<?php

namespace App\Controller\Terrain;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WorkflowPlanteController extends AbstractController
{
    public function __construct(private HttpClientInterface $httpClient) {}

    #[Route('/api/workflow-plante', name: 'api_workflow_plante', methods: ['POST'])]
    public function workflow(Request $request): JsonResponse
    {
        $apiKey = $_ENV['GROQ_API_KEY'] ?? '';
        if (!$apiKey) {
            return $this->json(['error' => 'Clé API Groq manquante.'], 500);
        }

        $data      = json_decode($request->getContent(), true);
        $nomPlante = $data['nomPlante'] ?? 'plante inconnue';
        $cycleJours = $data['cycleJours'] ?? 0;

        try {
            $response = $this->httpClient->request('POST', 'https://api.groq.com/openai/v1/chat/completions', [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $apiKey,
                ],
                'json' => [
                    'model'       => 'llama-3.3-70b-versatile',
                    'temperature' => 0.5,
                    'max_tokens'  => 1500,
                    'messages'    => [
                        [
                            'role'    => 'system',
                            'content' => 'Tu es un expert agronome. Réponds UNIQUEMENT en JSON valide, sans texte autour, sans backticks.',
                        ],
                        [
                            'role'    => 'user',
                            'content' => sprintf(
                                'Génère le workflow complet des étapes de culture pour la plante "%s"%s.

Chaque étape doit avoir un type parmi : prep, semis, croissance, entretien, recolte.

Réponds UNIQUEMENT en JSON valide avec exactement ce format :
{
  "steps": [
    {
      "titre": "Préparation du sol",
      "description": "Labourer et ameublir le sol en profondeur. Ajouter du compost si nécessaire.",
      "duree": "1-2 semaines",
      "type": "prep"
    },
    {
      "titre": "Semis",
      "description": "Semer les graines à 2-3 cm de profondeur, espacées de 30 cm.",
      "duree": "Jour J",
      "type": "semis"
    }
  ]
}

Génère entre 5 et 7 étapes réalistes et détaillées pour cette plante.',
                                $nomPlante,
                                $cycleJours > 0 ? " (cycle de {$cycleJours} jours)" : ''
                            ),
                        ],
                    ],
                ],
            ]);

            $body    = $response->toArray();
            $rawText = $body['choices'][0]['message']['content'] ?? '{}';
            $clean   = trim(preg_replace('/```json|```/', '', $rawText));
            $result  = json_decode($clean, true);

            if (!$result || empty($result['steps'])) {
                return $this->json(['error' => 'Réponse invalide de l\'IA.'], 500);
            }

            return $this->json($result);

        } catch (\Throwable $e) {
            return $this->json(['error' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }
}