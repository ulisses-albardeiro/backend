<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260723032828 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quote ADD include_signature TINYINT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE receipt ADD include_signature TINYINT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE work_order ADD include_signature TINYINT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quote DROP include_signature');
        $this->addSql('ALTER TABLE receipt DROP include_signature');
        $this->addSql('ALTER TABLE work_order DROP include_signature');
    }
}
