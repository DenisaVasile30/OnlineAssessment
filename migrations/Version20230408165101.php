<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230408165101 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE supported_quiz ADD supported_by_id INT NOT NULL');
        $this->addSql('ALTER TABLE supported_quiz ADD CONSTRAINT FK_995B8B97DAB09DFA FOREIGN KEY (supported_by_id) REFERENCES `users` (id)');
        $this->addSql('CREATE INDEX IDX_995B8B97DAB09DFA ON supported_quiz (supported_by_id)');
        $this->addSql('ALTER TABLE supported_quiz_details ADD supported_by_id INT NOT NULL');
        $this->addSql('ALTER TABLE supported_quiz_details ADD CONSTRAINT FK_13655442DAB09DFA FOREIGN KEY (supported_by_id) REFERENCES `users` (id)');
        $this->addSql('CREATE INDEX IDX_13655442DAB09DFA ON supported_quiz_details (supported_by_id)');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9BAD3A220');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9F650910A FOREIGN KEY (supported_quiz_details_id) REFERENCES supported_quiz_details (id)');
        $this->addSql('CREATE INDEX IDX_1483A5E9F650910A ON users (supported_quiz_details_id)');
        $this->addSql('DROP INDEX fk_1483a5e9bad3a220 ON users');
        $this->addSql('CREATE INDEX IDX_1483A5E9BAD3A220 ON users (supported_quiz_id)');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9BAD3A220 FOREIGN KEY (supported_quiz_id) REFERENCES supported_quiz (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `users` DROP FOREIGN KEY FK_1483A5E9F650910A');
        $this->addSql('DROP INDEX IDX_1483A5E9F650910A ON `users`');
        $this->addSql('ALTER TABLE `users` DROP FOREIGN KEY FK_1483A5E9BAD3A220');
        $this->addSql('DROP INDEX idx_1483a5e9bad3a220 ON `users`');
        $this->addSql('CREATE INDEX FK_1483A5E9BAD3A220 ON `users` (supported_quiz_id)');
        $this->addSql('ALTER TABLE `users` ADD CONSTRAINT FK_1483A5E9BAD3A220 FOREIGN KEY (supported_quiz_id) REFERENCES supported_quiz (id)');
        $this->addSql('ALTER TABLE supported_quiz DROP FOREIGN KEY FK_995B8B97DAB09DFA');
        $this->addSql('DROP INDEX IDX_995B8B97DAB09DFA ON supported_quiz');
        $this->addSql('ALTER TABLE supported_quiz DROP supported_by_id');
        $this->addSql('ALTER TABLE supported_quiz_details DROP FOREIGN KEY FK_13655442DAB09DFA');
        $this->addSql('DROP INDEX IDX_13655442DAB09DFA ON supported_quiz_details');
        $this->addSql('ALTER TABLE supported_quiz_details DROP supported_by_id');
    }
}
