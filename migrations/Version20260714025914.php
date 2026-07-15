<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260714025914 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Cria invoice (espelho local das cobrancas do Asaas)';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE invoice (id INT AUTO_INCREMENT NOT NULL, asaas_payment_id VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, billing_type VARCHAR(255) NOT NULL, value_cents INT NOT NULL, due_date DATE NOT NULL, paid_at DATETIME DEFAULT NULL, invoice_url VARCHAR(500) DEFAULT NULL, raw_payload JSON DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, subscription_id INT NOT NULL, company_id INT NOT NULL, UNIQUE INDEX UNIQ_90651744F1DF3B39 (asaas_payment_id), INDEX IDX_906517449A1887DC (subscription_id), INDEX IDX_90651744979B1AD6 (company_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_906517449A1887DC FOREIGN KEY (subscription_id) REFERENCES subscription (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_906517449A1887DC');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_90651744979B1AD6');
        $this->addSql('DROP TABLE invoice');
    }
}
