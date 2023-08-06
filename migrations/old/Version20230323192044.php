<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230323192044 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE assigned_subjects DROP FOREIGN KEY FK_D6289F1EDD3DD5F1');
        $this->addSql('DROP INDEX IDX_D6289F1EDD3DD5F1 ON assigned_subjects');
        $this->addSql('ALTER TABLE assigned_subjects CHANGE assessment_id assessment INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE assigned_subjects CHANGE assessment assessment_id INT NOT NULL');
        $this->addSql('ALTER TABLE assigned_subjects ADD CONSTRAINT FK_D6289F1EDD3DD5F1 FOREIGN KEY (assessment_id) REFERENCES assessments (id)');
        $this->addSql('CREATE INDEX IDX_D6289F1EDD3DD5F1 ON assigned_subjects (assessment_id)');
    }
}
