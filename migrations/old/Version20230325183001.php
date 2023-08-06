<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230325183001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `tickets` (id INT AUTO_INCREMENT NOT NULL, issued_by_id INT NOT NULL, assigned_to_id INT NOT NULL, type VARCHAR(500) NOT NULL, ticket_content VARCHAR(2500) NOT NULL, ticket_status VARCHAR(15) NOT NULL, issued_at DATETIME NOT NULL, INDEX IDX_54469DF4784BB717 (issued_by_id), INDEX IDX_54469DF4F4BD7827 (assigned_to_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `tickets` ADD CONSTRAINT FK_54469DF4784BB717 FOREIGN KEY (issued_by_id) REFERENCES `users` (id)');
        $this->addSql('ALTER TABLE `tickets` ADD CONSTRAINT FK_54469DF4F4BD7827 FOREIGN KEY (assigned_to_id) REFERENCES `users` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `tickets` DROP FOREIGN KEY FK_54469DF4784BB717');
        $this->addSql('ALTER TABLE `tickets` DROP FOREIGN KEY FK_54469DF4F4BD7827');
        $this->addSql('DROP TABLE `tickets`');
    }
}
