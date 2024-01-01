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
declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Controller pin configuration device type migration
 */
final class Version20230112101613 extends AbstractMigration {
	/**
	 * Returns a migration description
	 * @return string Migration description
	 */
	public function getDescription(): string {
		return 'Controller pin configuration device type migration';
	}

	public function up(Schema $schema): void {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

		$this->addSql('ALTER TABLE controller_pin_configs ADD COLUMN device_type VARCHAR(255) NOT NULL DEFAULT board');
	}

	public function down(Schema $schema): void {
		$this->addSql('CREATE TEMPORARY TABLE __temp__controller_pin_configs AS SELECT id, name, green_led, red_led, button, sck, sda FROM "controller_pin_configs"');

		$this->addSql('DROP TABLE "controller_pin_configs"');
		$this->addSql('CREATE TABLE "controller_pin_configs" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, green_led INTEGER NOT NULL, red_led INTEGER NOT NULL, button INTEGER NOT NULL, sck INTEGER DEFAULT NULL, sda INTEGER DEFAULT NULL)');
		$this->addSql('INSERT INTO "controller_pin_configs" (id, name, green_led, red_led, button, sck, sda) SELECT id, name, green_led, red_led, button, sck, sda FROM __temp__controller_pin_configs');
		$this->addSql('DROP TABLE __temp__controller_pin_configs');
	}
}
