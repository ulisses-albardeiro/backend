<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260504223535 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE work_order (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, start_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, title VARCHAR(255) NOT NULL, problem_description LONGTEXT NOT NULL, technical_report LONGTEXT NOT NULL, equipment VARCHAR(255) DEFAULT NULL, total_amount INT NOT NULL, customer_id INT NOT NULL, company_id INT NOT NULL, quote_id INT DEFAULT NULL, transaction_id INT NOT NULL, INDEX IDX_DDD2E8B79395C3F3 (customer_id), INDEX IDX_DDD2E8B7979B1AD6 (company_id), INDEX IDX_DDD2E8B7DB805178 (quote_id), UNIQUE INDEX UNIQ_DDD2E8B72FC0CB0F (transaction_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE work_order_item (id INT AUTO_INCREMENT NOT NULL, description VARCHAR(255) NOT NULL, quantity NUMERIC(10, 2) NOT NULL, unit_price INT NOT NULL, total_price INT NOT NULL, work_order_id INT NOT NULL, product_id INT DEFAULT NULL, labor_id INT DEFAULT NULL, INDEX IDX_4989B2DA582AE764 (work_order_id), INDEX IDX_4989B2DA4584665A (product_id), INDEX IDX_4989B2DAC9CF1734 (labor_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE work_order ADD CONSTRAINT FK_DDD2E8B79395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE work_order ADD CONSTRAINT FK_DDD2E8B7979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE work_order ADD CONSTRAINT FK_DDD2E8B7DB805178 FOREIGN KEY (quote_id) REFERENCES quote (id)');
        $this->addSql('ALTER TABLE work_order ADD CONSTRAINT FK_DDD2E8B72FC0CB0F FOREIGN KEY (transaction_id) REFERENCES transaction (id)');
        $this->addSql('ALTER TABLE work_order_item ADD CONSTRAINT FK_4989B2DA582AE764 FOREIGN KEY (work_order_id) REFERENCES work_order (id)');
        $this->addSql('ALTER TABLE work_order_item ADD CONSTRAINT FK_4989B2DA4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE work_order_item ADD CONSTRAINT FK_4989B2DAC9CF1734 FOREIGN KEY (labor_id) REFERENCES labor (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE work_order DROP FOREIGN KEY FK_DDD2E8B79395C3F3');
        $this->addSql('ALTER TABLE work_order DROP FOREIGN KEY FK_DDD2E8B7979B1AD6');
        $this->addSql('ALTER TABLE work_order DROP FOREIGN KEY FK_DDD2E8B7DB805178');
        $this->addSql('ALTER TABLE work_order DROP FOREIGN KEY FK_DDD2E8B72FC0CB0F');
        $this->addSql('ALTER TABLE work_order_item DROP FOREIGN KEY FK_4989B2DA582AE764');
        $this->addSql('ALTER TABLE work_order_item DROP FOREIGN KEY FK_4989B2DA4584665A');
        $this->addSql('ALTER TABLE work_order_item DROP FOREIGN KEY FK_4989B2DAC9CF1734');
        $this->addSql('DROP TABLE work_order');
        $this->addSql('DROP TABLE work_order_item');
    }
}
