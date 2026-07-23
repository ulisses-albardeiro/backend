<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260723024254 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE signature (id INT AUTO_INCREMENT NOT NULL, file_name VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, technician_id INT NOT NULL, company_id INT NOT NULL, UNIQUE INDEX UNIQ_AE880141E6C5D496 (technician_id), INDEX IDX_AE880141979B1AD6 (company_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE technician (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, company_id INT NOT NULL, INDEX IDX_F244E948979B1AD6 (company_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE signature ADD CONSTRAINT FK_AE880141E6C5D496 FOREIGN KEY (technician_id) REFERENCES technician (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE signature ADD CONSTRAINT FK_AE880141979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE technician ADD CONSTRAINT FK_F244E948979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE signature DROP FOREIGN KEY FK_AE880141E6C5D496');
        $this->addSql('ALTER TABLE signature DROP FOREIGN KEY FK_AE880141979B1AD6');
        $this->addSql('ALTER TABLE technician DROP FOREIGN KEY FK_F244E948979B1AD6');
        $this->addSql('DROP TABLE signature');
        $this->addSql('DROP TABLE technician');
    }
}
