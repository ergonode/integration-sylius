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

final class Version20250612101919 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add code and main language code columns.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE bitbag_ergonode_integration ADD code VARCHAR(255) NOT NULL, ADD main_language_code VARCHAR(255) NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE bitbag_ergonode_integration DROP code, DROP main_language_code
        SQL);
    }
}
