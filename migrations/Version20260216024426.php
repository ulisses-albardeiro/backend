<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260216024426 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE receipt (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, amount INT NOT NULL, payment_date DATE NOT NULL, payment_method VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, status VARCHAR(255) NOT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, quote_id INT DEFAULT NULL, customer_id INT DEFAULT NULL, company_id INT NOT NULL, INDEX IDX_5399B645DB805178 (quote_id), INDEX IDX_5399B6459395C3F3 (customer_id), INDEX IDX_5399B645979B1AD6 (company_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE receipt ADD CONSTRAINT FK_5399B645DB805178 FOREIGN KEY (quote_id) REFERENCES quote (id)');
        $this->addSql('ALTER TABLE receipt ADD CONSTRAINT FK_5399B6459395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE receipt ADD CONSTRAINT FK_5399B645979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE receipt DROP FOREIGN KEY FK_5399B645DB805178');
        $this->addSql('ALTER TABLE receipt DROP FOREIGN KEY FK_5399B6459395C3F3');
        $this->addSql('ALTER TABLE receipt DROP FOREIGN KEY FK_5399B645979B1AD6');
        $this->addSql('DROP TABLE receipt');
    }
}
