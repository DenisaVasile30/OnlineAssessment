<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230409135055 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE supported_quiz DROP FOREIGN KEY FK_995B8B97F650910A');
        $this->addSql('DROP INDEX IDX_995B8B97F650910A ON supported_quiz');
        $this->addSql('ALTER TABLE supported_quiz DROP supported_quiz_details_id, CHANGE quiz quiz INT NOT NULL');
        $this->addSql('ALTER TABLE supported_quiz_details DROP FOREIGN KEY FK_13655442587A6CE4');
        $this->addSql('DROP INDEX IDX_13655442587A6CE4 ON supported_quiz_details');
        $this->addSql('ALTER TABLE supported_quiz_details CHANGE supported_by_student_id supported_by_id INT NOT NULL');
        $this->addSql('ALTER TABLE supported_quiz_details ADD CONSTRAINT FK_13655442DAB09DFA FOREIGN KEY (supported_by_id) REFERENCES `users` (id)');
        $this->addSql('CREATE INDEX IDX_13655442DAB09DFA ON supported_quiz_details (supported_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE supported_quiz ADD supported_quiz_details_id INT DEFAULT NULL, CHANGE quiz quiz INT DEFAULT NULL');
        $this->addSql('ALTER TABLE supported_quiz ADD CONSTRAINT FK_995B8B97F650910A FOREIGN KEY (supported_quiz_details_id) REFERENCES supported_quiz_details (id)');
        $this->addSql('CREATE INDEX IDX_995B8B97F650910A ON supported_quiz (supported_quiz_details_id)');
        $this->addSql('ALTER TABLE supported_quiz_details DROP FOREIGN KEY FK_13655442DAB09DFA');
        $this->addSql('DROP INDEX IDX_13655442DAB09DFA ON supported_quiz_details');
        $this->addSql('ALTER TABLE supported_quiz_details CHANGE supported_by_id supported_by_student_id INT NOT NULL');
        $this->addSql('ALTER TABLE supported_quiz_details ADD CONSTRAINT FK_13655442587A6CE4 FOREIGN KEY (supported_by_student_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_13655442587A6CE4 ON supported_quiz_details (supported_by_student_id)');
    }
}
