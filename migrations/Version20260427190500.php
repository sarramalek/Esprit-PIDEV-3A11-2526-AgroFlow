<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260427190500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add abonnements.prix_paye to persist effective price after promo.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE abonnements ADD prix_paye DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE abonnements DROP prix_paye');
    }
}
