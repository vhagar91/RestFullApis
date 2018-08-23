<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151111143451 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE ext_translations (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(8) NOT NULL, object_class VARCHAR(255) NOT NULL, field VARCHAR(32) NOT NULL, foreign_key VARCHAR(64) NOT NULL, content LONGTEXT DEFAULT NULL, INDEX translations_lookup_idx (locale, object_class, foreign_key), UNIQUE INDEX lookup_unique_idx (locale, object_class, field, foreign_key), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ext_log_entries (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(8) NOT NULL, logged_at DATETIME NOT NULL, object_id VARCHAR(64) DEFAULT NULL, object_class VARCHAR(255) NOT NULL, version INT NOT NULL, data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', username VARCHAR(255) DEFAULT NULL, INDEX log_class_lookup_idx (object_class), INDEX log_date_lookup_idx (logged_at), INDEX log_user_lookup_idx (username), INDEX log_version_lookup_idx (object_id, object_class, version), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dat_project (id INT AUTO_INCREMENT NOT NULL, nomapplicationtype_id INT NOT NULL, user_id INT NOT NULL, name VARCHAR(100) NOT NULL, description VARCHAR(255) DEFAULT NULL, INDEX IDX_2639473B158983B5 (nomapplicationtype_id), INDEX IDX_2639473BA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dat_url (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, dir VARCHAR(255) NOT NULL, INDEX IDX_B536AE30166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE language (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, localizedName VARCHAR(255) NOT NULL, code VARCHAR(2) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dat_api_project (id INT AUTO_INCREMENT NOT NULL, api_id INT NOT NULL, project_id INT NOT NULL, apikey VARCHAR(255) NOT NULL, INDEX IDX_93EB8B3254963938 (api_id), INDEX IDX_93EB8B32166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dat_api (id INT AUTO_INCREMENT NOT NULL, nomdriver_id INT NOT NULL, nomapitype_id INT NOT NULL, name VARCHAR(100) NOT NULL, description VARCHAR(255) DEFAULT NULL, db_host VARCHAR(255) NOT NULL, db_port INT NOT NULL, db_name VARCHAR(255) NOT NULL, db_user VARCHAR(255) NOT NULL, db_password VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, status INT DEFAULT NULL, INDEX IDX_EC45339178C8B9E0 (nomdriver_id), INDEX IDX_EC45339163E5FCCC (nomapitype_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE nom_application_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE nom_api_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE nom_driver (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, username_canonical VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, email_canonical VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, locked TINYINT(1) NOT NULL, expired TINYINT(1) NOT NULL, expires_at DATETIME DEFAULT NULL, confirmation_token VARCHAR(255) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', credentials_expired TINYINT(1) NOT NULL, credentials_expire_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D64992FC23A8 (username_canonical), UNIQUE INDEX UNIQ_8D93D649A0D96FBF (email_canonical), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE dat_project ADD CONSTRAINT FK_2639473B158983B5 FOREIGN KEY (nomapplicationtype_id) REFERENCES nom_application_type (id)');
        $this->addSql('ALTER TABLE dat_project ADD CONSTRAINT FK_2639473BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE dat_url ADD CONSTRAINT FK_B536AE30166D1F9C FOREIGN KEY (project_id) REFERENCES dat_project (id)');
        $this->addSql('ALTER TABLE dat_api_project ADD CONSTRAINT FK_93EB8B3254963938 FOREIGN KEY (api_id) REFERENCES dat_api (id)');
        $this->addSql('ALTER TABLE dat_api_project ADD CONSTRAINT FK_93EB8B32166D1F9C FOREIGN KEY (project_id) REFERENCES dat_project (id)');
        $this->addSql('ALTER TABLE dat_api ADD CONSTRAINT FK_EC45339178C8B9E0 FOREIGN KEY (nomdriver_id) REFERENCES nom_driver (id)');
        $this->addSql('ALTER TABLE dat_api ADD CONSTRAINT FK_EC45339163E5FCCC FOREIGN KEY (nomapitype_id) REFERENCES nom_api_type (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE dat_url DROP FOREIGN KEY FK_B536AE30166D1F9C');
        $this->addSql('ALTER TABLE dat_api_project DROP FOREIGN KEY FK_93EB8B32166D1F9C');
        $this->addSql('ALTER TABLE dat_api_project DROP FOREIGN KEY FK_93EB8B3254963938');
        $this->addSql('ALTER TABLE dat_project DROP FOREIGN KEY FK_2639473B158983B5');
        $this->addSql('ALTER TABLE dat_api DROP FOREIGN KEY FK_EC45339163E5FCCC');
        $this->addSql('ALTER TABLE dat_api DROP FOREIGN KEY FK_EC45339178C8B9E0');
        $this->addSql('ALTER TABLE dat_project DROP FOREIGN KEY FK_2639473BA76ED395');
        $this->addSql('DROP TABLE ext_translations');
        $this->addSql('DROP TABLE ext_log_entries');
        $this->addSql('DROP TABLE dat_project');
        $this->addSql('DROP TABLE dat_url');
        $this->addSql('DROP TABLE language');
        $this->addSql('DROP TABLE dat_api_project');
        $this->addSql('DROP TABLE dat_api');
        $this->addSql('DROP TABLE nom_application_type');
        $this->addSql('DROP TABLE nom_api_type');
        $this->addSql('DROP TABLE nom_driver');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE role');
    }
}
