<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230328184015 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `quiz_questions` (id INT AUTO_INCREMENT NOT NULL, category VARCHAR(300) NOT NULL, optional_description VARCHAR(700) DEFAULT NULL, question_content VARCHAR(1500) NOT NULL, choice_a VARCHAR(400) NOT NULL, choice_b VARCHAR(500) NOT NULL, choice_cl VARCHAR(500) NOT NULL, choice_d VARCHAR(500) NOT NULL, correct_answer VARCHAR(7) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('DROP TABLE quiz_question');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE quiz_question (id INT AUTO_INCREMENT NOT NULL, category VARCHAR(300) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, optional_description VARCHAR(700) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, question_content VARCHAR(1500) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, choice_a VARCHAR(400) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, choice_b VARCHAR(500) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, choice_cl VARCHAR(500) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, choice_d VARCHAR(500) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, correct_answer VARCHAR(7) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE `quiz_questions`');
    }
}
