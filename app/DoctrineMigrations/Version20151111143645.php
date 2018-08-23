<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151111143645 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        /*Insert nom_api_type*/
        $this->addSql("INSERT INTO `nom_api_type` (`id`, `name`, `description`) VALUES
(1, 'Comercial', 'Api Comercial'),
(2, 'Gratuita', 'Api Gratuita')");

        /*Insert nom_api_type translations*/
        $this->addSql("INSERT INTO `ext_translations` (`id`, `locale`, `object_class`, `field`, `foreign_key`, `content`) VALUES
  (1, 'en', 'FrontendBundle\\\\Entity\\\\NomApiType', 'name',        '1', 'Commercial'),
  (2, 'en', 'FrontendBundle\\\\Entity\\\\NomApiType', 'description', '1', 'Commercial API'),
  (3, 'de', 'FrontendBundle\\\\Entity\\\\NomApiType', 'name',        '1', 'Kommerzielle'),
  (4, 'de', 'FrontendBundle\\\\Entity\\\\NomApiType', 'description', '1', 'Kommerzielle API'),
  (5, 'en', 'FrontendBundle\\\\Entity\\\\NomApiType', 'name',        '2', 'Free'),
  (6, 'en', 'FrontendBundle\\\\Entity\\\\NomApiType', 'description', '2', 'Free API'),
  (7, 'de', 'FrontendBundle\\\\Entity\\\\NomApiType', 'name',        '2', 'Kostenlos'),
  (8, 'de', 'FrontendBundle\\\\Entity\\\\NomApiType', 'description', '2', 'Kostenlose API')");

        /*Insert nom_application_type*/
        $this->addSql("INSERT INTO `nom_application_type` (`id`, `name`, `description`) VALUES
(1, 'Web', 'Aplicación Web'),
(2, 'Móvil', 'Aplicación Móvil'),
(3, 'Robot', 'Aplicación Robot')");

        /*Insert nom_application_type translations*/
        $this->addSql("INSERT INTO `ext_translations` (`id`, `locale`, `object_class`, `field`, `foreign_key`, `content`) VALUES
  (9,  'en', 'FrontendBundle\\\\Entity\\\\NomApplicationType', 'name',        '1', 'Web'),
  (10, 'en', 'FrontendBundle\\\\Entity\\\\NomApplicationType', 'description', '1', 'Web aplication '),
  (11, 'de', 'FrontendBundle\\\\Entity\\\\NomApplicationType', 'name',        '1', 'Anwendung'),
  (12, 'de', 'FrontendBundle\\\\Entity\\\\NomApplicationType', 'description', '1', 'Web-Anwendung'),
  (13, 'en', 'FrontendBundle\\\\Entity\\\\NomApplicationType', 'name',        '2', 'Mobile'),
  (14, 'en', 'FrontendBundle\\\\Entity\\\\NomApplicationType', 'description', '2', 'Mobile aplication'),
  (15, 'de', 'FrontendBundle\\\\Entity\\\\NomApplicationType', 'name',        '2', 'Mobil'),
  (16, 'de', 'FrontendBundle\\\\Entity\\\\NomApplicationType', 'description', '2', 'Mobile-Anwendung'),
  (17, 'en', 'FrontendBundle\\\\Entity\\\\NomApplicationType', 'name',        '3', 'Robot'),
  (18, 'en', 'FrontendBundle\\\\Entity\\\\NomApplicationType', 'description', '3', 'Robot aplication'),
  (19, 'de', 'FrontendBundle\\\\Entity\\\\NomApplicationType', 'name',        '3', 'Roboter'),
  (20, 'de', 'FrontendBundle\\\\Entity\\\\NomApplicationType', 'description', '3', 'Roboter aplication')");

        /*Insert nom_driver*/
        $this->addSql("INSERT INTO `nom_driver` (`id`, `name`, `description` ) VALUES
(1, 'pdo_mysql', 'Driver mysql'),
(2, 'pdo_pgsql', 'Driver postgresql')");

        /*Insert role admin*/
        $this->addSql("INSERT INTO `role` (`id`, `name`, `description` ) VALUES
(1, 'ROLE_USER', 'Rol del CBS'),
(2, 'ROLE_ADMIN', 'Rol administrador')");

        /*Insert role translations*/
        $this->addSql("INSERT INTO `ext_translations` (`id`, `locale`, `object_class`, `field`, `foreign_key`, `content`) VALUES
  (21, 'en', 'UserBundle\\\\Entity\\\\Role', 'description', '1', 'Role CBS'),
  (22, 'de', 'UserBundle\\\\Entity\\\\Role', 'description', '1', 'Rolle von CBS'),
  (23, 'en', 'UserBundle\\\\Entity\\\\Role', 'description', '2', 'Role administrator'),
  (24, 'de', 'UserBundle\\\\Entity\\\\Role', 'description', '2', 'Administratorrolle')");

        /*Insert admin user*/
        $salt=base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $password=md5('admin');
        $roles='a:1:{i:0;s:10:"ROLE_ADMIN";}';
        $this->addSql("INSERT INTO `user` (`username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `salt`, `password`, `last_login`, `locked`, `expired`, `expires_at`, `confirmation_token`, `password_requested_at`, `roles`, `credentials_expired`, `credentials_expire_at`) VALUES
('admin', 'admin', 'admin@gmail.com', 'admin@gmail.com', 1, 'rgr463gv90gkoo4g4cgo04o40wc4w4g', 'HRiCjhTryt8hGQwSfgO4pGrvd5JSfVf2d+t7rw8Vcgy2xBwj8mTnCoM7N3VmeF5CE3fCwWuvl8WunQMJsAPXfQ==', NULL, 0, 0, NULL, NULL, NULL,'$roles', 0, NULL)");

        /*Insert language*/
        $this->addSql("INSERT INTO `language` (`name`, `localizedName`, `code`) VALUES
('English', 'English', 'en'),
('German', 'Deutsch', 'de'),
('Spanish', 'Español', 'es')");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
