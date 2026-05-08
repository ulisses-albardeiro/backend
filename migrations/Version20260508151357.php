<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260508151357 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_asset ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME DEFAULT NULL, ADD company_id INT NOT NULL');
        $this->addSql('ALTER TABLE customer_asset ADD CONSTRAINT FK_CC9A2F67979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('CREATE INDEX IDX_CC9A2F67979B1AD6 ON customer_asset (company_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_asset DROP FOREIGN KEY FK_CC9A2F67979B1AD6');
        $this->addSql('DROP INDEX IDX_CC9A2F67979B1AD6 ON customer_asset');
        $this->addSql('ALTER TABLE customer_asset DROP created_at, DROP updated_at, DROP company_id');
    }
}
