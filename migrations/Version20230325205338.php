<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230325205338 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `tickets_answers` (id INT AUTO_INCREMENT NOT NULL, answer_by_id INT DEFAULT NULL, answer VARCHAR(2500) NOT NULL, added_at DATETIME NOT NULL, INDEX IDX_C534AA4D31DDC957 (answer_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `tickets_answers` ADD CONSTRAINT FK_C534AA4D31DDC957 FOREIGN KEY (answer_by_id) REFERENCES `tickets` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `tickets_answers` DROP FOREIGN KEY FK_C534AA4D31DDC957');
        $this->addSql('DROP TABLE `tickets_answers`');
    }
}
