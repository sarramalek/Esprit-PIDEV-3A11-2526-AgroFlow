<?php

namespace App\Controller\Terrain;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class WorkflowPlanteController extends AbstractController
{
    private const GROQ_ENDPOINT = 'https://api.groq.com/openai/v1/chat/completions';
    private const GROQ_MODEL    = 'llama-3.3-70b-versatile';

    private const LANG_MAP = [
        'fr' => 'français',
        'en' => 'English',
        'ar' => 'Arabic',
    ];

    public function __construct(private readonly HttpClientInterface $httpClient) {}

    #[Route('/api/workflow-plante', name: 'api_workflow_plante', methods: ['POST'])]
    public function workflow(Request $request): JsonResponse
    {
        // ✅ FIX 1: Use getenv() — more reliable than $_ENV on Railway
        // Even better: inject via Symfony DI (bind: $groqApiKey: '%env(GROQ_API_KEY_ROTATION)%')
        $apiKey = getenv('GROQ_API_KEY_ROTATION') ?: '';
        if (!$apiKey) {
            return $this->json(['error' => 'Clé API Groq manquante.'], 500);
        }

        $data = json_decode($request->getContent(), true);

        // ✅ FIX 2: Validate required input
        if (!is_array($data)) {
            return $this->json(['error' => 'Corps de requête invalide.'], 400);
        }

        $nomPlante  = trim($data['nomPlante']  ?? '');
        $cycleJours = (int) ($data['cycleJours'] ?? 0);
        $locale     = $data['locale'] ?? 'fr';

        if ($nomPlante === '') {
            return $this->json(['error' => 'Le champ nomPlante est requis.'], 400);
        }

        $langue = self::LANG_MAP[$locale] ?? 'français';

        try {
            $response = $this->httpClient->request('POST', self::GROQ_ENDPOINT, [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $apiKey,
                ],
                // ✅ FIX 3: Add a timeout — Groq can hang on Railway
                'timeout' => 30,
                'json' => [
                    'model'       => self::GROQ_MODEL,
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
                                'Generate the complete cultivation workflow for "%s"%s.
Each step must have a type from: prep, semis, croissance, entretien, recolte.
Respond ONLY with this JSON structure, no other text:
{"steps":[{"titre":"...","description":"...","duree":"...","type":"prep"}]}
ALL text must be in %s. Generate 5 to 7 realistic and detailed steps.',
                                $nomPlante,
                                $cycleJours > 0 ? " (cycle of {$cycleJours} days)" : '',
                                $langue
                            ),
                        ],
                    ],
                ],
            ]);

            $statusCode = $response->getStatusCode();

            // ✅ FIX 4: Distinguish 401/403/429 (auth/rate) from 500 (infra/IP block)
            if ($statusCode === 401 || $statusCode === 403) {
                return $this->json(['error' => 'Clé API Groq invalide ou non autorisée.'], 502);
            }
            if ($statusCode === 429) {
                return $this->json(['error' => 'Quota Groq dépassé. Réessayez dans quelques instants.'], 429);
            }
            if ($statusCode >= 500) {
                // Likely IP block on Railway — see debugging session
                return $this->json([
                    'error' => 'Groq API indisponible (erreur serveur). Vérifiez le whitelist IP Railway.',
                ], 502);
            }

            $body    = $response->toArray();
            $rawText = $body['choices'][0]['message']['content'] ?? '';

            if ($rawText === '') {
                return $this->json(['error' => 'Réponse vide de l\'IA.'], 502);
            }

            // ✅ FIX 5: Safer JSON extraction — handle code fences and leading/trailing junk
            $clean  = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($rawText));
            $result = json_decode($clean, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->json([
                    'error'   => 'Réponse IA non parsable.',
                    'raw'     => substr($rawText, 0, 200), // helps debugging
                    'details' => json_last_error_msg(),
                ], 502);
            }

            if (empty($result['steps']) || !is_array($result['steps'])) {
                return $this->json(['error' => 'Structure de réponse IA invalide.'], 502);
            }

            // ✅ FIX 6: Validate each step has required fields
            foreach ($result['steps'] as $i => $step) {
                if (empty($step['titre']) || empty($step['type'])) {
                    return $this->json([
                        'error' => "Étape {$i} invalide (titre ou type manquant).",
                    ], 502);
                }
            }

            return $this->json($result);

        } catch (TransportExceptionInterface $e) {
            // Network-level failure (DNS, timeout, connection refused)
            return $this->json([
                'error'   => 'Impossible de joindre l\'API Groq.',
                'details' => $e->getMessage(),
            ], 502);
        } catch (\Throwable $e) {
            return $this->json([
                'error'   => 'Erreur inattendue.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}