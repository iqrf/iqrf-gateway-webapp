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
 * Auto-generated Migration
 */
final class Version20201030161758 extends AbstractMigration {
	/**
	 * Returns the migration description
	 * @return string Migration description
	 */
	public function getDescription() : string {
		return 'Added new mappings repository';
	}

	public function up(Schema $schema) : void {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

		$this->addSql('CREATE TABLE mappings (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, type VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, iqrf_interface VARCHAR(255) NOT NULL, bus_enable_gpio_pin INTEGER NOT NULL, pgm_switch_gpio_pin INTEGER NOT NULL, power_enable_gpio_pin INTEGER NOT NULL, baud_rate INTEGER DEFAULT NULL, i2c_enable_gpio_pin INTEGER DEFAULT NULL, spi_enable_gpio_pin INTEGER DEFAULT NULL, uart_enable_gpio_pin INTEGER DEFAULT NULL)');
		$this->addSql('INSERT INTO mappings VALUES (0, "spi", "Raspberry Pi", "/dev/spidev0.0", 7, 22, 23, null, null, null, null)');
		$this->addSql('INSERT INTO mappings VALUES (1, "spi", "UniPi Axon", "/dev/spidev0.3", 18, 19, 2, null, null, null, null)');
		$this->addSql('INSERT INTO mappings VALUES (2, "spi", "UP", "/dev/spidev2.0", 7, 22, 23, null, null, null, null)');
		$this->addSql('INSERT INTO mappings VALUES (3, "spi", "UP Squared", "/dev/spidev1.0", 7, 22, 23, null, null, null, null)');
		$this->addSql('INSERT INTO mappings VALUES (4, "spi", "IQD-GW-01A", "/dev/spidev1.0", 10, 3, 19, null, null, null, null)');
		$this->addSql('INSERT INTO mappings VALUES (5, "spi", "IQD-GW-02A", "/dev/spidev1.0", -1, 3, 19, null, 7, 10, 6)');
		$this->addSql('INSERT INTO mappings VALUES (7, "uart", "Raspberry Pi", "/dev/ttyS0", 7, 22, 23, 57600, null, null, null)');
		$this->addSql('INSERT INTO mappings VALUES (8, "uart", "UniPi", "/dev/ttyS0", -1, -1, 18, 57600, null, null, null)');
		$this->addSql('INSERT INTO mappings VALUES (9, "uart", "UP", "/dev/ttyS1", 7, 22, 23, 57600, null, null, null)');
		$this->addSql('INSERT INTO mappings VALUES (10, "uart", "UP Squared", "/dev/ttyS1", 7, 22, 23, 57600, null, null, null)');
		$this->addSql('INSERT INTO mappings VALUES (11, "uart", "IQD-GW-01A", "/dev/ttyS1", 10, -1, 19, 57600, null, null, null)');
		$this->addSql('INSERT INTO mappings VALUES (12, "uart", "IQD-GW-02A", "/dev/ttyS1", -1, 3, 19, 57600, 7, 10, 6)');
	}

	public function down(Schema $schema) : void {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

		$this->addSql('DROP TABLE mappings');
	}

}
