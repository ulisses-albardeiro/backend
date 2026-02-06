<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260206205159 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE company (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, trading_name VARCHAR(255) NOT NULL, registration_number VARCHAR(15) DEFAULT NULL, state_registration VARCHAR(50) NOT NULL, email VARCHAR(50) DEFAULT NULL, phone VARCHAR(15) DEFAULT NULL, logo VARCHAR(255) DEFAULT NULL, website VARCHAR(50) DEFAULT NULL, zip_code VARCHAR(10) DEFAULT NULL, street VARCHAR(255) DEFAULT NULL, number VARCHAR(10) DEFAULT NULL, complement VARCHAR(255) DEFAULT NULL, neighborhood VARCHAR(255) DEFAULT NULL, city VARCHAR(255) NOT NULL, state VARCHAR(50) NOT NULL, owner_id INT NOT NULL, UNIQUE INDEX UNIQ_4FBF094F7E3C61F9 (owner_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094F7E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company DROP FOREIGN KEY FK_4FBF094F7E3C61F9');
        $this->addSql('DROP TABLE company');
    }
}
