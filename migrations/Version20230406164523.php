<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230406164523 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE created_quiz ADD created_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE created_quiz ADD CONSTRAINT FK_A3650586B03A8386 FOREIGN KEY (created_by_id) REFERENCES `teachers` (id)');
        $this->addSql('CREATE INDEX IDX_A3650586B03A8386 ON created_quiz (created_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE created_quiz DROP FOREIGN KEY FK_A3650586B03A8386');
        $this->addSql('DROP INDEX IDX_A3650586B03A8386 ON created_quiz');
        $this->addSql('ALTER TABLE created_quiz DROP created_by_id');
    }
}
