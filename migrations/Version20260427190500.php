<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Placeholder migration.
 *
 * This version exists in `doctrine_migration_versions` but the original file was missing
 * from the repository. Keeping a no-op migration with the same version restores a
 * consistent migrations history for Doctrine.
 */
final class Version20260427190500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Placeholder for previously executed migration (file was missing).';
    }

    public function up(Schema $schema): void
    {
        // Intentionally left empty.
    }

    public function down(Schema $schema): void
    {
        // Intentionally left empty.
    }
}

