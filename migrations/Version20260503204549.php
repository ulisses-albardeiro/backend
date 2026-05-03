<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260503204549 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quote_item ADD product_id INT DEFAULT NULL, ADD labor_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE quote_item ADD CONSTRAINT FK_8DFC7A944584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE quote_item ADD CONSTRAINT FK_8DFC7A94C9CF1734 FOREIGN KEY (labor_id) REFERENCES labor (id)');
        $this->addSql('CREATE INDEX IDX_8DFC7A944584665A ON quote_item (product_id)');
        $this->addSql('CREATE INDEX IDX_8DFC7A94C9CF1734 ON quote_item (labor_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quote_item DROP FOREIGN KEY FK_8DFC7A944584665A');
        $this->addSql('ALTER TABLE quote_item DROP FOREIGN KEY FK_8DFC7A94C9CF1734');
        $this->addSql('DROP INDEX IDX_8DFC7A944584665A ON quote_item');
        $this->addSql('DROP INDEX IDX_8DFC7A94C9CF1734 ON quote_item');
        $this->addSql('ALTER TABLE quote_item DROP product_id, DROP labor_id');
    }
}
