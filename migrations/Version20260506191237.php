<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260506191237 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE maintenance DROP FOREIGN KEY fk_maintenance_machine');
        $this->addSql('DROP INDEX IDX_2F84F8E9B1BBBA33 ON maintenance');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE maintenance ADD CONSTRAINT fk_maintenance_machine FOREIGN KEY (idM) REFERENCES machine (idM)');
        $this->addSql('CREATE INDEX IDX_2F84F8E9B1BBBA33 ON maintenance (idM)');
    }
}
