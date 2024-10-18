<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241018162735 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE devis_service (devis_id INT NOT NULL, service_id INT NOT NULL, PRIMARY KEY(devis_id, service_id))');
        $this->addSql('CREATE INDEX IDX_7373018E41DEFADA ON devis_service (devis_id)');
        $this->addSql('CREATE INDEX IDX_7373018EED5CA9E6 ON devis_service (service_id)');
        $this->addSql('ALTER TABLE devis_service ADD CONSTRAINT FK_7373018E41DEFADA FOREIGN KEY (devis_id) REFERENCES devis (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE devis_service ADD CONSTRAINT FK_7373018EED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE devis ADD services_names TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE devis DROP services');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE devis_service DROP CONSTRAINT FK_7373018E41DEFADA');
        $this->addSql('ALTER TABLE devis_service DROP CONSTRAINT FK_7373018EED5CA9E6');
        $this->addSql('DROP TABLE devis_service');
        $this->addSql('ALTER TABLE devis ADD services VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE devis DROP services_names');
    }
}
