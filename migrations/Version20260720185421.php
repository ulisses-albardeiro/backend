<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260720185421 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE quote_item_image (id INT AUTO_INCREMENT NOT NULL, is_main TINYINT DEFAULT NULL, sort_order INT DEFAULT NULL, path VARCHAR(255) NOT NULL, quote_item_id INT NOT NULL, INDEX IDX_5FCD6534FD80FADA (quote_item_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE quote_item_image ADD CONSTRAINT FK_5FCD6534FD80FADA FOREIGN KEY (quote_item_id) REFERENCES quote_item (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quote_item_image DROP FOREIGN KEY FK_5FCD6534FD80FADA');
        $this->addSql('DROP TABLE quote_item_image');
    }
}
