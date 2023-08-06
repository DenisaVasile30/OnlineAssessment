<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230406163859 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE created_quiz (id INT AUTO_INCREMENT NOT NULL, description VARCHAR(1000) NOT NULL, created_at DATETIME NOT NULL, start_at DATETIME NOT NULL, end_at DATETIME NOT NULL, time_limit INT DEFAULT NULL, time_unit VARCHAR(255) DEFAULT NULL, assignee_group LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', questions_no INT NOT NULL, questions_list LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', category VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE created_quiz');
    }
}
