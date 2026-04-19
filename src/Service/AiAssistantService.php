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
   $schema = $this->dbReader->getSchema();
file_put_contents(__DIR__ . '/../../var/schema_debug.txt', $schema);
    $grokResult = $this->grok->ask($question, $schema, $history, $userCin);

    $sql      = $grokResult['sql'];
    $analysis = $grokResult['analysis'];
    $summary  = $grokResult['summary'];
    $results  = [];
    $sqlError = null;

    if ($sql) {
        try {
            $results = $this->dbReader->executeSelect($sql);
        } catch (\Throwable $e) {
            $sqlError = $e->getMessage();
        }
    }

    // ── DEBUG TEMPORAIRE ──────────────────────────────
    file_put_contents(__DIR__ . '/../../var/schema_debug.txt', json_encode([
        'sql'      => $sql,
        'results'  => $results,
        'sqlError' => $sqlError,
        'count'    => count($results),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    // ─────────────────────────────────────────────────

    
if (!empty($results) && $sql) {
    $refined  = $this->grok->refineWithResults($analysis, $sql, $results, $schema, $question);
    $analysis = $refined['analysis'];
    $summary  = $refined['summary'] ?: $summary;
}

// Si analysis toujours vide, générer une réponse basique
if (empty($analysis) && !empty($results)) {
    $analysis = count($results) . ' résultat(s) trouvé(s).';
}
if (empty($analysis) && empty($results) && $sql) {
    $analysis = 'Aucun résultat trouvé.';
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
}