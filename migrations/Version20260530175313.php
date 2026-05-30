<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260530175313 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE work_order ADD asset_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE work_order ADD CONSTRAINT FK_DDD2E8B75DA1941 FOREIGN KEY (asset_id) REFERENCES customer_asset (id)');
        $this->addSql('CREATE INDEX IDX_DDD2E8B75DA1941 ON work_order (asset_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE work_order DROP FOREIGN KEY FK_DDD2E8B75DA1941');
        $this->addSql('DROP INDEX IDX_DDD2E8B75DA1941 ON work_order');
        $this->addSql('ALTER TABLE work_order DROP asset_id');
    }
}
