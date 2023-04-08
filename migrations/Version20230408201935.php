<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230408201935 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX FK_1483A5E9BAD3A220 ON users');
        $this->addSql('ALTER TABLE users DROP supported_quiz_id, DROP supported_quiz_details_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `users` ADD supported_quiz_id INT DEFAULT NULL, ADD supported_quiz_details_id INT NOT NULL');
        $this->addSql('CREATE INDEX FK_1483A5E9BAD3A220 ON `users` (supported_quiz_id)');
    }
}
