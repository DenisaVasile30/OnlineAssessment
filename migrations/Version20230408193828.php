<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230408193828 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE supported_quiz ADD supported_quiz_details_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE supported_quiz ADD CONSTRAINT FK_995B8B97F650910A FOREIGN KEY (supported_quiz_details_id) REFERENCES supported_quiz_details (id)');
        $this->addSql('CREATE INDEX IDX_995B8B97F650910A ON supported_quiz (supported_quiz_details_id)');
        $this->addSql('DROP INDEX FK_1483A5E9BAD3A220 ON users');
        $this->addSql('ALTER TABLE users DROP supported_quiz_id, DROP supported_quiz_details_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `users` ADD supported_quiz_id INT DEFAULT NULL, ADD supported_quiz_details_id INT NOT NULL');
        $this->addSql('CREATE INDEX FK_1483A5E9BAD3A220 ON `users` (supported_quiz_id)');
        $this->addSql('ALTER TABLE supported_quiz DROP FOREIGN KEY FK_995B8B97F650910A');
        $this->addSql('DROP INDEX IDX_995B8B97F650910A ON supported_quiz');
        $this->addSql('ALTER TABLE supported_quiz DROP supported_quiz_details_id');
    }
}
