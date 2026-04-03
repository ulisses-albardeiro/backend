<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260403140826 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, color VARCHAR(7) DEFAULT NULL, icon VARCHAR(50) DEFAULT NULL, status TINYINT NOT NULL, company_id INT NOT NULL, parent_id INT DEFAULT NULL, INDEX IDX_64C19C1979B1AD6 (company_id), INDEX IDX_64C19C1727ACA70 (parent_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE company (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, trading_name VARCHAR(255) NOT NULL, registration_number VARCHAR(15) DEFAULT NULL, state_registration VARCHAR(50) NOT NULL, email VARCHAR(50) DEFAULT NULL, phone VARCHAR(15) DEFAULT NULL, logo VARCHAR(255) DEFAULT NULL, website VARCHAR(50) DEFAULT NULL, zip_code VARCHAR(10) DEFAULT NULL, street VARCHAR(255) DEFAULT NULL, number VARCHAR(10) DEFAULT NULL, complement VARCHAR(255) DEFAULT NULL, neighborhood VARCHAR(255) DEFAULT NULL, city VARCHAR(255) NOT NULL, state VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, owner_id INT NOT NULL, UNIQUE INDEX UNIQ_4FBF094F7E3C61F9 (owner_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE customer (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, trading_name VARCHAR(255) DEFAULT NULL, document VARCHAR(14) DEFAULT NULL, state_registration VARCHAR(20) DEFAULT NULL, email VARCHAR(180) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, zip_code VARCHAR(10) DEFAULT NULL, street VARCHAR(255) DEFAULT NULL, number VARCHAR(20) DEFAULT NULL, complement VARCHAR(255) DEFAULT NULL, neighborhood VARCHAR(100) DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, state VARCHAR(2) DEFAULT NULL, status TINYINT NOT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, company_id INT NOT NULL, INDEX IDX_81398E09979B1AD6 (company_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE password_reset_request (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(6) NOT NULL, expires_at DATETIME NOT NULL, created_at DATETIME NOT NULL, is_used TINYINT NOT NULL, user_id INT DEFAULT NULL, INDEX IDX_C5D0A95AA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE price_list (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, company_id INT NOT NULL, INDEX IDX_399A0AA2979B1AD6 (company_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE price_list_item (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, quantity NUMERIC(10, 2) NOT NULL, unit VARCHAR(255) NOT NULL, price_list_id INT NOT NULL, INDEX IDX_D964C90B5688DED7 (price_list_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE quote (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, date DATE NOT NULL, due_date DATE NOT NULL, subtotal INT NOT NULL, discount_type VARCHAR(255) NOT NULL, discount_value INT DEFAULT NULL, shipping_value INT DEFAULT NULL, total_amount INT NOT NULL, description LONGTEXT DEFAULT NULL, notes LONGTEXT DEFAULT NULL, internal_notes LONGTEXT DEFAULT NULL, customer_id INT DEFAULT NULL, company_id INT NOT NULL, INDEX IDX_6B71CBF49395C3F3 (customer_id), INDEX IDX_6B71CBF4979B1AD6 (company_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE quote_item (id INT AUTO_INCREMENT NOT NULL, description VARCHAR(255) NOT NULL, quantity NUMERIC(10, 2) NOT NULL, unit_price INT NOT NULL, total_price INT NOT NULL, quote_id INT NOT NULL, INDEX IDX_8DFC7A94DB805178 (quote_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE receipt (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, amount INT NOT NULL, payment_date DATE NOT NULL, payment_method VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, status VARCHAR(255) NOT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, quote_id INT DEFAULT NULL, customer_id INT DEFAULT NULL, company_id INT NOT NULL, INDEX IDX_5399B645DB805178 (quote_id), INDEX IDX_5399B6459395C3F3 (customer_id), INDEX IDX_5399B645979B1AD6 (company_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE transaction (id INT AUTO_INCREMENT NOT NULL, description VARCHAR(255) DEFAULT NULL, amount INT NOT NULL, date DATETIME NOT NULL, type VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, category_id INT NOT NULL, customer_id INT DEFAULT NULL, company_id INT NOT NULL, INDEX IDX_723705D112469DE2 (category_id), INDEX IDX_723705D19395C3F3 (customer_id), INDEX IDX_723705D1979B1AD6 (company_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, phone VARCHAR(11) DEFAULT NULL, name VARCHAR(255) NOT NULL, google_id VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1727ACA70 FOREIGN KEY (parent_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094F7E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E09979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE password_reset_request ADD CONSTRAINT FK_C5D0A95AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE price_list ADD CONSTRAINT FK_399A0AA2979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE price_list_item ADD CONSTRAINT FK_D964C90B5688DED7 FOREIGN KEY (price_list_id) REFERENCES price_list (id)');
        $this->addSql('ALTER TABLE quote ADD CONSTRAINT FK_6B71CBF49395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE quote ADD CONSTRAINT FK_6B71CBF4979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE quote_item ADD CONSTRAINT FK_8DFC7A94DB805178 FOREIGN KEY (quote_id) REFERENCES quote (id)');
        $this->addSql('ALTER TABLE receipt ADD CONSTRAINT FK_5399B645DB805178 FOREIGN KEY (quote_id) REFERENCES quote (id)');
        $this->addSql('ALTER TABLE receipt ADD CONSTRAINT FK_5399B6459395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE receipt ADD CONSTRAINT FK_5399B645979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D112469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D19395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C1979B1AD6');
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C1727ACA70');
        $this->addSql('ALTER TABLE company DROP FOREIGN KEY FK_4FBF094F7E3C61F9');
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E09979B1AD6');
        $this->addSql('ALTER TABLE password_reset_request DROP FOREIGN KEY FK_C5D0A95AA76ED395');
        $this->addSql('ALTER TABLE price_list DROP FOREIGN KEY FK_399A0AA2979B1AD6');
        $this->addSql('ALTER TABLE price_list_item DROP FOREIGN KEY FK_D964C90B5688DED7');
        $this->addSql('ALTER TABLE quote DROP FOREIGN KEY FK_6B71CBF49395C3F3');
        $this->addSql('ALTER TABLE quote DROP FOREIGN KEY FK_6B71CBF4979B1AD6');
        $this->addSql('ALTER TABLE quote_item DROP FOREIGN KEY FK_8DFC7A94DB805178');
        $this->addSql('ALTER TABLE receipt DROP FOREIGN KEY FK_5399B645DB805178');
        $this->addSql('ALTER TABLE receipt DROP FOREIGN KEY FK_5399B6459395C3F3');
        $this->addSql('ALTER TABLE receipt DROP FOREIGN KEY FK_5399B645979B1AD6');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D112469DE2');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D19395C3F3');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1979B1AD6');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE company');
        $this->addSql('DROP TABLE customer');
        $this->addSql('DROP TABLE password_reset_request');
        $this->addSql('DROP TABLE price_list');
        $this->addSql('DROP TABLE price_list_item');
        $this->addSql('DROP TABLE quote');
        $this->addSql('DROP TABLE quote_item');
        $this->addSql('DROP TABLE receipt');
        $this->addSql('DROP TABLE transaction');
        $this->addSql('DROP TABLE `user`');
    }
}
