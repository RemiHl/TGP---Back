<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241018124443 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE devis_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE service_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE devis (id INT NOT NULL, nom VARCHAR(255) DEFAULT NULL, prenom VARCHAR(255) DEFAULT NULL, email VARCHAR(255) NOT NULL, entreprise VARCHAR(255) DEFAULT NULL, localisation VARCHAR(255) NOT NULL, services VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE service (id INT NOT NULL, nom_du_service VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE devis_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE service_id_seq CASCADE');
        $this->addSql('DROP TABLE devis');
        $this->addSql('DROP TABLE service');
    }
}
