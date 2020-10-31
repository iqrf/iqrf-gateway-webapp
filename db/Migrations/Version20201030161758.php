<?php

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

		$this->addSql('CREATE TABLE mappings (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, type VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, iqrf_interface VARCHAR(255) NOT NULL, bus_enable_gpio_pin INTEGER NOT NULL, pgm_switch_gpio_pin INTEGER NOT NULL, power_enable_gpio_pin INTEGER NOT NULL, baud_rate INTEGER DEFAULT NULL)');
		$this->addSql('INSERT INTO mappings VALUES (0, "spi", "Raspberry Pi", "/dev/spidev0.0", 7, 22, 23, null)');
		$this->addSql('INSERT INTO mappings VALUES (1, "spi", "Orange Pi", "/dev/spidev1.0", 10, 3, 19, null)');
		$this->addSql('INSERT INTO mappings VALUES (2, "spi", "UniPi Axon", "/dev/spidev0.3", 18, 19, 2, null)');
		$this->addSql('INSERT INTO mappings VALUES (3, "spi", "UP", "/dev/spidev2.0", 7, 22, 23, null)');
		$this->addSql('INSERT INTO mappings VALUES (4, "spi", "UP Squared", "/dev/spidev1.0", 7, 22, 23, null)');
		$this->addSql('INSERT INTO mappings VALUES (5, "uart", "Raspberry Pi 1", "/dev/ttyAMA0", 7, 22, 23, 57600)');
		$this->addSql('INSERT INTO mappings VALUES (6, "uart", "Raspberry Pi", "/dev/ttyS0", 7, 22, 23, 57600)');
		$this->addSql('INSERT INTO mappings VALUES (7, "uart", "Orange Pi", "/dev/ttyS0", 10, 3, 19, 57600)');
		$this->addSql('INSERT INTO mappings VALUES (8, "uart", "UniPi", "/dev/ttyS0", -1, -1, 18, 57600)');
		$this->addSql('INSERT INTO mappings VALUES (9, "uart", "UP", "/dev/ttyS1", 7, 22, 23, 57600)');
		$this->addSql('INSERT INTO mappings VALUES (10, "uart", "UP Squared", "/dev/ttyS1", 7, 22, 23, 57600)');
	}

	public function down(Schema $schema) : void {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

		$this->addSql('DROP TABLE mappings');
	}

}
