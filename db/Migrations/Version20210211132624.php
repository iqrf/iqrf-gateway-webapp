<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Wireguard peer repository migration
 */
final class Version20210211132624 extends AbstractMigration
{
	public function getDescription() : string
	{
		return 'Added new wireguard peer repository';
	}

	public function up(Schema $schema) : void {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

		$this->addSql('CREATE TABLE wireguard_peers (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, public_key VARCHAR(255) NOT NULL, psk VARCHAR(255) DEFAULT NULL, keepalive INTEGER NOT NULL, endpoint VARCHAR(255) NOT NULL, port INTEGER NOT NULL, interface_id INTEGER NOT NULL, FOREIGN KEY (interface_id) REFERENCES wireguard_interfaces(id))');
	}

	public function down(Schema $schema) : void {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

		$this->addSql('DROP TABLE wireguard_peers');
	}
}
