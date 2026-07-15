<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260714024357 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Cria plan e subscription, e semeia os 3 planos iniciais (Mensal, Trimestral, Anual)';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE plan (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(50) NOT NULL, price_cents INT NOT NULL, billing_cycle VARCHAR(255) NOT NULL, trial_days INT NOT NULL, active TINYINT NOT NULL, sort_order INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_DD5A5B7D77153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE subscription (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) NOT NULL, billing_type VARCHAR(255) NOT NULL, asaas_customer_id VARCHAR(255) DEFAULT NULL, asaas_subscription_id VARCHAR(255) DEFAULT NULL, credit_card_token VARCHAR(255) DEFAULT NULL, card_last_four VARCHAR(4) DEFAULT NULL, card_brand VARCHAR(50) DEFAULT NULL, trial_ends_at DATETIME DEFAULT NULL, current_period_end DATETIME DEFAULT NULL, canceled_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, company_id INT NOT NULL, plan_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_A3C664D3553DE289 (asaas_subscription_id), UNIQUE INDEX UNIQ_A3C664D3979B1AD6 (company_id), INDEX IDX_A3C664D3E899029B (plan_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D3979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D3E899029B FOREIGN KEY (plan_id) REFERENCES plan (id)');

        $this->addSql("INSERT INTO plan (name, code, price_cents, billing_cycle, trial_days, active, sort_order, created_at) VALUES
            ('Mensal', 'monthly', 4000, 'monthly', 14, 1, 1, NOW()),
            ('Trimestral', 'quarterly', 10900, 'quarterly', 14, 1, 2, NOW()),
            ('Anual', 'yearly', 40000, 'yearly', 14, 1, 3, NOW())");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D3979B1AD6');
        $this->addSql('ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D3E899029B');
        $this->addSql('DROP TABLE plan');
        $this->addSql('DROP TABLE subscription');
    }
}
