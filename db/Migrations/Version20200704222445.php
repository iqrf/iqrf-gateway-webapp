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

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * IQRF OS patch repository migration
 */
final class Version20200704222445 extends AbstractMigration {

	/**
	 * Returns the migration description
	 * @return string Migration description
	 */
	public function getDescription(): string {
		return 'Added IQRF OS patch repository';
	}

	public function up(Schema $schema): void {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

		$this->addSql('CREATE TABLE "iqrf_os_patches" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, module_type VARCHAR(15) NOT NULL, from_version INTEGER NOT NULL, from_build INTEGER NOT NULL, to_version INTEGER NOT NULL, to_build INTEGER NOT NULL, part INTEGER NOT NULL, parts INTEGER NOT NULL, file_name VARCHAR(255) NOT NULL)');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_9AF95CD0D7DF1668 ON "iqrf_os_patches" (file_name)');
	}

	public function down(Schema $schema): void {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

		$this->addSql('DROP TABLE "iqrf_os_patches"');
	}
}
