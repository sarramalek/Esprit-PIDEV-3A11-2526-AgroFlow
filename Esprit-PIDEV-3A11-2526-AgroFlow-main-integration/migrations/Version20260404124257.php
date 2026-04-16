<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260404124257 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE animaux CHANGE poids poids DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE examens_sante CHANGE date_examen date_examen DATE DEFAULT NULL, CHANGE type_examen type_examen VARCHAR(100) DEFAULT NULL, CHANGE traitement traitement VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE examens_sante RENAME INDEX fk_animal TO IDX_714ADD7D4C9C96F2');
        $this->addSql('ALTER TABLE machine ADD etat_m VARCHAR(255) NOT NULL, ADD numero_serie VARCHAR(255) NOT NULL, DROP etatM, DROP numeroSerie, CHANGE idM id INT AUTO_INCREMENT NOT NULL, CHANGE dateAchat date_achat DATE NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('DROP INDEX idx_two_factor_enabled ON users');
        $this->addSql('DROP INDEX idx_email_active ON users');
        $this->addSql('ALTER TABLE users CHANGE nom nom VARCHAR(255) DEFAULT NULL, CHANGE prenom prenom VARCHAR(255) DEFAULT NULL, CHANGE tel tel VARCHAR(8) DEFAULT NULL, CHANGE date_naiss date_naiss DATE DEFAULT NULL, CHANGE email email VARCHAR(255) DEFAULT NULL, CHANGE mdp mdp VARCHAR(255) DEFAULT NULL, CHANGE adresse adresse VARCHAR(255) DEFAULT NULL, CHANGE ville ville VARCHAR(255) DEFAULT NULL, CHANGE date_creationcpt date_creationcpt DATE DEFAULT NULL, CHANGE date_dernierchg date_dernierchg DATE DEFAULT NULL, CHANGE two_factor_enabled two_factor_enabled TINYINT(1) DEFAULT 0 NOT NULL, CHANGE two_factor_secret two_factor_secret VARCHAR(32) DEFAULT NULL, CHANGE two_factor_backup_codes two_factor_backup_codes LONGTEXT DEFAULT NULL, CHANGE img img VARCHAR(255) DEFAULT \'default.png\' NOT NULL');
        $this->addSql('ALTER TABLE users RENAME INDEX email TO UNIQ_1483A5E9E7927C74');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE animaux CHANGE poids poids DOUBLE PRECISION DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE examens_sante CHANGE date_examen date_examen DATE DEFAULT \'NULL\', CHANGE type_examen type_examen VARCHAR(100) DEFAULT \'NULL\', CHANGE traitement traitement VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE examens_sante RENAME INDEX idx_714add7d4c9c96f2 TO fk_animal');
        $this->addSql('ALTER TABLE machine ADD etatM VARCHAR(255) NOT NULL, ADD numeroSerie VARCHAR(255) NOT NULL, DROP etat_m, DROP numero_serie, CHANGE id idM INT AUTO_INCREMENT NOT NULL, CHANGE date_achat dateAchat DATE NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (idM)');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE users CHANGE nom nom VARCHAR(255) DEFAULT \'NULL\', CHANGE prenom prenom VARCHAR(255) DEFAULT \'NULL\', CHANGE tel tel VARCHAR(8) DEFAULT \'NULL\', CHANGE date_naiss date_naiss DATE DEFAULT \'NULL\', CHANGE email email VARCHAR(255) DEFAULT \'NULL\', CHANGE mdp mdp VARCHAR(255) DEFAULT \'NULL\', CHANGE adresse adresse VARCHAR(255) DEFAULT \'NULL\', CHANGE ville ville VARCHAR(255) DEFAULT \'NULL\', CHANGE date_creationcpt date_creationcpt DATE DEFAULT \'NULL\', CHANGE date_dernierchg date_dernierchg DATE DEFAULT \'NULL\', CHANGE two_factor_enabled two_factor_enabled TINYINT(1) DEFAULT 0 COMMENT \'Indique si la 2FA est activée\', CHANGE two_factor_secret two_factor_secret VARCHAR(32) DEFAULT \'NULL\' COMMENT \'Clé secrète pour Google Authenticator\', CHANGE two_factor_backup_codes two_factor_backup_codes TEXT DEFAULT NULL COMMENT \'Codes de secours hashés (séparés par virgules)\', CHANGE img img VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX idx_two_factor_enabled ON users (two_factor_enabled)');
        $this->addSql('CREATE INDEX idx_email_active ON users (email, two_factor_enabled)');
        $this->addSql('ALTER TABLE users RENAME INDEX uniq_1483a5e9e7927c74 TO email');
    }
}
