<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230314195514 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subjects ADD issued_by_id INT NOT NULL');
        $this->addSql('ALTER TABLE subjects ADD CONSTRAINT FK_AB259917784BB717 FOREIGN KEY (issued_by_id) REFERENCES `teachers` (id)');
        $this->addSql('CREATE INDEX IDX_AB259917784BB717 ON subjects (issued_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `subjects` DROP FOREIGN KEY FK_AB259917784BB717');
        $this->addSql('DROP INDEX IDX_AB259917784BB717 ON `subjects`');
        $this->addSql('ALTER TABLE `subjects` DROP issued_by_id');
    }
}
