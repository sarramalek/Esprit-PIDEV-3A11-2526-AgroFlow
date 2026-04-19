<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260419165733 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE users_with_2fa');
        $this->addSql('ALTER TABLE abonnements CHANGE id_abonn id_abonn INT AUTO_INCREMENT NOT NULL, ADD PRIMARY KEY (id_abonn)');
        $this->addSql('ALTER TABLE animaux CHANGE poids poids DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE animaux ADD CONSTRAINT FK_9ABE194DA76ED395 FOREIGN KEY (user_id) REFERENCES users (cin)');
        $this->addSql('CREATE INDEX IDX_9ABE194DA76ED395 ON animaux (user_id)');
        $this->addSql('ALTER TABLE article CHANGE id_article id_article INT AUTO_INCREMENT NOT NULL, CHANGE prix_unitaire prix_unitaire DOUBLE PRECISION DEFAULT NULL, ADD PRIMARY KEY (id_article)');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66C9486A13 FOREIGN KEY (id_categorie) REFERENCES categorie (id_categorie)');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E666B3CA4B FOREIGN KEY (id_user) REFERENCES users (cin)');
        $this->addSql('CREATE INDEX IDX_23A0E66C9486A13 ON article (id_categorie)');
        $this->addSql('CREATE INDEX IDX_23A0E666B3CA4B ON article (id_user)');
        $this->addSql('ALTER TABLE categorie CHANGE id_categorie id_categorie INT AUTO_INCREMENT NOT NULL, ADD PRIMARY KEY (id_categorie)');
        $this->addSql('ALTER TABLE categorie ADD CONSTRAINT FK_497DD6346B3CA4B FOREIGN KEY (id_user) REFERENCES users (cin)');
        $this->addSql('CREATE INDEX IDX_497DD6346B3CA4B ON categorie (id_user)');
        $this->addSql('ALTER TABLE categorieevenement CHANGE id_categorie id_categorie INT AUTO_INCREMENT NOT NULL, ADD PRIMARY KEY (id_categorie)');
        $this->addSql('ALTER TABLE evenement CHANGE id_evenement id_evenement INT AUTO_INCREMENT NOT NULL, ADD PRIMARY KEY (id_evenement)');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681EC9486A13 FOREIGN KEY (id_categorie) REFERENCES categorieevenement (id_categorie)');
        $this->addSql('CREATE INDEX IDX_B26681EC9486A13 ON evenement (id_categorie)');
        $this->addSql('ALTER TABLE examens_sante ADD is_reminder TINYINT(1) DEFAULT 0, ADD next_reminder_date DATE DEFAULT NULL, ADD reminder_type VARCHAR(100) DEFAULT NULL, CHANGE date_examen date_examen DATE DEFAULT NULL, CHANGE type_examen type_examen VARCHAR(100) DEFAULT NULL, CHANGE traitement traitement VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE examens_sante ADD CONSTRAINT FK_714ADD7D4C9C96F2 FOREIGN KEY (id_animal) REFERENCES animaux (id)');
        $this->addSql('CREATE INDEX IDX_714ADD7D4C9C96F2 ON examens_sante (id_animal)');
        $this->addSql('ALTER TABLE machine CHANGE idM idM INT AUTO_INCREMENT NOT NULL, CHANGE numeroSerie numeroSerie VARCHAR(255) DEFAULT NULL, CHANGE dateAchat dateAchat DATE DEFAULT NULL, ADD PRIMARY KEY (idM)');
        $this->addSql('ALTER TABLE machine ADD CONSTRAINT FK_1505DF84ABE530DA FOREIGN KEY (cin) REFERENCES users (cin) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_1505DF84ABE530DA ON machine (cin)');
        $this->addSql('ALTER TABLE maintenance CHANGE idMain idMain INT AUTO_INCREMENT NOT NULL, CHANGE dateMain dateMain DATE DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL, ADD PRIMARY KEY (idMain)');
        $this->addSql('ALTER TABLE mouvement_stock CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE motif motif VARCHAR(255) DEFAULT NULL, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE mouvement_stock ADD CONSTRAINT FK_61E2C8EB7294869C FOREIGN KEY (article_id) REFERENCES article (id_article)');
        $this->addSql('ALTER TABLE mouvement_stock ADD CONSTRAINT FK_61E2C8EB6B3CA4B FOREIGN KEY (id_user) REFERENCES users (cin)');
        $this->addSql('CREATE INDEX IDX_61E2C8EB7294869C ON mouvement_stock (article_id)');
        $this->addSql('CREATE INDEX IDX_61E2C8EB6B3CA4B ON mouvement_stock (id_user)');
        $this->addSql('ALTER TABLE offres CHANGE id_offres id_offres INT AUTO_INCREMENT NOT NULL, CHANGE nom_offre nom_offre VARCHAR(255) DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE prix prix DOUBLE PRECISION DEFAULT NULL, ADD PRIMARY KEY (id_offres)');
        $this->addSql('ALTER TABLE participation CHANGE id_participation id_participation INT AUTO_INCREMENT NOT NULL, ADD PRIMARY KEY (id_participation)');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24F8B13D439 FOREIGN KEY (id_evenement) REFERENCES evenement (id_evenement)');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24F6B3CA4B FOREIGN KEY (id_user) REFERENCES users (cin)');
        $this->addSql('CREATE INDEX IDX_AB55E24F8B13D439 ON participation (id_evenement)');
        $this->addSql('CREATE INDEX IDX_AB55E24F6B3CA4B ON participation (id_user)');
        $this->addSql('ALTER TABLE plante CHANGE id_plante id_plante INT AUTO_INCREMENT NOT NULL, CHANGE variete variete VARCHAR(100) DEFAULT NULL, CHANGE besoin_eau besoin_eau DOUBLE PRECISION DEFAULT NULL, ADD PRIMARY KEY (id_plante)');
        $this->addSql('ALTER TABLE rotation CHANGE id_rotation id_rotation INT AUTO_INCREMENT NOT NULL, CHANGE date_debut_t date_debut_t DATE DEFAULT NULL, CHANGE date_fin_t date_fin_t DATE DEFAULT NULL, ADD PRIMARY KEY (id_rotation)');
        $this->addSql('ALTER TABLE rotation ADD CONSTRAINT FK_297C98F116EBFAC1 FOREIGN KEY (id_terrain) REFERENCES terrain (id_terrain)');
        $this->addSql('ALTER TABLE rotation ADD CONSTRAINT FK_297C98F1774DDCAA FOREIGN KEY (id_plante) REFERENCES plante (id_plante)');
        $this->addSql('CREATE INDEX IDX_297C98F116EBFAC1 ON rotation (id_terrain)');
        $this->addSql('CREATE INDEX IDX_297C98F1774DDCAA ON rotation (id_plante)');
        $this->addSql('ALTER TABLE taches CHANGE id_tache id_tache INT AUTO_INCREMENT NOT NULL, CHANGE nom_tache nom_tache VARCHAR(255) DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE etat etat VARCHAR(255) DEFAULT NULL, CHANGE priorite priorite VARCHAR(255) DEFAULT NULL, CHANGE date_echeancee date_echeancee DATE DEFAULT NULL, ADD PRIMARY KEY (id_tache)');
        $this->addSql('ALTER TABLE taches ADD CONSTRAINT FK_3BF2CD987C9DFC0C FOREIGN KEY (assignee) REFERENCES users (cin)');
        $this->addSql('CREATE INDEX IDX_3BF2CD987C9DFC0C ON taches (assignee)');
        $this->addSql('ALTER TABLE terrain CHANGE id_terrain id_terrain INT AUTO_INCREMENT NOT NULL, CHANGE nom_terrain nom_terrain VARCHAR(50) DEFAULT NULL, CHANGE surface surface DOUBLE PRECISION DEFAULT NULL, CHANGE type_sol type_sol VARCHAR(200) DEFAULT NULL, CHANGE localisation localisation VARCHAR(2000) DEFAULT NULL, CHANGE p_h p_h DOUBLE PRECISION DEFAULT NULL, ADD PRIMARY KEY (id_terrain)');
        $this->addSql('ALTER TABLE users CHANGE nom nom VARCHAR(255) DEFAULT NULL, CHANGE prenom prenom VARCHAR(255) DEFAULT NULL, CHANGE tel tel VARCHAR(8) DEFAULT NULL, CHANGE date_naiss date_naiss DATE DEFAULT NULL, CHANGE email email VARCHAR(255) DEFAULT NULL, CHANGE mdp mdp VARCHAR(255) DEFAULT NULL, CHANGE adresse adresse VARCHAR(255) DEFAULT NULL, CHANGE ville ville VARCHAR(255) DEFAULT NULL, CHANGE date_creationcpt date_creationcpt DATE DEFAULT NULL, CHANGE date_dernierchg date_dernierchg DATE DEFAULT NULL, CHANGE two_factor_secret two_factor_secret VARCHAR(32) DEFAULT NULL, CHANGE img img VARCHAR(255) DEFAULT \'default.png\' NOT NULL, ADD PRIMARY KEY (cin)');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E916EBFAC1 FOREIGN KEY (id_terrain) REFERENCES terrain (id_terrain) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('CREATE INDEX IDX_1483A5E916EBFAC1 ON users (id_terrain)');
        $this->addSql('ALTER TABLE messenger_messages CHANGE id id BIGINT AUTO_INCREMENT NOT NULL, CHANGE delivered_at delivered_at DATETIME DEFAULT NULL, ADD PRIMARY KEY (id)');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE users_with_2fa (cin INT DEFAULT NULL, nom_complet VARCHAR(511) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_general_ci`, email VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_general_ci`, role INT DEFAULT NULL, two_factor_enabled TINYINT(1) DEFAULT NULL, date_creation DATE DEFAULT \'NULL\') DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE abonnements MODIFY id_abonn INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON abonnements');
        $this->addSql('ALTER TABLE abonnements CHANGE id_abonn id_abonn INT NOT NULL');
        $this->addSql('ALTER TABLE animaux DROP FOREIGN KEY FK_9ABE194DA76ED395');
        $this->addSql('DROP INDEX IDX_9ABE194DA76ED395 ON animaux');
        $this->addSql('ALTER TABLE animaux CHANGE poids poids DOUBLE PRECISION DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE article MODIFY id_article INT NOT NULL');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66C9486A13');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E666B3CA4B');
        $this->addSql('DROP INDEX IDX_23A0E66C9486A13 ON article');
        $this->addSql('DROP INDEX IDX_23A0E666B3CA4B ON article');
        $this->addSql('DROP INDEX `primary` ON article');
        $this->addSql('ALTER TABLE article CHANGE id_article id_article INT NOT NULL, CHANGE prix_unitaire prix_unitaire DOUBLE PRECISION DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE categorie MODIFY id_categorie INT NOT NULL');
        $this->addSql('ALTER TABLE categorie DROP FOREIGN KEY FK_497DD6346B3CA4B');
        $this->addSql('DROP INDEX IDX_497DD6346B3CA4B ON categorie');
        $this->addSql('DROP INDEX `primary` ON categorie');
        $this->addSql('ALTER TABLE categorie CHANGE id_categorie id_categorie INT NOT NULL');
        $this->addSql('ALTER TABLE categorieevenement MODIFY id_categorie INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON categorieevenement');
        $this->addSql('ALTER TABLE categorieevenement CHANGE id_categorie id_categorie INT NOT NULL');
        $this->addSql('ALTER TABLE evenement MODIFY id_evenement INT NOT NULL');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681EC9486A13');
        $this->addSql('DROP INDEX IDX_B26681EC9486A13 ON evenement');
        $this->addSql('DROP INDEX `primary` ON evenement');
        $this->addSql('ALTER TABLE evenement CHANGE id_evenement id_evenement INT NOT NULL');
        $this->addSql('ALTER TABLE examens_sante DROP FOREIGN KEY FK_714ADD7D4C9C96F2');
        $this->addSql('DROP INDEX IDX_714ADD7D4C9C96F2 ON examens_sante');
        $this->addSql('ALTER TABLE examens_sante DROP is_reminder, DROP next_reminder_date, DROP reminder_type, CHANGE date_examen date_examen DATE DEFAULT \'NULL\', CHANGE type_examen type_examen VARCHAR(100) DEFAULT \'NULL\', CHANGE traitement traitement VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE machine MODIFY idM INT NOT NULL');
        $this->addSql('ALTER TABLE machine DROP FOREIGN KEY FK_1505DF84ABE530DA');
        $this->addSql('DROP INDEX IDX_1505DF84ABE530DA ON machine');
        $this->addSql('DROP INDEX `primary` ON machine');
        $this->addSql('ALTER TABLE machine CHANGE idM idM INT NOT NULL, CHANGE numeroSerie numeroSerie VARCHAR(255) DEFAULT \'NULL\', CHANGE dateAchat dateAchat DATE DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE maintenance MODIFY idMain INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON maintenance');
        $this->addSql('ALTER TABLE maintenance CHANGE idMain idMain INT NOT NULL, CHANGE dateMain dateMain DATE DEFAULT \'NULL\', CHANGE description description VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE messenger_messages MODIFY id BIGINT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON messenger_messages');
        $this->addSql('DROP INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages');
        $this->addSql('ALTER TABLE messenger_messages CHANGE id id BIGINT NOT NULL, CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE mouvement_stock MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE mouvement_stock DROP FOREIGN KEY FK_61E2C8EB7294869C');
        $this->addSql('ALTER TABLE mouvement_stock DROP FOREIGN KEY FK_61E2C8EB6B3CA4B');
        $this->addSql('DROP INDEX IDX_61E2C8EB7294869C ON mouvement_stock');
        $this->addSql('DROP INDEX IDX_61E2C8EB6B3CA4B ON mouvement_stock');
        $this->addSql('DROP INDEX `primary` ON mouvement_stock');
        $this->addSql('ALTER TABLE mouvement_stock CHANGE id id INT NOT NULL, CHANGE motif motif VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE offres MODIFY id_offres INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON offres');
        $this->addSql('ALTER TABLE offres CHANGE id_offres id_offres INT NOT NULL, CHANGE nom_offre nom_offre VARCHAR(255) DEFAULT \'NULL\', CHANGE description description VARCHAR(255) DEFAULT \'NULL\', CHANGE prix prix DOUBLE PRECISION DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE participation MODIFY id_participation INT NOT NULL');
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24F8B13D439');
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24F6B3CA4B');
        $this->addSql('DROP INDEX IDX_AB55E24F8B13D439 ON participation');
        $this->addSql('DROP INDEX IDX_AB55E24F6B3CA4B ON participation');
        $this->addSql('DROP INDEX `primary` ON participation');
        $this->addSql('ALTER TABLE participation CHANGE id_participation id_participation INT NOT NULL');
        $this->addSql('ALTER TABLE plante MODIFY id_plante INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON plante');
        $this->addSql('ALTER TABLE plante CHANGE id_plante id_plante INT NOT NULL, CHANGE variete variete VARCHAR(100) DEFAULT \'NULL\', CHANGE besoin_eau besoin_eau DOUBLE PRECISION DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE rotation MODIFY id_rotation INT NOT NULL');
        $this->addSql('ALTER TABLE rotation DROP FOREIGN KEY FK_297C98F116EBFAC1');
        $this->addSql('ALTER TABLE rotation DROP FOREIGN KEY FK_297C98F1774DDCAA');
        $this->addSql('DROP INDEX IDX_297C98F116EBFAC1 ON rotation');
        $this->addSql('DROP INDEX IDX_297C98F1774DDCAA ON rotation');
        $this->addSql('DROP INDEX `primary` ON rotation');
        $this->addSql('ALTER TABLE rotation CHANGE id_rotation id_rotation INT NOT NULL, CHANGE date_debut_t date_debut_t DATE DEFAULT \'NULL\', CHANGE date_fin_t date_fin_t DATE DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE taches MODIFY id_tache INT NOT NULL');
        $this->addSql('ALTER TABLE taches DROP FOREIGN KEY FK_3BF2CD987C9DFC0C');
        $this->addSql('DROP INDEX IDX_3BF2CD987C9DFC0C ON taches');
        $this->addSql('DROP INDEX `primary` ON taches');
        $this->addSql('ALTER TABLE taches CHANGE id_tache id_tache INT NOT NULL, CHANGE nom_tache nom_tache VARCHAR(255) DEFAULT \'NULL\', CHANGE description description VARCHAR(255) DEFAULT \'NULL\', CHANGE etat etat VARCHAR(255) DEFAULT \'NULL\', CHANGE priorite priorite VARCHAR(255) DEFAULT \'NULL\', CHANGE date_echeancee date_echeancee DATE DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE terrain MODIFY id_terrain INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON terrain');
        $this->addSql('ALTER TABLE terrain CHANGE id_terrain id_terrain INT NOT NULL, CHANGE nom_terrain nom_terrain VARCHAR(50) DEFAULT \'NULL\', CHANGE surface surface DOUBLE PRECISION DEFAULT \'NULL\', CHANGE type_sol type_sol VARCHAR(200) DEFAULT \'NULL\', CHANGE localisation localisation VARCHAR(2000) DEFAULT \'NULL\', CHANGE p_h p_h DOUBLE PRECISION DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E916EBFAC1');
        $this->addSql('DROP INDEX UNIQ_1483A5E9E7927C74 ON users');
        $this->addSql('DROP INDEX IDX_1483A5E916EBFAC1 ON users');
        $this->addSql('DROP INDEX `primary` ON users');
        $this->addSql('ALTER TABLE users CHANGE nom nom VARCHAR(255) DEFAULT \'NULL\', CHANGE prenom prenom VARCHAR(255) DEFAULT \'NULL\', CHANGE tel tel VARCHAR(8) DEFAULT \'NULL\', CHANGE date_naiss date_naiss DATE DEFAULT \'NULL\', CHANGE email email VARCHAR(255) DEFAULT \'NULL\', CHANGE mdp mdp VARCHAR(255) DEFAULT \'NULL\', CHANGE adresse adresse VARCHAR(255) DEFAULT \'NULL\', CHANGE ville ville VARCHAR(255) DEFAULT \'NULL\', CHANGE date_creationcpt date_creationcpt DATE DEFAULT \'NULL\', CHANGE date_dernierchg date_dernierchg DATE DEFAULT \'NULL\', CHANGE two_factor_secret two_factor_secret VARCHAR(32) DEFAULT \'NULL\', CHANGE img img VARCHAR(255) DEFAULT \'\'\'default.png\'\'\' NOT NULL');
    }
}
