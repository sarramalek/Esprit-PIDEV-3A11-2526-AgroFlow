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
     * @param array  $history  [{role:'user'|'assistant', content:string}, ...]
     * @return array{
     *   sql: string|null,
     *   analysis: string,
     *   summary: string,
     *   results: array,
     *   row_count: int,
     *   sql_error: string|null
     * }
     */
   public function answer(string $question, array $history = [], ?string $userCin = null): array
{
    $schema     = $this->dbReader->getSchema();
    $grokResult = $this->grok->ask($question, $schema, $history, $userCin);

    $sql      = $grokResult['sql'];
    $analysis = $grokResult['analysis'];
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
        $analysis = $refined['analysis'];
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
}