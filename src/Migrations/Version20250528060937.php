<?php

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250528060937 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add relation to integration table to resource relation mapping table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE bitbag_ergonode_resource_relation_mapping ADD integration_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE bitbag_ergonode_resource_relation_mapping ADD CONSTRAINT FK_CA88674A9E82DDEA FOREIGN KEY (integration_id) REFERENCES bitbag_ergonode_integration (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_CA88674A9E82DDEA ON bitbag_ergonode_resource_relation_mapping (integration_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE bitbag_ergonode_resource_relation_mapping DROP FOREIGN KEY FK_CA88674A9E82DDEA
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_CA88674A9E82DDEA ON bitbag_ergonode_resource_relation_mapping
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE bitbag_ergonode_resource_relation_mapping DROP integration_id
        SQL);
    }
}
