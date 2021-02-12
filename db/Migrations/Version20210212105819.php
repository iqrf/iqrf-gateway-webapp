<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Wireguard peer address repository migration
 */
final class Version20210212105819 extends AbstractMigration
{
	public function getDescription() : string
	{
		return 'Added new wireguard peer address repository';
	}

	public function up(Schema $schema) : void {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

		$this->addSql('CREATE TABLE wireguard_peer_addresses (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, address BLOB NOT NULL, prefix INTEGER NOT NULL, peer_id INTEGER NOT NULL, FOREIGN KEY (peer_id) REFERENCES wireguard_peers(id))');
	}

	public function down(Schema $schema) : void {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

		$this->addSql('DROP TABLE wireguard_peer_addresses');
	}
}
