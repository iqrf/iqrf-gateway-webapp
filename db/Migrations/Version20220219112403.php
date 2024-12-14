<?php

declare(strict_types = 1);

namespace Database\Migrations;

use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Network operators database migration
 */
final class Version20220219112403 extends AbstractMigration {

	/**
	 * Returns the migration description
	 * @return string Migration description
	 */
	public function getDescription(): string {
		return 'Network operators database migration';
	}

	/**
	 * Applies the migration
	 * @param Schema $schema Database schema
	 */
	public function up(Schema $schema): void {
		$this->abortIf(!$this->connection->getDatabasePlatform() instanceof SQLitePlatform, 'Migration can only be executed safely on \'sqlite\'.');

		$this->addSql('CREATE TABLE network_operators (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, apn VARCHAR(255) NOT NULL, username VARCHAR(255) DEFAULT NULL, password VARCHAR(255) DEFAULT NULL)');
		$this->addSql('INSERT INTO network_operators VALUES (1, "T-Mobile CZ", "internet.t-mobile.cz", "gprs", "gprs")');
		$this->addSql('INSERT INTO network_operators VALUES (2, "O2 CZ", "internet", null, null)');
		$this->addSql('INSERT INTO network_operators VALUES (3, "Vodafone CZ", "internet", null, null)');
		$this->addSql('INSERT INTO network_operators VALUES (4, "T-Mobile SK", "internet", null, null)');
	}

	/**
	 * Reverts the migration
	 * @param Schema $schema Database schema
	 */
	public function down(Schema $schema): void {
		$this->abortIf(!$this->connection->getDatabasePlatform() instanceof SQLitePlatform, 'Migration can only be executed safely on \'sqlite\'.');

		$this->addSql('DROP TABLE network_operators');
	}

}
