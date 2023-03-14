<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230312163050 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX group_no ON groups');
        $this->addSql('CREATE UNIQUE INDEX group_no_idx ON groups (group_no)');
        $this->addSql('ALTER TABLE students ADD CONSTRAINT FK_A4698DB2FE54D947 FOREIGN KEY (group_id) REFERENCES `groups` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `students` DROP FOREIGN KEY FK_A4698DB2FE54D947');
        $this->addSql('DROP INDEX group_no_idx ON `groups`');
        $this->addSql('CREATE UNIQUE INDEX group_no ON `groups` (group_no)');
    }
}
