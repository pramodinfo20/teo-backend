<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190424063441 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Support value ranges for Global Parameters';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE global_parameters ADD COLUMN min_value VARCHAR');
        $this->addSql('ALTER TABLE global_parameters ADD COLUMN max_value VARCHAR');

    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE global_parameters DROP COLUMN min_value');
        $this->addSql('ALTER TABLE global_parameters DROP COLUMN max_value');
    }
}
