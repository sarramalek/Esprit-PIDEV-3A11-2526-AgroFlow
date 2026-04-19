<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

/**
 * Appelle l'API Grok (xAI) pour générer du SQL et analyser les résultats.
 */
class GrokService
{
    private const API_URL   = 'https://api.groq.com/openai/v1/chat/completions';
    private const API_MODEL = 'llama-3.1-8b-instant';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface     $logger,
        private readonly string              $grokApiKey,
    ) {}

    /**
     * Envoie une question + schéma à Grok et retourne [sql, analysis, summary].
     *
     * @param string $question      Question de l'utilisateur
     * @param string $schema        Schéma de la base (généré par DatabaseReaderService)
     * @param array  $history       Historique [{role, content}, ...]
     * @return array{sql:string|null, analysis:string, summary:string}
     */
   public function ask(string $question, string $schema, array $history = [], ?string $userCin = null): array
{
    $systemPrompt = $this->buildSystemPrompt($schema, $userCin);

    $messages   = array_map(fn($h) => ['role' => $h['role'], 'content' => $h['content']], $history);
    $messages[] = ['role' => 'user', 'content' => $question];

    $raw = $this->callApi($systemPrompt, $messages);

    return $this->parseResponse($raw);
}
    /**
     * Affine la réponse après avoir exécuté la requête SQL et récupéré les résultats.
     */
    public function refineWithResults(
    string $originalAnalysis,
    string $sql,
    array  $results,
    string $schema,
    string $question
): array {
    if (empty($results)) {
        return [
            'analysis' => "Aucun résultat trouvé pour votre question.",
            'summary'  => ''
        ];
    }

    $systemPrompt = $this->buildSystemPrompt($schema);

    $messages = [
        [
            'role'    => 'user',
            'content' => sprintf(
                "Question posée : \"%s\"\n\n" .
                "Résultats obtenus depuis la base de données :\n%s\n\n" .
                "CONSIGNE STRICTE : Réponds directement à la question en utilisant ces données réelles. " .
                "Exemple : \"Votre stock de blé est de 150 kg.\" ou \"Vous avez 3 cultures plantées : tomate, blé, maïs.\"\n" .
                "NE DIS PAS 'Recherche de...' ou 'La requête...'. Donne la réponse finale directement.\n" .
                "JSON uniquement : {\"analysis\":\"Réponse directe avec les vraies valeurs\",\"summary\":\"Résumé court\"}",
                $question,
                json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
            ),
        ],
    ];

    $raw = $this->callApi($systemPrompt, $messages, 300);

    return $this->parseResponse($raw);
}

private function buildSystemPrompt(string $schema, ?string $userCin = null): string
{
    $lines = explode("\n", $schema);
    $lines = array_filter($lines, fn($l) => !preg_match(
        '/^\s*(KEY|INDEX|CONSTRAINT|UNIQUE KEY|ENGINE|CHARSET|COLLATE|AUTO_INCREMENT)/i', $l
    ));
    $schema = implode("\n", $lines);

    preg_match_all('/TABLE:\s*(\w+)/i', $schema, $matches);
    $tableNames = implode(', ', $matches[1] ?? []);

    if (strlen($schema) > 10000) {
        $schema = substr($schema, 0, 10000) . "\n...(tronqué)";
    }

    // Construire la règle de filtrage avec le mapping colonne → table
    $filterRule = '';
    if ($userCin) {
        $filterRule = <<<FILTER
⚠️ FILTRE OBLIGATOIRE : L'utilisateur connecté a le CIN = '{$userCin}'.
Tu DOIS toujours filtrer les données par cet identifiant.
Mapping des colonnes selon la table :
- Table "users"   → filtre sur la colonne "cin" : WHERE cin = '{$userCin}'
- Table "terrain" → filtre sur la colonne "cin" : WHERE cin = '{$userCin}'
- Table "animaux" → filtre sur la colonne "user_id" : WHERE user_id = '{$userCin}'
- Table "article" → filtre sur la colonne "user_id" : WHERE user_id = '{$userCin}'
- Toute autre table → utilise la colonne qui référence l'utilisateur (cin, user_id, id_user)
NE JAMAIS retourner des données sans ce filtre.
FILTER;
    }

    return <<<PROMPT
Tu es un assistant expert en agriculture tunisienne.
Tables disponibles : {$tableNames}
Schéma complet :
{$schema}

{$filterRule}

INSTRUCTION CRITIQUE : Tu dois TOUJOURS répondre avec un objet JSON valide.
JAMAIS de texte libre, JAMAIS de SQL seul, JAMAIS de markdown.
Format OBLIGATOIRE :
{"sql":"SELECT ...","analysis":"Réponse en français","summary":"Résumé court"}

Si pas de SQL nécessaire :
{"sql":null,"analysis":"Réponse en français","summary":"Résumé court"}
PROMPT;
}
private function callApi(string $system, array $messages, int $maxTokens = 400): string
{
    for ($attempt = 1; $attempt <= 3; $attempt++) {
        try {
            $response = $this->httpClient->request('POST', self::API_URL, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->grokApiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model'       => self::API_MODEL,
                    'max_tokens'  => $maxTokens,
                    'temperature' => 0.3,
                    'messages'    => array_merge(
                        [['role' => 'system', 'content' => $system]],
                        $messages
                    ),
                ],
                'timeout' => 30,
            ]);

            $raw = $response->toArray()['choices'][0]['message']['content'] ?? '';
            
            // DEBUG TEMPORAIRE
            file_put_contents(
                __DIR__ . '/../../var/grok_raw.txt',
                date('H:i:s') . "\n" . $raw . "\n\n---\n\n",
                FILE_APPEND
            );

            return $raw;

        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), '429') && $attempt < 3) {
                sleep($attempt * 3);
                continue;
            }
            $this->logger->error('GrokService error: ' . $e->getMessage());
            throw new \RuntimeException('Erreur API Grok : ' . $e->getMessage(), 0, $e);
        }
    }

    throw new \RuntimeException('Limite de tentatives atteinte');
}

    private function parseResponse(string $raw): array
{
    $clean = preg_replace('/```json|```/i', '', $raw);
    $clean = trim($clean);

    // Extraire le premier objet JSON
    if (preg_match('/\{[\s\S]*\}/u', $clean, $m)) {
        $parsed = json_decode($m[0], true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return [
                'sql'      => $parsed['sql']      ?? null,
                'analysis' => $parsed['analysis'] ?? '',
                'summary'  => $parsed['summary']  ?? '',
            ];
        }
    }

    // ── Fallback : Grok a retourné du SQL brut ──
    $upperClean = strtoupper(trim($clean));
    if (str_starts_with($upperClean, 'SELECT') || str_starts_with($upperClean, 'SHOW')) {
        return [
            'sql'      => trim($clean),
            'analysis' => '',
            'summary'  => '',
        ];
    }

    // ── Fallback : réponse texte pure ──
    return [
        'sql'      => null,
        'analysis' => $raw,
        'summary'  => '',
    ];
}
}