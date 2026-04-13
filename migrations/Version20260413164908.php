<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260413164908 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE animaux CHANGE poids poids DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE article CHANGE prix_unitaire prix_unitaire DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE examens_sante CHANGE date_examen date_examen DATE DEFAULT NULL, CHANGE type_examen type_examen VARCHAR(100) DEFAULT NULL, CHANGE traitement traitement VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE machine CHANGE numeroSerie numeroSerie VARCHAR(255) DEFAULT NULL, CHANGE dateAchat dateAchat DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE maintenance CHANGE dateMain dateMain DATE DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE mouvement_stock CHANGE motif motif VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE offres CHANGE nom_offre nom_offre VARCHAR(255) DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE prix prix DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE plante CHANGE variete variete VARCHAR(100) DEFAULT NULL, CHANGE besoin_eau besoin_eau DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE rotation CHANGE date_debut_t date_debut_t DATE DEFAULT NULL, CHANGE date_fin_t date_fin_t DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE taches DROP FOREIGN KEY `FK_3BF2CD987C9DFC0C`');
        $this->addSql('ALTER TABLE taches CHANGE nom_tache nom_tache VARCHAR(255) DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE etat etat VARCHAR(255) DEFAULT NULL, CHANGE priorite priorite VARCHAR(255) DEFAULT NULL, CHANGE date_echeancee date_echeancee DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE taches ADD CONSTRAINT FK_3BF2CD987C9DFC0C FOREIGN KEY (assignee) REFERENCES users (cin)');
        $this->addSql('ALTER TABLE terrain CHANGE nom_terrain nom_terrain VARCHAR(50) DEFAULT NULL, CHANGE surface surface DOUBLE PRECISION DEFAULT NULL, CHANGE type_sol type_sol VARCHAR(200) DEFAULT NULL, CHANGE localisation localisation VARCHAR(2000) DEFAULT NULL, CHANGE p_h p_h DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD google_authenticator_secret VARCHAR(255) DEFAULT NULL, ADD backup_codes JSON DEFAULT NULL, CHANGE nom nom VARCHAR(255) DEFAULT NULL, CHANGE prenom prenom VARCHAR(255) DEFAULT NULL, CHANGE tel tel VARCHAR(8) DEFAULT NULL, CHANGE date_naiss date_naiss DATE DEFAULT NULL, CHANGE email email VARCHAR(255) DEFAULT NULL, CHANGE mdp mdp VARCHAR(255) DEFAULT NULL, CHANGE adresse adresse VARCHAR(255) DEFAULT NULL, CHANGE ville ville VARCHAR(255) DEFAULT NULL, CHANGE date_creationcpt date_creationcpt DATE DEFAULT NULL, CHANGE date_dernierchg date_dernierchg DATE DEFAULT NULL, CHANGE two_factor_secret two_factor_secret VARCHAR(32) DEFAULT NULL, CHANGE img img VARCHAR(255) DEFAULT \'default.png\' NOT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE animaux CHANGE poids poids DOUBLE PRECISION DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE article CHANGE prix_unitaire prix_unitaire DOUBLE PRECISION DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE examens_sante CHANGE date_examen date_examen DATE DEFAULT \'NULL\', CHANGE type_examen type_examen VARCHAR(100) DEFAULT \'NULL\', CHANGE traitement traitement VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE machine CHANGE numeroSerie numeroSerie VARCHAR(255) DEFAULT \'NULL\', CHANGE dateAchat dateAchat DATE DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE maintenance CHANGE dateMain dateMain DATE DEFAULT \'NULL\', CHANGE description description VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE mouvement_stock CHANGE motif motif VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE offres CHANGE nom_offre nom_offre VARCHAR(255) DEFAULT \'NULL\', CHANGE description description VARCHAR(255) DEFAULT \'NULL\', CHANGE prix prix DOUBLE PRECISION DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE plante CHANGE variete variete VARCHAR(100) DEFAULT \'NULL\', CHANGE besoin_eau besoin_eau DOUBLE PRECISION DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE rotation CHANGE date_debut_t date_debut_t DATE DEFAULT \'NULL\', CHANGE date_fin_t date_fin_t DATE DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE taches DROP FOREIGN KEY FK_3BF2CD987C9DFC0C');
        $this->addSql('ALTER TABLE taches CHANGE nom_tache nom_tache VARCHAR(255) DEFAULT \'NULL\', CHANGE description description VARCHAR(255) DEFAULT \'NULL\', CHANGE etat etat VARCHAR(255) DEFAULT \'NULL\', CHANGE priorite priorite VARCHAR(255) DEFAULT \'NULL\', CHANGE date_echeancee date_echeancee DATE DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE taches ADD CONSTRAINT `FK_3BF2CD987C9DFC0C` FOREIGN KEY (assignee) REFERENCES users (cin) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE terrain CHANGE nom_terrain nom_terrain VARCHAR(50) DEFAULT \'NULL\', CHANGE surface surface DOUBLE PRECISION DEFAULT \'NULL\', CHANGE type_sol type_sol VARCHAR(200) DEFAULT \'NULL\', CHANGE localisation localisation VARCHAR(2000) DEFAULT \'NULL\', CHANGE p_h p_h DOUBLE PRECISION DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE users DROP google_authenticator_secret, DROP backup_codes, CHANGE nom nom VARCHAR(255) DEFAULT \'NULL\', CHANGE prenom prenom VARCHAR(255) DEFAULT \'NULL\', CHANGE tel tel VARCHAR(8) DEFAULT \'NULL\', CHANGE date_naiss date_naiss DATE DEFAULT \'NULL\', CHANGE email email VARCHAR(255) DEFAULT \'NULL\', CHANGE mdp mdp VARCHAR(255) DEFAULT \'NULL\', CHANGE adresse adresse VARCHAR(255) DEFAULT \'NULL\', CHANGE ville ville VARCHAR(255) DEFAULT \'NULL\', CHANGE date_creationcpt date_creationcpt DATE DEFAULT \'NULL\', CHANGE date_dernierchg date_dernierchg DATE DEFAULT \'NULL\', CHANGE two_factor_secret two_factor_secret VARCHAR(32) DEFAULT \'NULL\', CHANGE img img VARCHAR(255) DEFAULT \'\'\'default.png\'\'\' NOT NULL');
    }
}
