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
 * Mapping device type migration
 */
final class Version20230107201321 extends AbstractMigration {

	/**
	 * Returns a migration description
	 * @return string Migration description
	 */
	public function getDescription(): string {
		return 'Mapping device type migration';
	}

	/**
	 * Applies the migration
	 * @param Schema $schema Database schema
	 */
	public function up(Schema $schema): void {
		$this->abortIf(!$this->connection->getDatabasePlatform() instanceof SQLitePlatform, 'Migration can only be executed safely on \'sqlite\'.');

		$this->addSql('ALTER TABLE mappings ADD COLUMN device_type VARCHAR(255) NOT NULL DEFAULT board');
	}

	/**
	 * Reverts the migration
	 * @param Schema $schema Database schema
	 */
	public function down(Schema $schema): void {
		$this->abortIf(!$this->connection->getDatabasePlatform() instanceof SQLitePlatform, 'Migration can only be executed safely on \'sqlite\'.');

		$this->addSql('CREATE TEMPORARY TABLE __temp__mappings AS SELECT id, type, name, iqrf_interface, bus_enable_gpio_pin, pgm_switch_gpio_pin, power_enable_gpio_pin, baud_rate, i2c_enable_gpio_pin, spi_enable_gpio_pin, uart_enable_gpio_pin FROM mappings');
		$this->addSql('DROP TABLE mappings');
		$this->addSql('CREATE TABLE mappings (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, type VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, iqrf_interface VARCHAR(255) NOT NULL, bus_enable_gpio_pin INTEGER NOT NULL, pgm_switch_gpio_pin INTEGER NOT NULL, power_enable_gpio_pin INTEGER NOT NULL, baud_rate INTEGER DEFAULT NULL, i2c_enable_gpio_pin INTEGER DEFAULT NULL, spi_enable_gpio_pin INTEGER DEFAULT NULL, uart_enable_gpio_pin INTEGER DEFAULT NULL)');
		$this->addSql('INSERT INTO mappings (id, type, name, iqrf_interface, bus_enable_gpio_pin, pgm_switch_gpio_pin, power_enable_gpio_pin, baud_rate, i2c_enable_gpio_pin, spi_enable_gpio_pin, uart_enable_gpio_pin) SELECT id, type, name, iqrf_interface, bus_enable_gpio_pin, pgm_switch_gpio_pin, power_enable_gpio_pin, baud_rate, i2c_enable_gpio_pin, spi_enable_gpio_pin, uart_enable_gpio_pin FROM __temp__mappings');
		$this->addSql('DROP TABLE __temp__mappings');
	}

}
