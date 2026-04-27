<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260405111432 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE abonnements DROP FOREIGN KEY abonnements_ibfk_1');
        $this->addSql('ALTER TABLE achat DROP FOREIGN KEY fk_users_cin');
        $this->addSql('DROP TABLE abonnements');
        $this->addSql('DROP TABLE achat');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE categorie');
        $this->addSql('DROP TABLE categorieevenement');
        $this->addSql('DROP TABLE evenement');
        $this->addSql('DROP TABLE login_history');
        $this->addSql('DROP TABLE machine');
        $this->addSql('DROP TABLE maintenance');
        $this->addSql('DROP TABLE offres');
        $this->addSql('DROP TABLE participation');
        $this->addSql('DROP TABLE password_resets');
        $this->addSql('DROP TABLE security_logs');
        $this->addSql('DROP TABLE taches');
        $this->addSql('ALTER TABLE animaux DROP race, CHANGE nom nom VARCHAR(255) NOT NULL, CHANGE espece espece VARCHAR(255) NOT NULL, CHANGE date_naissance date_naissance DATE NOT NULL, CHANGE sexe sexe VARCHAR(255) NOT NULL, CHANGE poids poids DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE examens_sante DROP FOREIGN KEY fk_animal');
        $this->addSql('ALTER TABLE examens_sante DROP FOREIGN KEY examens_sante_ibfk_1');
        $this->addSql('DROP INDEX animal_id ON examens_sante');
        $this->addSql('ALTER TABLE examens_sante DROP animal_id, CHANGE date_examen date_examen DATE DEFAULT NULL, CHANGE type_examen type_examen VARCHAR(100) DEFAULT NULL, CHANGE diagnostic diagnostic LONGTEXT DEFAULT NULL, CHANGE traitement traitement VARCHAR(255) DEFAULT NULL, CHANGE id_animal id_animal INT NOT NULL');
        $this->addSql('ALTER TABLE examens_sante ADD CONSTRAINT FK_714ADD7D4C9C96F2 FOREIGN KEY (id_animal) REFERENCES animaux (id)');
        $this->addSql('ALTER TABLE examens_sante RENAME INDEX fk_animal TO IDX_714ADD7D4C9C96F2');
        $this->addSql('ALTER TABLE plante CHANGE nom_p nom_p VARCHAR(100) NOT NULL, CHANGE variete variete VARCHAR(100) DEFAULT NULL, CHANGE besoin_eau besoin_eau DOUBLE PRECISION DEFAULT NULL, CHANGE cycle_jours cycle_jours INT DEFAULT NULL');
        $this->addSql('DROP INDEX id_terrain ON rotation');
        $this->addSql('ALTER TABLE rotation CHANGE status status INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE rotation RENAME INDEX fk_rotation_plante TO IDX_297C98F1774DDCAA');
        $this->addSql('ALTER TABLE terrain DROP FOREIGN KEY terrain_ibfk_1');
        $this->addSql('DROP INDEX cin ON terrain');
        $this->addSql('ALTER TABLE terrain CHANGE nom_terrain nom_terrain VARCHAR(100) NOT NULL, CHANGE surface surface DOUBLE PRECISION NOT NULL, CHANGE type_sol type_sol VARCHAR(50) NOT NULL, CHANGE localisation localisation VARCHAR(150) NOT NULL, CHANGE p_h p_h DOUBLE PRECISION DEFAULT NULL, CHANGE cin cin VARCHAR(8) DEFAULT NULL');
        $this->addSql('DROP INDEX idx_email_active ON users');
        $this->addSql('DROP INDEX idx_two_factor_enabled ON users');
        $this->addSql('ALTER TABLE users CHANGE nom nom VARCHAR(255) DEFAULT NULL, CHANGE prenom prenom VARCHAR(255) DEFAULT NULL, CHANGE tel tel VARCHAR(8) DEFAULT NULL, CHANGE date_naiss date_naiss DATE DEFAULT NULL, CHANGE email email VARCHAR(255) DEFAULT NULL, CHANGE mdp mdp VARCHAR(255) DEFAULT NULL, CHANGE adresse adresse VARCHAR(255) DEFAULT NULL, CHANGE ville ville VARCHAR(255) DEFAULT NULL, CHANGE date_creationcpt date_creationcpt DATE DEFAULT NULL, CHANGE date_dernierchg date_dernierchg DATE DEFAULT NULL, CHANGE two_factor_enabled two_factor_enabled TINYINT(1) DEFAULT 0 NOT NULL, CHANGE two_factor_secret two_factor_secret VARCHAR(32) DEFAULT NULL, CHANGE two_factor_backup_codes two_factor_backup_codes LONGTEXT DEFAULT NULL, CHANGE img img VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE users RENAME INDEX email TO UNIQ_1483A5E9E7927C74');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE abonnements (id_abonn INT AUTO_INCREMENT NOT NULL, cin INT NOT NULL, id_offre INT NOT NULL, date_inscription DATE NOT NULL, date_expiration DATE NOT NULL, situation VARCHAR(9) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, INDEX id_offre (id_offre), INDEX cin (cin), PRIMARY KEY(id_abonn, cin, id_offre)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE achat (idAchat INT AUTO_INCREMENT NOT NULL, dateAchat DATE NOT NULL, idM INT NOT NULL, cin INT NOT NULL, quantite VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, INDEX idM (idM), INDEX cin (cin), PRIMARY KEY(idAchat)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE article (id_article INT AUTO_INCREMENT NOT NULL, nom VARCHAR(150) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, quantite_en_stock DOUBLE PRECISION DEFAULT \'0\' NOT NULL, seuil_alerte DOUBLE PRECISION DEFAULT \'10\' NOT NULL, unite_mesure VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, code_qr VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_general_ci`, id_categorie INT DEFAULT NULL, INDEX fk_article_categorie (id_categorie), PRIMARY KEY(id_article)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE categorie (id_categorie INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, description TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, date_creation DATETIME DEFAULT \'current_timestamp()\' NOT NULL, nom_en VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, nom_ar VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, image_url VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, PRIMARY KEY(id_categorie)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE categorieevenement (id_categorie INT AUTO_INCREMENT NOT NULL, nom_categorie VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, description TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, PRIMARY KEY(id_categorie)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE evenement (id_evenement INT AUTO_INCREMENT NOT NULL, titre VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, description TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, type_evenement VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, date_debut DATE NOT NULL, date_fin DATE NOT NULL, lieu VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, statut VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, id_categorie INT NOT NULL, INDEX id_categorie (id_categorie), PRIMARY KEY(id_evenement)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE login_history (id INT AUTO_INCREMENT NOT NULL, user_cin INT NOT NULL, email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, login_time DATETIME NOT NULL, ip_address VARCHAR(45) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci` COMMENT \'Adresse IP (IPv4 ou IPv6)\', user_agent TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'Navigateur/OS utilisé\', success TINYINT(1) DEFAULT 1 COMMENT \'Connexion réussie ou échouée\', two_factor_used TINYINT(1) DEFAULT 0 COMMENT \'Indique si la 2FA a été utilisée\', failure_reason VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci` COMMENT \'Raison de l\'\'échec si applicable\', INDEX idx_login_time (login_time), INDEX idx_success (success), INDEX idx_user_cin (user_cin), INDEX idx_email (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'Historique des connexions pour audit de sécurité\' ');
        $this->addSql('CREATE TABLE machine (idM INT AUTO_INCREMENT NOT NULL, marque VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, modele VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, etatM VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, numeroSerie VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, dateAchat DATE NOT NULL, nom VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, PRIMARY KEY(idM)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE maintenance (idMain INT AUTO_INCREMENT NOT NULL, typePanne VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, cout DOUBLE PRECISION NOT NULL, dateMain DATE NOT NULL, description VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, idM INT DEFAULT NULL, INDEX idM (idM), PRIMARY KEY(idMain)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE offres (id_offres INT AUTO_INCREMENT NOT NULL, nom_offre VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_general_ci`, description VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_general_ci`, prix FLOAT DEFAULT \'NULL\', duree_offre INT DEFAULT NULL, PRIMARY KEY(id_offres)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE participation (id_participation INT AUTO_INCREMENT NOT NULL, statut_participation VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, date_inscription DATE NOT NULL, presence TINYINT(1) NOT NULL, id_evenement INT DEFAULT NULL, id_user INT NOT NULL, INDEX id_evenement (id_evenement), INDEX cin (id_user), PRIMARY KEY(id_participation)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE password_resets (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, code VARCHAR(6) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'Code de vérification à 6 chiffres\', expires_at DATETIME NOT NULL COMMENT \'Date d\'\'expiration du code (15 minutes)\', created_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL, used TINYINT(1) DEFAULT 0 COMMENT \'Indique si le code a été utilisé\', INDEX idx_expires_at (expires_at), UNIQUE INDEX unique_email (email), INDEX idx_email (email), INDEX idx_code (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'Table pour stocker les codes de réinitialisation de mot de passe\' ');
        $this->addSql('CREATE TABLE security_logs (id INT AUTO_INCREMENT NOT NULL, user_cin INT DEFAULT NULL, event_type VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'Type d\'\'événement (login, logout, password_change, etc.)\', event_description TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ip_address VARCHAR(45) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, timestamp DATETIME NOT NULL, severity ENUM(\'LOW\', \'MEDIUM\', \'HIGH\', \'CRITICAL\') CHARACTER SET utf8mb4 DEFAULT \'\'\'LOW\'\'\' COLLATE `utf8mb4_unicode_ci`, INDEX idx_timestamp (timestamp), INDEX idx_severity (severity), INDEX idx_user_cin (user_cin), INDEX idx_event_type (event_type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'Logs de sécurité pour surveiller les activités suspectes\' ');
        $this->addSql('CREATE TABLE taches (id_tache INT AUTO_INCREMENT NOT NULL, nom_tache VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_general_ci`, description VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_general_ci`, assignee INT NOT NULL, etat VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_general_ci`, priorite VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_general_ci`, date_echeancee DATE DEFAULT \'NULL\', INDEX assignee (assignee), PRIMARY KEY(id_tache, assignee)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE abonnements ADD CONSTRAINT abonnements_ibfk_1 FOREIGN KEY (id_offre) REFERENCES offres (id_offres) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE achat ADD CONSTRAINT fk_users_cin FOREIGN KEY (cin) REFERENCES users (cin) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE animaux ADD race VARCHAR(255) DEFAULT \'NULL\', CHANGE nom nom VARCHAR(50) DEFAULT \'NULL\', CHANGE espece espece VARCHAR(50) DEFAULT \'NULL\', CHANGE date_naissance date_naissance DATE DEFAULT \'NULL\', CHANGE sexe sexe VARCHAR(20) DEFAULT \'NULL\', CHANGE poids poids FLOAT DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE examens_sante DROP FOREIGN KEY FK_714ADD7D4C9C96F2');
        $this->addSql('ALTER TABLE examens_sante ADD animal_id INT DEFAULT NULL, CHANGE date_examen date_examen DATE DEFAULT \'NULL\', CHANGE type_examen type_examen VARCHAR(100) DEFAULT \'NULL\', CHANGE diagnostic diagnostic TEXT DEFAULT NULL, CHANGE traitement traitement VARCHAR(255) DEFAULT \'NULL\', CHANGE id_animal id_animal INT DEFAULT NULL');
        $this->addSql('ALTER TABLE examens_sante ADD CONSTRAINT fk_animal FOREIGN KEY (id_animal) REFERENCES animaux (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE examens_sante ADD CONSTRAINT examens_sante_ibfk_1 FOREIGN KEY (animal_id) REFERENCES animaux (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX animal_id ON examens_sante (animal_id)');
        $this->addSql('ALTER TABLE examens_sante RENAME INDEX idx_714add7d4c9c96f2 TO fk_animal');
        $this->addSql('ALTER TABLE plante CHANGE nom_p nom_p VARCHAR(200) NOT NULL, CHANGE variete variete VARCHAR(200) NOT NULL, CHANGE besoin_eau besoin_eau FLOAT NOT NULL, CHANGE cycle_jours cycle_jours INT NOT NULL');
        $this->addSql('ALTER TABLE rotation CHANGE status status INT NOT NULL');
        $this->addSql('CREATE INDEX id_terrain ON rotation (id_terrain, id_plante)');
        $this->addSql('ALTER TABLE rotation RENAME INDEX idx_297c98f1774ddcaa TO fk_rotation_plante');
        $this->addSql('ALTER TABLE terrain CHANGE nom_terrain nom_terrain VARCHAR(50) NOT NULL, CHANGE surface surface FLOAT NOT NULL, CHANGE type_sol type_sol VARCHAR(200) NOT NULL, CHANGE localisation localisation VARCHAR(2000) NOT NULL, CHANGE p_h p_h FLOAT NOT NULL, CHANGE cin cin INT NOT NULL');
        $this->addSql('ALTER TABLE terrain ADD CONSTRAINT terrain_ibfk_1 FOREIGN KEY (cin) REFERENCES users (cin) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('CREATE INDEX cin ON terrain (cin)');
        $this->addSql('ALTER TABLE users CHANGE nom nom VARCHAR(255) DEFAULT \'NULL\', CHANGE prenom prenom VARCHAR(255) DEFAULT \'NULL\', CHANGE tel tel VARCHAR(8) DEFAULT \'NULL\', CHANGE date_naiss date_naiss DATE DEFAULT \'NULL\', CHANGE email email VARCHAR(255) DEFAULT \'NULL\', CHANGE mdp mdp VARCHAR(255) DEFAULT \'NULL\', CHANGE adresse adresse VARCHAR(255) DEFAULT \'NULL\', CHANGE ville ville VARCHAR(255) DEFAULT \'NULL\', CHANGE date_creationcpt date_creationcpt DATE DEFAULT \'NULL\', CHANGE date_dernierchg date_dernierchg DATE DEFAULT \'NULL\', CHANGE two_factor_enabled two_factor_enabled TINYINT(1) DEFAULT 0 COMMENT \'Indique si la 2FA est activée\', CHANGE two_factor_secret two_factor_secret VARCHAR(32) DEFAULT \'NULL\' COMMENT \'Clé secrète pour Google Authenticator\', CHANGE two_factor_backup_codes two_factor_backup_codes TEXT DEFAULT NULL COMMENT \'Codes de secours hashés (séparés par virgules)\', CHANGE img img VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('CREATE INDEX idx_email_active ON users (email, two_factor_enabled)');
        $this->addSql('CREATE INDEX idx_two_factor_enabled ON users (two_factor_enabled)');
        $this->addSql('ALTER TABLE users RENAME INDEX uniq_1483a5e9e7927c74 TO email');
    }
}
