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
        $apiKey = $_ENV['GROQ_API_KEY_ROTATION'] ?? '';
        if (!$apiKey) {
            return $this->json(['error' => 'Clé API Groq manquante.'], 500);
        }

        $data       = json_decode($request->getContent(), true);
        $nomPlante  = $data['nomPlante']  ?? 'plante inconnue';
        $cycleJours = $data['cycleJours'] ?? 0;
        $locale     = $data['locale']     ?? 'fr'; // ← récupérer la locale

        // Mapper la locale vers la langue du prompt
        $langMap = [
            'fr' => 'français',
            'en' => 'English',
            'ar' => 'Arabic',
        ];
        $langue = $langMap[$locale] ?? 'français';

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
                            'content' => sprintf(
                                'You are an expert agronomist. Respond ONLY in valid JSON, no surrounding text, no backticks. ALL text values (titre, description, duree) MUST be written in %s.',
                                $langue
                            ),
                        ],
                        [
                            'role'    => 'user',
                            'content' => sprintf(
                                'Generate the complete cultivation workflow for the plant "%s"%s.

Each step must have a type from: prep, semis, croissance, entretien, recolte.

Respond ONLY in valid JSON with exactly this format:
{
  "steps": [
    {
      "titre": "...",
      "description": "...",
      "duree": "...",
      "type": "prep"
    }
  ]
}

ALL text must be in %s. Generate between 5 and 7 realistic and detailed steps.',
                                $nomPlante,
                                $cycleJours > 0 ? " (cycle of {$cycleJours} days)" : '',
                                $langue
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