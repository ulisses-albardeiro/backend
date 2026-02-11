<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260211003515 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE customer (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, trading_name VARCHAR(255) DEFAULT NULL, document VARCHAR(14) DEFAULT NULL, state_registration VARCHAR(20) NOT NULL, email VARCHAR(180) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, zip_code VARCHAR(10) NOT NULL, street VARCHAR(255) DEFAULT NULL, number VARCHAR(20) DEFAULT NULL, complement VARCHAR(255) DEFAULT NULL, neighborhood VARCHAR(100) DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, state VARCHAR(2) DEFAULT NULL, status TINYINT NOT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, company_id INT NOT NULL, INDEX IDX_81398E09979B1AD6 (company_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E09979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E09979B1AD6');
        $this->addSql('DROP TABLE customer');
    }
}
