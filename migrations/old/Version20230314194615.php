<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230314194615 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE subject (id INT AUTO_INCREMENT NOT NULL, subject VARCHAR(60) NOT NULL, created_at DATE NOT NULL, last_modified DATE DEFAULT NULL, content LONGBLOB DEFAULT NULL, subject_content VARCHAR(2000) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE students DROP FOREIGN KEY students_ibfk_4');
        $this->addSql('ALTER TABLE students ADD CONSTRAINT FK_A4698DB2FE54D947 FOREIGN KEY (group_id) REFERENCES `groups` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE subject');
        $this->addSql('ALTER TABLE `students` DROP FOREIGN KEY FK_A4698DB2FE54D947');
        $this->addSql('ALTER TABLE `students` ADD CONSTRAINT students_ibfk_4 FOREIGN KEY (group_id) REFERENCES groups (id) ON UPDATE CASCADE ON DELETE NO ACTION');
    }
}
