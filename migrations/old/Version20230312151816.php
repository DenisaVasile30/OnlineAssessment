<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230312151816 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE students ADD group_no_id INT DEFAULT NULL, ADD group_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE students ADD CONSTRAINT FK_A4698DB24A49EE21 FOREIGN KEY (group_no_id) REFERENCES `groups` (id)');
        $this->addSql('CREATE INDEX IDX_A4698DB24A49EE21 ON students (group_no_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `students` DROP FOREIGN KEY FK_A4698DB24A49EE21');
        $this->addSql('DROP INDEX IDX_A4698DB24A49EE21 ON `students`');
        $this->addSql('ALTER TABLE `students` DROP group_no_id, DROP group_id');
    }
}
