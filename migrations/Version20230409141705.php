<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230409141705 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
//        $this->addSql('ALTER TABLE supported_quiz DROP supported_quiz_details_id, CHANGE obtained_grade obtained_grade DOUBLE PRECISION DEFAULT NULL, CHANGE quiz quiz INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
//        $this->addSql('ALTER TABLE supported_quiz ADD supported_quiz_details_id INT DEFAULT NULL, CHANGE quiz quiz INT DEFAULT NULL, CHANGE obtained_grade obtained_grade INT DEFAULT NULL');
    }
}
