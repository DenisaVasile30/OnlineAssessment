<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230326062043 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tickets_answers DROP FOREIGN KEY FK_C534AA4D31DDC957');
        $this->addSql('DROP INDEX IDX_C534AA4D31DDC957 ON tickets_answers');
        $this->addSql('ALTER TABLE tickets_answers CHANGE answer_by_id ticket_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tickets_answers ADD CONSTRAINT FK_C534AA4D700047D2 FOREIGN KEY (ticket_id) REFERENCES `tickets` (id)');
        $this->addSql('CREATE INDEX IDX_C534AA4D700047D2 ON tickets_answers (ticket_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `tickets_answers` DROP FOREIGN KEY FK_C534AA4D700047D2');
        $this->addSql('DROP INDEX IDX_C534AA4D700047D2 ON `tickets_answers`');
        $this->addSql('ALTER TABLE `tickets_answers` CHANGE ticket_id answer_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `tickets_answers` ADD CONSTRAINT FK_C534AA4D31DDC957 FOREIGN KEY (answer_by_id) REFERENCES tickets (id)');
        $this->addSql('CREATE INDEX IDX_C534AA4D31DDC957 ON `tickets_answers` (answer_by_id)');
    }
}
