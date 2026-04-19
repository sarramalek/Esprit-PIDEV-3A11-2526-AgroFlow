<?php

namespace App\Service;

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

/**
 * Lit le schéma de la base de données et exécute des requêtes SELECT sécurisées.
 */
class DatabaseReaderService
{
    public function __construct(
        private readonly Connection    $connection,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * Retourne une description textuelle du schéma (tables + colonnes + exemples).
     */
    public function getSchema(): string
{
    $database = $this->connection->getDatabase();
    $tables   = $this->getTables($database);

    $schema = "Base de données : {$database}\n\n";

    foreach ($tables as $table) {
        $columns = $this->getColumns($database, $table);
        $schema .= "TABLE: {$table}\n";

        foreach ($columns as $col) {
            $pk  = $col['COLUMN_KEY'] === 'PRI' ? ' [PK]' : '';
            $nul = $col['IS_NULLABLE'] === 'YES' ? ' (nullable)' : '';
            $schema .= "  - {$col['COLUMN_NAME']}: {$col['DATA_TYPE']}{$pk}{$nul}\n";
        }

        $schema .= "\n";
    }

    return $schema;
}

    /**
     * Exécute une requête SELECT et retourne les lignes.
     * Lève une exception si la requête n'est pas un SELECT/SHOW.
     *
     * @return array<int, array<string, mixed>>
     */
    public function executeSelect(string $sql): array
    {
        $normalized = strtoupper(ltrim($sql));

        if (!str_starts_with($normalized, 'SELECT') && !str_starts_with($normalized, 'SHOW')) {
            throw new \InvalidArgumentException('Seules les requêtes SELECT/SHOW sont autorisées.');
        }

        try {
            return $this->connection->fetchAllAssociative($sql);
        } catch (\Throwable $e) {
            $this->logger->error("Erreur SQL: {$e->getMessage()} | SQL: {$sql}");
            throw new \RuntimeException('Erreur lors de l\'exécution SQL : ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Retourne les métadonnées de la base (nb tables, nom).
     */
    public function getMeta(): array
    {
        $database = $this->connection->getDatabase();
        $tables   = $this->getTables($database);

        return [
            'database'    => $database,
            'table_count' => count($tables),
            'tables'      => $tables,
        ];
    }

    // ── Private ───────────────────────────────────────────────────────────────

    /** @return string[] */
    private function getTables(string $database): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME',
            [$database]
        );

        return array_column($rows, 'TABLE_NAME');
    }

    /** @return array<int, array<string, string>> */
    private function getColumns(string $database, string $table): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_KEY
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
             ORDER BY ORDINAL_POSITION',
            [$database, $table]
        );
    }
}