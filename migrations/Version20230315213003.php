<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230315213003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE assessments ADD teacher_id INT NOT NULL');
        $this->addSql('ALTER TABLE assessments ADD CONSTRAINT FK_4BFCEC0A41807E1D FOREIGN KEY (teacher_id) REFERENCES `teachers` (id)');
        $this->addSql('CREATE INDEX IDX_4BFCEC0A41807E1D ON assessments (teacher_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `assessments` DROP FOREIGN KEY FK_4BFCEC0A41807E1D');
        $this->addSql('DROP INDEX IDX_4BFCEC0A41807E1D ON `assessments`');
        $this->addSql('ALTER TABLE `assessments` DROP teacher_id');
    }
}
