<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230408185953 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
//        $this->addSql('ALTER TABLE supported_quiz CHANGE ended_at ended_at DATETIME DEFAULT NULL, CHANGE obtained_grade obtained_grade INT DEFAULT NULL');
//        $this->addSql('ALTER TABLE supported_quiz_details DROP FOREIGN KEY FK_13655442DAB09DFA');
//        $this->addSql('DROP INDEX IDX_13655442DAB09DFA ON supported_quiz_details');
//        $this->addSql('ALTER TABLE supported_quiz_details CHANGE supported_by_id supported_by_student_id INT NOT NULL');
//        $this->addSql('ALTER TABLE supported_quiz_details ADD CONSTRAINT FK_13655442587A6CE4 FOREIGN KEY (supported_by_student_id) REFERENCES `users` (id)');
//        $this->addSql('CREATE INDEX IDX_13655442587A6CE4 ON supported_quiz_details (supported_by_student_id)');
//        $this->addSql('DROP INDEX FK_1483A5E9BAD3A220 ON users');
//        $this->addSql('ALTER TABLE users DROP supported_quiz_id, DROP supported_quiz_details_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
//        $this->addSql('ALTER TABLE `users` ADD supported_quiz_id INT DEFAULT NULL, ADD supported_quiz_details_id INT NOT NULL');
//        $this->addSql('CREATE INDEX FK_1483A5E9BAD3A220 ON `users` (supported_quiz_id)');
//        $this->addSql('ALTER TABLE supported_quiz CHANGE ended_at ended_at DATETIME NOT NULL, CHANGE obtained_grade obtained_grade INT NOT NULL');
//        $this->addSql('ALTER TABLE supported_quiz_details DROP FOREIGN KEY FK_13655442587A6CE4');
//        $this->addSql('DROP INDEX IDX_13655442587A6CE4 ON supported_quiz_details');
//        $this->addSql('ALTER TABLE supported_quiz_details CHANGE supported_by_student_id supported_by_id INT NOT NULL');
//        $this->addSql('ALTER TABLE supported_quiz_details ADD CONSTRAINT FK_13655442DAB09DFA FOREIGN KEY (supported_by_id) REFERENCES users (id)');
//        $this->addSql('CREATE INDEX IDX_13655442DAB09DFA ON supported_quiz_details (supported_by_id)');
    }
}
