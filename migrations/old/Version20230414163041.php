<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230414163041 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
//        $this->addSql('ALTER TABLE supported_assessment CHANGE started_at started_at DATETIME NOT NULL, CHANGE ended_at ended_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE tickets CHANGE assigned_to_id assigned_to_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `tickets` CHANGE assigned_to_id assigned_to_id INT NOT NULL');
        $this->addSql('ALTER TABLE supported_assessment CHANGE started_at started_at DATETIME DEFAULT NULL, CHANGE ended_at ended_at DATETIME DEFAULT NULL');
    }
}
