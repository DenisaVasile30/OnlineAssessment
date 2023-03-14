<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230312141723 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE students DROP FOREIGN KEY FK_A4698DB29D86650F');
        $this->addSql('DROP INDEX UNIQ_A4698DB29D86650F ON students');
        $this->addSql('ALTER TABLE students CHANGE user_id_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE students ADD CONSTRAINT FK_A4698DB2A76ED395 FOREIGN KEY (user_id) REFERENCES `users` (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A4698DB2A76ED395 ON students (user_id)');
        $this->addSql('ALTER TABLE teachers DROP FOREIGN KEY FK_ED071FF69D86650F');
        $this->addSql('DROP INDEX UNIQ_ED071FF69D86650F ON teachers');
        $this->addSql('ALTER TABLE teachers CHANGE user_id_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE teachers ADD CONSTRAINT FK_ED071FF6A76ED395 FOREIGN KEY (user_id) REFERENCES `users` (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_ED071FF6A76ED395 ON teachers (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `students` DROP FOREIGN KEY FK_A4698DB2A76ED395');
        $this->addSql('DROP INDEX UNIQ_A4698DB2A76ED395 ON `students`');
        $this->addSql('ALTER TABLE `students` CHANGE user_id user_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE `students` ADD CONSTRAINT FK_A4698DB29D86650F FOREIGN KEY (user_id_id) REFERENCES users (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A4698DB29D86650F ON `students` (user_id_id)');
        $this->addSql('ALTER TABLE `teachers` DROP FOREIGN KEY FK_ED071FF6A76ED395');
        $this->addSql('DROP INDEX UNIQ_ED071FF6A76ED395 ON `teachers`');
        $this->addSql('ALTER TABLE `teachers` CHANGE user_id user_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE `teachers` ADD CONSTRAINT FK_ED071FF69D86650F FOREIGN KEY (user_id_id) REFERENCES users (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_ED071FF69D86650F ON `teachers` (user_id_id)');
    }
}
