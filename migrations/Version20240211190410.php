<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240211190410 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D4E6F81D7D3AC2A ON address (line1)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D4E6F814EDAFD90 ON address (line2)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_D4E6F81D7D3AC2A ON address');
        $this->addSql('DROP INDEX UNIQ_D4E6F814EDAFD90 ON address');
    }
}
