<?php

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250617131708 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add category stream cursors columns. Add index on integration code column.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE bitbag_ergonode_integration ADD category_stream_cursor_end VARCHAR(255) DEFAULT NULL, ADD category_deleted_stream_cursor_end VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_2BE0B94177153098 ON bitbag_ergonode_integration (code)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_2BE0B94177153098 ON bitbag_ergonode_integration
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE bitbag_ergonode_integration DROP category_stream_cursor_end, DROP category_deleted_stream_cursor_end
        SQL);
    }
}
