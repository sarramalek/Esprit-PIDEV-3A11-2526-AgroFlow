<?php

namespace App\Service;

/**
 * Service principal : orchestre la lecture de la base et l'appel à Grok.
 */
class AiAssistantService
{
    public function __construct(
        private readonly GrokService           $grok,
        private readonly DatabaseReaderService $dbReader,
    ) {}

    /**
     * Traite une question de l'utilisateur et retourne une réponse complète.
     *
     * @param string $question
     * @param array<int, array{role: string, content: string}> $history [{role:'user'|'assistant', content:string}, ...]
     * @return array{
     *   sql: string|null,
     *   analysis: string,
     *   summary: string,
     *   results: array<int, mixed>,
     *   row_count: int,
     *   sql_error: string|null
     * }
     */
    public function answer(string $question, array $history = [], ?string $userCin = null): array
{
    $schema     = $this->dbReader->getSchema();
    try {
        $grokResult = $this->grok->ask($question, $schema, $history, $userCin);
    } catch (\Throwable $e) {
        $fallback = $this->answerWithFallback($question, $userCin);
        if ($fallback !== null) {
            return $fallback;
        }

        $msg = $e->getMessage();
        $isRateLimited = str_contains($msg, '429') || str_contains(strtolower($msg), 'rate limit');

        return [
            'sql'       => null,
            'analysis'  => $isRateLimited
                ? 'Le service IA est temporairement saturé (limite atteinte). Réessayez dans quelques instants.'
                : 'Le service IA est temporairement indisponible. Veuillez réessayer.',
            'summary'   => 'Service IA indisponible',
            'results'   => [],
            'row_count' => 0,
            'sql_error' => null,
        ];
    }

    $sql      = $grokResult['sql'];
    $analysis = $this->normalizeAssistantText($grokResult['analysis']);
    $summary  = $grokResult['summary'];
    $results  = [];
    $sqlError = null;

    if ($sql) {
        // Forcer le filtre CIN si Grok l'a oublié
        if ($userCin && !preg_match(
            '/\b(cin|user_id|id_user)\s*=\s*[\'"]?' . preg_quote($userCin, '/') . '[\'"]?/i',
            $sql
        )) {
            $sql = $this->injectCinFilter($sql, $userCin);
        }

        // Log du SQL final pour vérification
        file_put_contents(
            __DIR__ . '/../../var/sql_final.txt',
            date('H:i:s') . " | CIN: {$userCin}\n" . $sql . "\n\n---\n\n",
            FILE_APPEND
        );

        try {
            $results = $this->dbReader->executeSelect($sql);
        } catch (\Throwable $e) {
            $sqlError = $e->getMessage();
            $analysis = "Une erreur est survenue lors de la recherche. Veuillez reformuler votre question.";
        }
    }

    if (!empty($results) && $sql && !$sqlError) {
        $refined  = $this->grok->refineWithResults($analysis, $sql, $results, $schema, $question);
        $analysis = $this->normalizeAssistantText($refined['analysis']);
        $summary  = $refined['summary'] ?: $summary;
    }

    if (empty($analysis) && !empty($results)) {
        $analysis = count($results) . ' résultat(s) trouvé(s).';
    }
    if (empty($analysis) && empty($results) && $sql && !$sqlError) {
        $analysis = 'Aucun résultat trouvé pour votre question.';
    }

    return [
        'sql'       => $sql,
        'analysis'  => $analysis,
        'summary'   => $summary,
        'results'   => $results,
        'row_count' => count($results),
        'sql_error' => $sqlError,
    ];
}
    /**
     * Retourne les métadonnées de la base (pour l'affichage dans le header).
     */
    /**
     * @return array{database:string, table_count:int, tables:array<int, string>}
     */
    public function getDatabaseMeta(): array
    {
        return $this->dbReader->getMeta();
    }
    
    private function injectCinFilter(string $sql, string $cin): string
{
    // Mapping table → colonne CIN
    $tableColumnMap = [
        'animaux' => 'user_id',
        'article' => 'user_id',
        'terrain' => 'cin',
        'users'   => 'cin',
    ];

    $sqlLower = strtolower($sql);

    // Détecter la table principale utilisée dans la requête
    $column = 'cin'; // fallback
    foreach ($tableColumnMap as $table => $col) {
        if (str_contains($sqlLower, $table)) {
            $column = $col;
            break;
        }
    }

    // Vérifier si le filtre est déjà présent (tolérant aux espaces)
    $cinPattern = '/\b' . preg_quote($column, '/') . '\s*=\s*[\'"]?' . preg_quote($cin, '/') . '[\'"]?/i';
    if (preg_match($cinPattern, $sql)) {
        return $sql;
    }

    // Injecter dans WHERE existant
    if (preg_match('/\bwhere\b/i', $sql)) {
        return preg_replace('/\bwhere\b/i', "WHERE {$column} = '{$cin}' AND ", $sql, 1);
    }

    // Injecter avant ORDER BY, GROUP BY, LIMIT, HAVING
    foreach (['order by', 'group by', 'limit', 'having'] as $keyword) {
        if (str_contains($sqlLower, $keyword)) {
            $pos = stripos($sql, $keyword);
            return substr($sql, 0, $pos) . " WHERE {$column} = '{$cin}' " . substr($sql, $pos);
        }
    }

    return $sql . " WHERE {$column} = '{$cin}'";
}

    /**
     * Fallback local (sans API IA) pour les questions fréquentes.
     *
     * @return array{
     *   sql: string|null,
     *   analysis: string,
     *   summary: string,
     *   results: array<int, mixed>,
     *   row_count: int,
     *   sql_error: string|null
     * }|null
     */
    private function answerWithFallback(string $question, ?string $userCin): ?array
    {
        if ($userCin === null || trim($userCin) === '') {
            return null;
        }

        $q = mb_strtolower($question);
        $sql = null;
        $analysis = null;

        if (str_contains($q, 'animaux') || str_contains($q, 'animal')) {
            $sql = "SELECT COUNT(*) AS total_animaux FROM animaux WHERE user_id = '{$userCin}'";
            $analysis = 'Résumé local: vous avez {n} animal(aux).';
        } elseif (str_contains($q, 'terrain') || str_contains($q, 'terrains') || str_contains($q, 'surface')) {
            $sql = "SELECT COUNT(*) AS total_terrains, COALESCE(SUM(surface), 0) AS surface_totale FROM terrain WHERE cin = '{$userCin}'";
            $analysis = 'Résumé local: vous avez {n} terrain(s) pour une surface totale de {surface}.';
        } elseif (str_contains($q, 'stock') || str_contains($q, 'article') || str_contains($q, 'produit')) {
            $sql = "SELECT COUNT(*) AS total_articles, COALESCE(SUM(quantite_en_stock), 0) AS stock_total FROM article WHERE id_user = '{$userCin}'";
            $analysis = 'Résumé local: {n} article(s) en stock, quantité totale {stock}.';
        }

        if ($sql === null) {
            return null;
        }

        try {
            $results = $this->dbReader->executeSelect($sql);
            $row = $results[0] ?? [];

            if (isset($row['total_animaux'])) {
                $text = str_replace('{n}', (string) $row['total_animaux'], $analysis);
            } elseif (isset($row['total_terrains'])) {
                $text = str_replace(
                    ['{n}', '{surface}'],
                    [(string) $row['total_terrains'], (string) $row['surface_totale'],
                    ],
                    $analysis
                );
            } else {
                $text = str_replace(
                    ['{n}', '{stock}'],
                    [(string) ($row['total_articles'] ?? 0), (string) ($row['stock_total'] ?? 0)],
                    $analysis
                );
            }

            return [
                'sql'       => $sql,
                'analysis'  => $text,
                'summary'   => 'Réponse locale (mode fallback)',
                'results'   => $results,
                'row_count' => count($results),
                'sql_error' => null,
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeAssistantText(string $text): string
    {
        $clean = trim($text);
        if ($clean === '') {
            return $clean;
        }

        if (preg_match('/\{[\s\S]*\}/u', $clean, $m) === 1) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded) && isset($decoded['analysis']) && is_string($decoded['analysis'])) {
                return trim($decoded['analysis']);
            }
        }

        // Fallback tolérant: certains retours ressemblent à du JSON invalide.
        if (str_starts_with($clean, '{"sql"') || str_starts_with($clean, '{')) {
            $patternWithSummary = '/"analysis"\s*:\s*"([\s\S]*?)"\s*,\s*"summary"/u';
            if (preg_match($patternWithSummary, $clean, $m) === 1) {
                return stripcslashes(trim($m[1]));
            }

            $patternToEnd = '/"analysis"\s*:\s*"([\s\S]*?)"\s*\}\s*$/u';
            if (preg_match($patternToEnd, $clean, $m) === 1) {
                return stripcslashes(trim($m[1]));
            }
        }

        return $text;
    }
}