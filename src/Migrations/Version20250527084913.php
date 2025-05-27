<?php

declare(strict_types=1);

namespace BitBag\SyliusErgonodePlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250527084913 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE bitbag_ergonode_integration (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, api_key VARCHAR(255) NOT NULL, api_url VARCHAR(255) NOT NULL, category_tree_code VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE bitbag_ergonode_resource_relation_mapping (id INT AUTO_INCREMENT NOT NULL, relation_type VARCHAR(255) NOT NULL, ergonode_value VARCHAR(255) NOT NULL, sylius_value VARCHAR(255) NOT NULL, import_once TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP TABLE bitbag_ergonode_integration
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE bitbag_ergonode_resource_relation_mapping
        SQL);
    }
}
