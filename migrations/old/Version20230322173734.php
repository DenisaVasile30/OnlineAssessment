<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230322173734 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE assigned_subjects (id INT AUTO_INCREMENT NOT NULL, assessment_id INT NOT NULL, subjects_option_list LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', requirement_no INT NOT NULL, INDEX IDX_D6289F1EDD3DD5F1 (assessment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE assigned_subjects ADD CONSTRAINT FK_D6289F1EDD3DD5F1 FOREIGN KEY (assessment_id) REFERENCES `assessments` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE assigned_subjects DROP FOREIGN KEY FK_D6289F1EDD3DD5F1');
        $this->addSql('DROP TABLE assigned_subjects');
    }
}
