<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230312152133 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE students DROP FOREIGN KEY FK_A4698DB24A49EE21');
        $this->addSql('DROP INDEX IDX_A4698DB24A49EE21 ON students');
        $this->addSql('ALTER TABLE students DROP group_no_id');
        $this->addSql('ALTER TABLE students ADD CONSTRAINT FK_A4698DB2FE54D947 FOREIGN KEY (group_id) REFERENCES `groups` (group_no)');
        $this->addSql('CREATE INDEX IDX_A4698DB2FE54D947 ON students (group_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `students` DROP FOREIGN KEY FK_A4698DB2FE54D947');
        $this->addSql('DROP INDEX IDX_A4698DB2FE54D947 ON `students`');
        $this->addSql('ALTER TABLE `students` ADD group_no_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `students` ADD CONSTRAINT FK_A4698DB24A49EE21 FOREIGN KEY (group_no_id) REFERENCES groups (group_no)');
        $this->addSql('CREATE INDEX IDX_A4698DB24A49EE21 ON `students` (group_no_id)');
    }
}
