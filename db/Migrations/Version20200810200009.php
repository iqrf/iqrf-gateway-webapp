<?php

/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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
 * API key repository migration
 */
final class Version20200810200009 extends AbstractMigration {

	/**
	 * Returns the migration description
	 * @return string Migration description
	 */
	public function getDescription(): string {
		return 'Added API key repository';
	}

	/**
	 * Applies the migration
	 * @param Schema $schema Database schema
	 */
	public function up(Schema $schema): void {
		$this->abortIf(!$this->connection->getDatabasePlatform() instanceof SQLitePlatform, 'Migration can only be executed safely on \'sqlite\'.');

		$this->addSql('CREATE TABLE "api_keys" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, hash VARCHAR(255) NOT NULL, salt VARCHAR(22) NOT NULL, description VARCHAR(255) NOT NULL, expiration DATE DEFAULT NULL)');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_9579321FD1B862B8 ON "api_keys" (hash)');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_9579321F8FFBE0F7 ON "api_keys" (salt)');
	}

	/**
	 * Reverts the migration
	 * @param Schema $schema Database schema
	 */
	public function down(Schema $schema): void {
		$this->abortIf(!$this->connection->getDatabasePlatform() instanceof SQLitePlatform, 'Migration can only be executed safely on \'sqlite\'.');

		$this->addSql('DROP TABLE "api_keys"');
	}

}
