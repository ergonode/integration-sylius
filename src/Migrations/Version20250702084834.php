<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250702084834 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add product stream cursors to integration.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE bitbag_ergonode_integration ADD product_stream_cursor_end VARCHAR(255) DEFAULT NULL, ADD product_deleted_stream_cursor_end VARCHAR(255) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE bitbag_ergonode_integration DROP product_stream_cursor_end, DROP product_deleted_stream_cursor_end
        SQL);
    }
}
