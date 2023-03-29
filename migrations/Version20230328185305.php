<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230328185305 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quiz_questions ADD issued_by_id INT NOT NULL');
        $this->addSql('ALTER TABLE quiz_questions ADD CONSTRAINT FK_8CBC2533784BB717 FOREIGN KEY (issued_by_id) REFERENCES `teachers` (id)');
        $this->addSql('CREATE INDEX IDX_8CBC2533784BB717 ON quiz_questions (issued_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `quiz_questions` DROP FOREIGN KEY FK_8CBC2533784BB717');
        $this->addSql('DROP INDEX IDX_8CBC2533784BB717 ON `quiz_questions`');
        $this->addSql('ALTER TABLE `quiz_questions` DROP issued_by_id');
    }
}
