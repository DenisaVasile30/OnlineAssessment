<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230412164159 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE supported_assessment (id INT AUTO_INCREMENT NOT NULL, submitted_by_id INT NOT NULL, assessment_id INT NOT NULL, started_at DATETIME NOT NULL, ended_at DATETIME NOT NULL, max_grade INT DEFAULT NULL, calculated_grade DOUBLE PRECISION DEFAULT NULL, time_spent INT DEFAULT NULL, INDEX IDX_A98204C879F7D87D (submitted_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE supported_assessment ADD CONSTRAINT FK_A98204C879F7D87D FOREIGN KEY (submitted_by_id) REFERENCES `users` (id)');
//        $this->addSql('ALTER TABLE supported_quiz DROP supported_quiz_details_id, CHANGE quiz quiz INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE supported_assessment DROP FOREIGN KEY FK_A98204C879F7D87D');
        $this->addSql('DROP TABLE supported_assessment');
//        $this->addSql('ALTER TABLE supported_quiz ADD supported_quiz_details_id INT DEFAULT NULL, CHANGE quiz quiz INT DEFAULT NULL');
    }
}
