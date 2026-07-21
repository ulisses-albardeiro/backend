<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260721032846 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE work_order_item_image (id INT AUTO_INCREMENT NOT NULL, is_main TINYINT DEFAULT NULL, sort_order INT DEFAULT NULL, path VARCHAR(255) NOT NULL, work_order_item_id INT NOT NULL, INDEX IDX_79BDC300A4C88221 (work_order_item_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE work_order_item_image ADD CONSTRAINT FK_79BDC300A4C88221 FOREIGN KEY (work_order_item_id) REFERENCES work_order_item (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE work_order_item_image DROP FOREIGN KEY FK_79BDC300A4C88221');
        $this->addSql('DROP TABLE work_order_item_image');
    }
}
