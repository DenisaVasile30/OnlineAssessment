<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230315211836 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `assessments` (id INT AUTO_INCREMENT NOT NULL, subject_id INT NOT NULL, description VARCHAR(500) NOT NULL, created_at DATETIME NOT NULL, assignee_group LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', start_at DATETIME NOT NULL, end_at DATETIME NOT NULL, INDEX IDX_4BFCEC0A23EDC87 (subject_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `assessments` ADD CONSTRAINT FK_4BFCEC0A23EDC87 FOREIGN KEY (subject_id) REFERENCES `subjects` (id)');
        $this->addSql('DROP TABLE assessment');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE assessment (id INT AUTO_INCREMENT NOT NULL, description VARCHAR(500) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL, asignee_group LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', start_at DATETIME NOT NULL, end_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE `assessments` DROP FOREIGN KEY FK_4BFCEC0A23EDC87');
        $this->addSql('DROP TABLE `assessments`');
    }
}
