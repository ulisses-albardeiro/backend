<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260502185038 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE labor_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, color VARCHAR(50) DEFAULT NULL, icon VARCHAR(50) DEFAULT NULL, status VARCHAR(255) NOT NULL, company_id INT NOT NULL, parent_id INT DEFAULT NULL, INDEX IDX_9F8AE9DB979B1AD6 (company_id), INDEX IDX_9F8AE9DB727ACA70 (parent_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE labor_category ADD CONSTRAINT FK_9F8AE9DB979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE labor_category ADD CONSTRAINT FK_9F8AE9DB727ACA70 FOREIGN KEY (parent_id) REFERENCES labor_category (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE labor_category DROP FOREIGN KEY FK_9F8AE9DB979B1AD6');
        $this->addSql('ALTER TABLE labor_category DROP FOREIGN KEY FK_9F8AE9DB727ACA70');
        $this->addSql('DROP TABLE labor_category');
    }
}
