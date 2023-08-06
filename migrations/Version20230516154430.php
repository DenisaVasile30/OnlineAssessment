<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230516154430 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `assessments` (
          id INT AUTO_INCREMENT NOT NULL,
          subject_id INT NOT NULL,
          created_by_id INT NOT NULL,
          description VARCHAR(500) NOT NULL,
          created_at DATETIME NOT NULL,
          assignee_group LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\',
          start_at DATETIME NOT NULL,
          end_at DATETIME NOT NULL,
          subject_list LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\',
          status VARCHAR(255) DEFAULT NULL,
          time_limit INT DEFAULT NULL,
          time_unit VARCHAR(255) DEFAULT NULL,
          INDEX IDX_4BFCEC0A23EDC87 (subject_id),
          INDEX IDX_4BFCEC0AB03A8386 (created_by_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE assigned_subjects (
          id INT AUTO_INCREMENT NOT NULL,
          subjects_option_list LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\',
          requirement_no INT NOT NULL,
          assessment INT NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE created_quiz (
          id INT AUTO_INCREMENT NOT NULL,
          created_by_id INT DEFAULT NULL,
          description VARCHAR(1000) NOT NULL,
          created_at DATETIME NOT NULL,
          start_at DATETIME NOT NULL,
          end_at DATETIME NOT NULL,
          time_limit INT DEFAULT NULL,
          time_unit VARCHAR(255) DEFAULT NULL,
          practice_quiz TINYINT(1) DEFAULT NULL,
          assignee_group LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\',
          questions_no INT NOT NULL,
          questions_list LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\',
          category VARCHAR(255) DEFAULT NULL,
          max_grade INT NOT NULL,
          max_points INT NOT NULL,
          questions_source VARCHAR(255) NOT NULL,
          INDEX IDX_A3650586B03A8386 (created_by_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `groups` (
          id INT AUTO_INCREMENT NOT NULL,
          group_no INT NOT NULL,
          created_date DATE NOT NULL,
          faculty VARCHAR(500) NOT NULL,
          UNIQUE INDEX group_no_idx (group_no),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `quiz_questions` (
          id INT AUTO_INCREMENT NOT NULL,
          issued_by_id INT NOT NULL,
          category VARCHAR(300) NOT NULL,
          optional_description VARCHAR(700) DEFAULT NULL,
          question_content VARCHAR(1500) NOT NULL,
          choice_a VARCHAR(400) NOT NULL,
          choice_b VARCHAR(500) NOT NULL,
          choice_c VARCHAR(500) NOT NULL,
          choice_d VARCHAR(500) DEFAULT NULL,
          correct_answer VARCHAR(500) NOT NULL,
          created_at DATETIME NOT NULL,
          content_file LONGBLOB DEFAULT NULL,
          file_name VARCHAR(255) DEFAULT NULL,
          INDEX IDX_8CBC2533784BB717 (issued_by_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `students` (
          id INT AUTO_INCREMENT NOT NULL,
          group_id INT DEFAULT NULL,
          user_id INT NOT NULL,
          enrollment_date DATE NOT NULL,
          year INT DEFAULT NULL,
          UNIQUE INDEX UNIQ_A4698DB2A76ED395 (user_id),
          INDEX IDX_A4698DB2FE54D947 (group_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `subjects` (
          id INT AUTO_INCREMENT NOT NULL,
          issued_by_id INT NOT NULL,
          description VARCHAR(200) NOT NULL,
          subject VARCHAR(60) NOT NULL,
          created_at DATE NOT NULL,
          last_modified DATE DEFAULT NULL,
          content_file LONGBLOB DEFAULT NULL,
          file_name VARCHAR(255) DEFAULT NULL,
          subject_content VARCHAR(2000) DEFAULT NULL,
          subject_requirements VARCHAR(2000) DEFAULT NULL,
          INDEX IDX_AB259917784BB717 (issued_by_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supported_assessment (
          id INT AUTO_INCREMENT NOT NULL,
          submitted_by_id INT NOT NULL,
          assessment_id INT NOT NULL,
          started_at DATETIME NOT NULL,
          ended_at DATETIME NOT NULL,
          max_grade INT DEFAULT NULL,
          calculated_grade DOUBLE PRECISION DEFAULT NULL,
          time_spent INT DEFAULT NULL,
          content_file LONGBLOB DEFAULT NULL,
          file_name VARCHAR(255) DEFAULT NULL,
          resulted_response LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\',
          INDEX IDX_A98204C879F7D87D (submitted_by_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supported_quiz (
          id INT AUTO_INCREMENT NOT NULL,
          supported_by_id INT NOT NULL,
          quiz INT NOT NULL,
          started_at DATETIME NOT NULL,
          ended_at DATETIME DEFAULT NULL,
          max_grade INT NOT NULL,
          obtained_grade DOUBLE PRECISION DEFAULT NULL,
          total_time_spent INT DEFAULT NULL,
          INDEX IDX_995B8B97DAB09DFA (supported_by_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supported_quiz_details (
          id INT AUTO_INCREMENT NOT NULL,
          supported_by_student_id INT NOT NULL,
          quiz_id INT NOT NULL,
          question_id INT NOT NULL,
          provided_answer VARCHAR(1500) NOT NULL,
          time_spent INT DEFAULT NULL,
          correct_answer VARCHAR(1500) NOT NULL,
          question_score INT NOT NULL,
          obtained_score INT DEFAULT NULL,
          INDEX IDX_13655442587A6CE4 (supported_by_student_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `teachers` (
          id INT AUTO_INCREMENT NOT NULL,
          user_id INT NOT NULL,
          enrollment_date DATE NOT NULL,
          assigned_groups LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\',
          UNIQUE INDEX UNIQ_ED071FF6A76ED395 (user_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `tickets` (
          id INT AUTO_INCREMENT NOT NULL,
          issued_by_id INT NOT NULL,
          assigned_to_id INT DEFAULT NULL,
          type VARCHAR(500) NOT NULL,
          ticket_content VARCHAR(2500) NOT NULL,
          ticket_status VARCHAR(15) NOT NULL,
          issued_at DATETIME NOT NULL,
          multiple_assign_to LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\',
          INDEX IDX_54469DF4784BB717 (issued_by_id),
          INDEX IDX_54469DF4F4BD7827 (assigned_to_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `tickets_answers` (
          id INT AUTO_INCREMENT NOT NULL,
          ticket_id INT DEFAULT NULL,
          answer VARCHAR(2500) NOT NULL,
          added_at DATETIME NOT NULL,
          answer_by VARCHAR(150) NOT NULL,
          INDEX IDX_C534AA4D700047D2 (ticket_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_profile (
          id INT AUTO_INCREMENT NOT NULL,
          user_id INT NOT NULL,
          first_name VARCHAR(255) DEFAULT NULL,
          last_name VARCHAR(100) DEFAULT NULL,
          address VARCHAR(1000) DEFAULT NULL,
          description VARCHAR(1024) DEFAULT NULL,
          date_of_birth DATE DEFAULT NULL,
          UNIQUE INDEX UNIQ_D95AB405A76ED395 (user_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `users` (
          id INT AUTO_INCREMENT NOT NULL,
          email VARCHAR(180) NOT NULL,
          roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\',
          password VARCHAR(255) NOT NULL,
          is_verified TINYINT(1) NOT NULL,
          UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          `assessments`
        ADD
          CONSTRAINT FK_4BFCEC0A23EDC87 FOREIGN KEY (subject_id) REFERENCES `subjects` (id)');
        $this->addSql('ALTER TABLE
          `assessments`
        ADD
          CONSTRAINT FK_4BFCEC0AB03A8386 FOREIGN KEY (created_by_id) REFERENCES `teachers` (id)');
        $this->addSql('ALTER TABLE
          created_quiz
        ADD
          CONSTRAINT FK_A3650586B03A8386 FOREIGN KEY (created_by_id) REFERENCES `teachers` (id)');
        $this->addSql('ALTER TABLE
          `quiz_questions`
        ADD
          CONSTRAINT FK_8CBC2533784BB717 FOREIGN KEY (issued_by_id) REFERENCES `teachers` (id)');
        $this->addSql('ALTER TABLE
          `students`
        ADD
          CONSTRAINT FK_A4698DB2A76ED395 FOREIGN KEY (user_id) REFERENCES `users` (id)');
        $this->addSql('ALTER TABLE
          `students`
        ADD
          CONSTRAINT FK_A4698DB2FE54D947 FOREIGN KEY (group_id) REFERENCES `groups` (id)');
        $this->addSql('ALTER TABLE
          `subjects`
        ADD
          CONSTRAINT FK_AB259917784BB717 FOREIGN KEY (issued_by_id) REFERENCES `teachers` (id)');
        $this->addSql('ALTER TABLE
          supported_assessment
        ADD
          CONSTRAINT FK_A98204C879F7D87D FOREIGN KEY (submitted_by_id) REFERENCES `users` (id)');
        $this->addSql('ALTER TABLE
          supported_quiz
        ADD
          CONSTRAINT FK_995B8B97DAB09DFA FOREIGN KEY (supported_by_id) REFERENCES `users` (id)');
        $this->addSql('ALTER TABLE
          supported_quiz_details
        ADD
          CONSTRAINT FK_13655442587A6CE4 FOREIGN KEY (supported_by_student_id) REFERENCES `users` (id)');
        $this->addSql('ALTER TABLE
          `teachers`
        ADD
          CONSTRAINT FK_ED071FF6A76ED395 FOREIGN KEY (user_id) REFERENCES `users` (id)');
        $this->addSql('ALTER TABLE
          `tickets`
        ADD
          CONSTRAINT FK_54469DF4784BB717 FOREIGN KEY (issued_by_id) REFERENCES `users` (id)');
        $this->addSql('ALTER TABLE
          `tickets`
        ADD
          CONSTRAINT FK_54469DF4F4BD7827 FOREIGN KEY (assigned_to_id) REFERENCES `users` (id)');
        $this->addSql('ALTER TABLE
          `tickets_answers`
        ADD
          CONSTRAINT FK_C534AA4D700047D2 FOREIGN KEY (ticket_id) REFERENCES `tickets` (id)');
        $this->addSql('ALTER TABLE
          user_profile
        ADD
          CONSTRAINT FK_D95AB405A76ED395 FOREIGN KEY (user_id) REFERENCES `users` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `assessments` DROP FOREIGN KEY FK_4BFCEC0A23EDC87');
        $this->addSql('ALTER TABLE `assessments` DROP FOREIGN KEY FK_4BFCEC0AB03A8386');
        $this->addSql('ALTER TABLE created_quiz DROP FOREIGN KEY FK_A3650586B03A8386');
        $this->addSql('ALTER TABLE `quiz_questions` DROP FOREIGN KEY FK_8CBC2533784BB717');
        $this->addSql('ALTER TABLE `students` DROP FOREIGN KEY FK_A4698DB2A76ED395');
        $this->addSql('ALTER TABLE `students` DROP FOREIGN KEY FK_A4698DB2FE54D947');
        $this->addSql('ALTER TABLE `subjects` DROP FOREIGN KEY FK_AB259917784BB717');
        $this->addSql('ALTER TABLE supported_assessment DROP FOREIGN KEY FK_A98204C879F7D87D');
        $this->addSql('ALTER TABLE supported_quiz DROP FOREIGN KEY FK_995B8B97DAB09DFA');
        $this->addSql('ALTER TABLE supported_quiz_details DROP FOREIGN KEY FK_13655442587A6CE4');
        $this->addSql('ALTER TABLE `teachers` DROP FOREIGN KEY FK_ED071FF6A76ED395');
        $this->addSql('ALTER TABLE `tickets` DROP FOREIGN KEY FK_54469DF4784BB717');
        $this->addSql('ALTER TABLE `tickets` DROP FOREIGN KEY FK_54469DF4F4BD7827');
        $this->addSql('ALTER TABLE `tickets_answers` DROP FOREIGN KEY FK_C534AA4D700047D2');
        $this->addSql('ALTER TABLE user_profile DROP FOREIGN KEY FK_D95AB405A76ED395');
        $this->addSql('DROP TABLE `assessments`');
        $this->addSql('DROP TABLE assigned_subjects');
        $this->addSql('DROP TABLE created_quiz');
        $this->addSql('DROP TABLE `groups`');
        $this->addSql('DROP TABLE `quiz_questions`');
        $this->addSql('DROP TABLE `students`');
        $this->addSql('DROP TABLE `subjects`');
        $this->addSql('DROP TABLE supported_assessment');
        $this->addSql('DROP TABLE supported_quiz');
        $this->addSql('DROP TABLE supported_quiz_details');
        $this->addSql('DROP TABLE `teachers`');
        $this->addSql('DROP TABLE `tickets`');
        $this->addSql('DROP TABLE `tickets_answers`');
        $this->addSql('DROP TABLE user_profile');
        $this->addSql('DROP TABLE `users`');
    }
}
