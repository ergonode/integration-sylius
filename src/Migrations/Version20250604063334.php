<?php

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250604063334 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add channel code to resource relation mapping';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE bitbag_ergonode_resource_relation_mapping ADD channel_code VARCHAR(255) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE bitbag_ergonode_resource_relation_mapping DROP channel_code
        SQL);
    }
}
