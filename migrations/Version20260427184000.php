<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260427184000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Align users.backup_codes with non-null JSON default array.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE users SET backup_codes = '[]' WHERE backup_codes IS NULL");
        $this->addSql("ALTER TABLE users CHANGE backup_codes backup_codes JSON DEFAULT '[]' NOT NULL COMMENT '(DC2Type:json)'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE users CHANGE backup_codes backup_codes JSON DEFAULT NULL COMMENT '(DC2Type:json)'");
    }
}
