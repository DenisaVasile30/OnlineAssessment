<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230409095240 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
//        $this->addSql('ALTER TABLE created_quiz ADD max_points INT NOT NULL');
//        $this->addSql('ALTER TABLE supported_quiz CHANGE quiz quiz INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
//        $this->addSql('ALTER TABLE supported_quiz CHANGE quiz quiz INT DEFAULT NULL');
//        $this->addSql('ALTER TABLE created_quiz DROP max_points');
    }
}
