<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260310220905 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add API key table for new key format.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE api_keys_v2 (expiration DATETIME NOT NULL, hash VARCHAR(64) NOT NULL, salt VARCHAR(32) NOT NULL, revoked_at DATETIME DEFAULT NULL, description VARCHAR(255) NOT NULL, state INTEGER DEFAULT 0 NOT NULL, scopes CLOB NOT NULL, id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created_at DATETIME NOT NULL, revoked_by_id INTEGER DEFAULT NULL, created_by_id INTEGER DEFAULT NULL, CONSTRAINT FK_FAC0465EFB8FE773 FOREIGN KEY (revoked_by_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_FAC0465EB03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FAC0465ED1B862B8 ON api_keys_v2 (hash)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FAC0465E8FFBE0F7 ON api_keys_v2 (salt)');
        $this->addSql('CREATE INDEX IDX_FAC0465EFB8FE773 ON api_keys_v2 (revoked_by_id)');
        $this->addSql('CREATE INDEX IDX_FAC0465EB03A8386 ON api_keys_v2 (created_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE api_keys_v2');
    }
}
