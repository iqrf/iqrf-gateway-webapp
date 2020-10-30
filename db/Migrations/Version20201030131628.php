<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration
 */
final class Version20201030131628 extends AbstractMigration {
	/**
	 * Returns the migration description
	 * @return string Migration description
	 */
	public function getDescription() : string {
		return '';
	}

	public function up(Schema $schema) : void {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

		$this->addSql('CREATE TABLE mappings (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, iqrf_interface VARCHAR(255) NOT NULL, bus_enable_gpio_pin INTEGER NOT NULL, pgm_switch_gpio_pin INTEGER NOT NULL, power_enable_gpio_pin INTEGER NOT NULL)');
	}

	public function down(Schema $schema) : void {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

		$this->addSql('DROP TABLE mappings');
	}

}
