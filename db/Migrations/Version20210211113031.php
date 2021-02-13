<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Wireguard interface repository migration
 */
final class Version20210211113031 extends AbstractMigration
{
	public function getDescription() : string
	{
		return 'Added new wireguard interface repository';
	}

	public function up(Schema $schema) : void {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

		$this->addSql('CREATE TABLE wireguard_interfaces (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL UNIQUE, private_key VARCHAR(255) NOT NULL, port INTEGER, ipv4 BLOB, ipv4_prefix INTEGER, ipv6 BLOB, ipv6_prefix INTEGER)');
	}

	public function down(Schema $schema) : void {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

		$this->addSql('DROP TABLE wireguard_interfaces');
	}

}
