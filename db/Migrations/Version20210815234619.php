<?php

/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
declare(strict_types = 1);

namespace Database\Migrations;

use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * E-mail verification database migration
 */
final class Version20210815234619 extends AbstractMigration {

	/**
	 * Returns the migration description
	 * @return string Migration description
	 */
	public function getDescription(): string {
		return 'E-mail verification database migration';
	}

	/**
	 * Applies the migration
	 * @param Schema $schema Database schema
	 */
	public function up(Schema $schema): void {
		$this->abortIf(!$this->connection->getDatabasePlatform() instanceof SQLitePlatform, 'Migration can only be executed safely on \'sqlite\'.');

		$this->addSql('CREATE TABLE "email_verification" (uuid CHAR(36) NOT NULL --(DC2Type:uuid)
        , user INTEGER DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(uuid))');
		$this->addSql('CREATE INDEX IDX_FE223588D93D649 ON "email_verification" (user)');
		$this->addSql('DROP INDEX UNIQ_1483A5E9F85E0677');
		$this->addSql('CREATE TEMPORARY TABLE __temp__users AS SELECT id, username, password, role, language FROM users');
		$this->addSql('DROP TABLE users');
		$this->addSql('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(255) NOT NULL COLLATE BINARY, password VARCHAR(255) NOT NULL COLLATE BINARY, role VARCHAR(15) NOT NULL COLLATE BINARY, language VARCHAR(7) NOT NULL COLLATE BINARY, email VARCHAR(255) DEFAULT NULL, state INTEGER DEFAULT 0 NOT NULL)');
		$this->addSql('INSERT INTO users (id, username, password, role, language) SELECT id, username, password, role, language FROM __temp__users');
		$this->addSql('DROP TABLE __temp__users');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9F85E0677 ON users (username)');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
	}

	/**
	 * Reverts the migration
	 * @param Schema $schema Database schema
	 */
	public function down(Schema $schema): void {
		$this->abortIf(!$this->connection->getDatabasePlatform() instanceof SQLitePlatform, 'Migration can only be executed safely on \'sqlite\'.');

		$this->addSql('DROP TABLE "email_verification"');
		$this->addSql('DROP INDEX UNIQ_1483A5E9F85E0677');
		$this->addSql('DROP INDEX UNIQ_1483A5E9E7927C74');
		$this->addSql('CREATE TEMPORARY TABLE __temp__users AS SELECT id, username, password, role, language FROM "users"');
		$this->addSql('DROP TABLE "users"');
		$this->addSql('CREATE TABLE "users" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, role VARCHAR(15) NOT NULL, language VARCHAR(7) NOT NULL)');
		$this->addSql('INSERT INTO "users" (id, username, password, role, language) SELECT id, username, password, role, language FROM __temp__users');
		$this->addSql('DROP TABLE __temp__users');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9F85E0677 ON "users" (username)');
	}

}
