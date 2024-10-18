<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241002150750 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contact ADD connected_user_id INT NOT NULL');
        $this->addSql('ALTER TABLE contact DROP connected_user');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E638349E946C FOREIGN KEY (connected_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_4C62E638349E946C ON contact (connected_user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE contact DROP CONSTRAINT FK_4C62E638349E946C');
        $this->addSql('DROP INDEX IDX_4C62E638349E946C');
        $this->addSql('ALTER TABLE contact ADD connected_user VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE contact DROP connected_user_id');
    }
}
