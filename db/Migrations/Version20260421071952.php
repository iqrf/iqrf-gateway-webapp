<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260421071952 extends AbstractMigration
{
	/**
	 * Returns the migration description
	 * @return string Migration description
	 */
    public function getDescription(): string
    {
        return 'Updates Role relations to RESTRICT - role cannot be removed when there are still some Users or API keys using it.';
    }

	/**
	 * Applies the migration
	 * @param Schema $schema Database schema
	 */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__api_keys_v2 AS SELECT expiration, hash, salt, revoked_at, description, state, id, created_at, revoked_by_id, created_by_id, role_id FROM api_keys_v2');
        $this->addSql('DROP TABLE api_keys_v2');
        $this->addSql('CREATE TABLE api_keys_v2 (expiration DATETIME NOT NULL, hash VARCHAR(64) NOT NULL, salt VARCHAR(32) NOT NULL, revoked_at DATETIME DEFAULT NULL, description VARCHAR(255) NOT NULL, state INTEGER DEFAULT 0 NOT NULL, id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created_at DATETIME NOT NULL, revoked_by_id INTEGER DEFAULT NULL, created_by_id INTEGER DEFAULT NULL, role_id INTEGER NOT NULL, CONSTRAINT FK_FAC0465EFB8FE773 FOREIGN KEY (revoked_by_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_FAC0465EB03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_FAC0465ED60322AC FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO api_keys_v2 (expiration, hash, salt, revoked_at, description, state, id, created_at, revoked_by_id, created_by_id, role_id) SELECT expiration, hash, salt, revoked_at, description, state, id, created_at, revoked_by_id, created_by_id, role_id FROM __temp__api_keys_v2');
        $this->addSql('DROP TABLE __temp__api_keys_v2');
        $this->addSql('CREATE INDEX IDX_FAC0465ED60322AC ON api_keys_v2 (role_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FAC0465ED1B862B8 ON api_keys_v2 (hash)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FAC0465E8FFBE0F7 ON api_keys_v2 (salt)');
        $this->addSql('CREATE INDEX IDX_FAC0465EFB8FE773 ON api_keys_v2 (revoked_by_id)');
        $this->addSql('CREATE INDEX IDX_FAC0465EB03A8386 ON api_keys_v2 (created_by_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__users AS SELECT id, username, password, language, email, state, role_id FROM users');
        $this->addSql('DROP TABLE users');
        $this->addSql('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(255) DEFAULT NULL, language VARCHAR(7) DEFAULT \'en\' NOT NULL, email VARCHAR(255) DEFAULT NULL, state INTEGER DEFAULT 0 NOT NULL, role_id INTEGER NOT NULL, CONSTRAINT FK_1483A5E9D60322AC FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO users (id, username, password, language, email, state, role_id) SELECT id, username, password, language, email, state, role_id FROM __temp__users');
        $this->addSql('DROP TABLE __temp__users');
        $this->addSql('CREATE INDEX IDX_1483A5E9D60322AC ON users (role_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9F85E0677 ON users (username)');
    }

	/**
	 * Reverts the migration
	 * @param Schema $schema Database schema
	 */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__api_keys_v2 AS SELECT expiration, hash, salt, revoked_at, description, state, id, created_at, revoked_by_id, created_by_id, role_id FROM api_keys_v2');
        $this->addSql('DROP TABLE api_keys_v2');
        $this->addSql('CREATE TABLE api_keys_v2 (expiration DATETIME NOT NULL, hash VARCHAR(64) NOT NULL, salt VARCHAR(32) NOT NULL, revoked_at DATETIME DEFAULT NULL, description VARCHAR(255) NOT NULL, state INTEGER DEFAULT 0 NOT NULL, id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created_at DATETIME NOT NULL, revoked_by_id INTEGER DEFAULT NULL, created_by_id INTEGER DEFAULT NULL, role_id INTEGER NOT NULL, CONSTRAINT FK_FAC0465EFB8FE773 FOREIGN KEY (revoked_by_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_FAC0465EB03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_FAC0465ED60322AC FOREIGN KEY (role_id) REFERENCES roles (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO api_keys_v2 (expiration, hash, salt, revoked_at, description, state, id, created_at, revoked_by_id, created_by_id, role_id) SELECT expiration, hash, salt, revoked_at, description, state, id, created_at, revoked_by_id, created_by_id, role_id FROM __temp__api_keys_v2');
        $this->addSql('DROP TABLE __temp__api_keys_v2');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FAC0465ED1B862B8 ON api_keys_v2 (hash)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FAC0465E8FFBE0F7 ON api_keys_v2 (salt)');
        $this->addSql('CREATE INDEX IDX_FAC0465EFB8FE773 ON api_keys_v2 (revoked_by_id)');
        $this->addSql('CREATE INDEX IDX_FAC0465EB03A8386 ON api_keys_v2 (created_by_id)');
        $this->addSql('CREATE INDEX IDX_FAC0465ED60322AC ON api_keys_v2 (role_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__users AS SELECT email, password, username, language, state, id, role_id FROM users');
        $this->addSql('DROP TABLE users');
        $this->addSql('CREATE TABLE users (email VARCHAR(255) DEFAULT NULL, password VARCHAR(255) DEFAULT NULL, username VARCHAR(255) NOT NULL, language VARCHAR(7) DEFAULT \'en\' NOT NULL, state INTEGER DEFAULT 0 NOT NULL, id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, role_id INTEGER NOT NULL, CONSTRAINT FK_1483A5E9D60322AC FOREIGN KEY (role_id) REFERENCES roles (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO users (email, password, username, language, state, id, role_id) SELECT email, password, username, language, state, id, role_id FROM __temp__users');
        $this->addSql('DROP TABLE __temp__users');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9F85E0677 ON users (username)');
        $this->addSql('CREATE INDEX IDX_1483A5E9D60322AC ON users (role_id)');
    }
}
