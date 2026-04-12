<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260404150910 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // Migration sécurisée pour Animaux et Examens
        // Ajout de la relation User-Animal et mise à jour des champs
        // La colonne user_id et ses index existent déjà suite à l'essai précédent
        $this->addSql('ALTER TABLE animaux CHANGE nom nom VARCHAR(255) NOT NULL, CHANGE espece espece VARCHAR(255) NOT NULL, CHANGE date_naissance date_naissance DATE NOT NULL, CHANGE sexe sexe VARCHAR(255) NOT NULL, CHANGE poids poids DOUBLE PRECISION DEFAULT NULL');
        // $this->addSql('ALTER TABLE animaux ADD CONSTRAINT FK_9ABE194DA76ED395 FOREIGN KEY (user_id) REFERENCES users (cin)');
        // $this->addSql('CREATE INDEX IDX_9ABE194DA76ED395 ON animaux (user_id)');
        // Les clés étrangères ont déjà été supprimées lors des tentatives précédentes
        // $this->addSql('ALTER TABLE examens_sante DROP FOREIGN KEY examens_sante_ibfk_1');
        // $this->addSql('ALTER TABLE examens_sante DROP FOREIGN KEY fk_animal');
        // On ignore les suppressions qui bloquent, on passe directement aux modifications
        // $this->addSql('DROP INDEX fk_animal ON examens_sante');
        // Les modifications de la table examens_sante sont déjà en place
        // $this->addSql('ALTER TABLE examens_sante CHANGE date_examen ..., CHANGE type_examen ..., CHANGE id_animal id_animal INT NOT NULL');
        // $this->addSql('ALTER TABLE examens_sante ADD CONSTRAINT FK_714ADD7D4C9C96F2 FOREIGN KEY (id_animal) REFERENCES animaux (id)');
        // $this->addSql('CREATE INDEX IDX_714ADD7D4C9C96F2 ON examens_sante (id_animal)');
        // $this->addSql('DROP INDEX idx_email_active ON users');
        // $this->addSql('DROP INDEX idx_two_factor_enabled ON users');
        $this->addSql('ALTER TABLE users CHANGE nom nom VARCHAR(255) DEFAULT NULL, CHANGE prenom prenom VARCHAR(255) DEFAULT NULL, CHANGE tel tel VARCHAR(8) DEFAULT NULL, CHANGE date_naiss date_naiss DATE DEFAULT NULL, CHANGE email email VARCHAR(255) DEFAULT NULL, CHANGE mdp mdp VARCHAR(255) DEFAULT NULL, CHANGE adresse adresse VARCHAR(255) DEFAULT NULL, CHANGE ville ville VARCHAR(255) DEFAULT NULL, CHANGE date_creationcpt date_creationcpt DATE DEFAULT NULL, CHANGE date_dernierchg date_dernierchg DATE DEFAULT NULL, CHANGE two_factor_enabled two_factor_enabled TINYINT(1) DEFAULT 0 NOT NULL, CHANGE two_factor_secret two_factor_secret VARCHAR(32) DEFAULT NULL, CHANGE two_factor_backup_codes two_factor_backup_codes LONGTEXT DEFAULT NULL, CHANGE img img VARCHAR(255) DEFAULT NULL');
        // $this->addSql('DROP INDEX email ON users');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        // Les tables des autres groupes seront préservées ici
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE animaux DROP FOREIGN KEY FK_9ABE194DA76ED395');
        $this->addSql('DROP INDEX IDX_9ABE194DA76ED395 ON animaux');
        $this->addSql('ALTER TABLE animaux DROP user_id, CHANGE nom nom VARCHAR(50) DEFAULT \'NULL\', CHANGE espece espece VARCHAR(50) DEFAULT \'NULL\', CHANGE date_naissance date_naissance DATE DEFAULT \'NULL\', CHANGE sexe sexe VARCHAR(20) DEFAULT \'NULL\', CHANGE poids poids FLOAT DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE examens_sante DROP FOREIGN KEY FK_714ADD7D4C9C96F2');
        $this->addSql('ALTER TABLE examens_sante ADD animal_id INT DEFAULT NULL, CHANGE date_examen date_examen DATE DEFAULT \'NULL\', CHANGE type_examen type_examen VARCHAR(100) DEFAULT \'NULL\', CHANGE diagnostic diagnostic TEXT DEFAULT NULL, CHANGE traitement traitement VARCHAR(255) DEFAULT \'NULL\', CHANGE id_animal id_animal INT DEFAULT NULL');
        $this->addSql('ALTER TABLE examens_sante ADD CONSTRAINT examens_sante_ibfk_1 FOREIGN KEY (animal_id) REFERENCES animaux (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE examens_sante ADD CONSTRAINT fk_animal FOREIGN KEY (id_animal) REFERENCES animaux (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX animal_id ON examens_sante (animal_id)');
        $this->addSql('DROP INDEX idx_714add7d4c9c96f2 ON examens_sante');
        $this->addSql('CREATE INDEX fk_animal ON examens_sante (id_animal)');
        $this->addSql('ALTER TABLE users CHANGE nom nom VARCHAR(255) DEFAULT \'NULL\', CHANGE prenom prenom VARCHAR(255) DEFAULT \'NULL\', CHANGE tel tel VARCHAR(8) DEFAULT \'NULL\', CHANGE date_naiss date_naiss DATE DEFAULT \'NULL\', CHANGE email email VARCHAR(255) DEFAULT \'NULL\', CHANGE mdp mdp VARCHAR(255) DEFAULT \'NULL\', CHANGE adresse adresse VARCHAR(255) DEFAULT \'NULL\', CHANGE ville ville VARCHAR(255) DEFAULT \'NULL\', CHANGE date_creationcpt date_creationcpt DATE DEFAULT \'NULL\', CHANGE date_dernierchg date_dernierchg DATE DEFAULT \'NULL\', CHANGE two_factor_enabled two_factor_enabled TINYINT(1) DEFAULT 0 COMMENT \'Indique si la 2FA est activée\', CHANGE two_factor_secret two_factor_secret VARCHAR(32) DEFAULT \'NULL\' COMMENT \'Clé secrète pour Google Authenticator\', CHANGE two_factor_backup_codes two_factor_backup_codes TEXT DEFAULT NULL COMMENT \'Codes de secours hashés (séparés par virgules)\', CHANGE img img VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('CREATE INDEX idx_email_active ON users (email, two_factor_enabled)');
        $this->addSql('CREATE INDEX idx_two_factor_enabled ON users (two_factor_enabled)');
        $this->addSql('DROP INDEX uniq_1483a5e9e7927c74 ON users');
        $this->addSql('CREATE UNIQUE INDEX email ON users (email)');
    }
}
