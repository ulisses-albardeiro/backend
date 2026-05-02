<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260502190219 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE labor (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, sale_price INT DEFAULT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, company_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_E478501979B1AD6 (company_id), INDEX IDX_E47850112469DE2 (category_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE labor ADD CONSTRAINT FK_E478501979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE labor ADD CONSTRAINT FK_E47850112469DE2 FOREIGN KEY (category_id) REFERENCES labor_category (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE labor DROP FOREIGN KEY FK_E478501979B1AD6');
        $this->addSql('ALTER TABLE labor DROP FOREIGN KEY FK_E47850112469DE2');
        $this->addSql('DROP TABLE labor');
    }
}
