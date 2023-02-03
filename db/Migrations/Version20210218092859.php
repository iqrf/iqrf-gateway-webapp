<?php

/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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
 * Wireguard tunnel repositories migration
 */
final class Version20210218092859 extends AbstractMigration {
	/**
	 * Returns the migration description
	 * @return string Migration description
	 */
	public function getDescription(): string {
		return 'Added Wireguard tunnel repositories';
	}

	public function up(Schema $schema): void {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

		$this->addSql('CREATE TABLE "wireguard_interface_ipv4s" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, interface_id INTEGER DEFAULT NULL, address BLOB NOT NULL --(DC2Type:ip)
		, prefix INTEGER NOT NULL)');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_EA5C8753AB0BE982 ON "wireguard_interface_ipv4s" (interface_id)');
		$this->addSql('CREATE TABLE "wireguard_interface_ipv6s" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, interface_id INTEGER DEFAULT NULL, address BLOB NOT NULL --(DC2Type:ip)
		, prefix INTEGER NOT NULL)');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_D86AE5D1AB0BE982 ON "wireguard_interface_ipv6s" (interface_id)');
		$this->addSql('CREATE TABLE "wireguard_interfaces" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, private_key VARCHAR(255) NOT NULL, port INTEGER DEFAULT NULL)');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_25B210A65E237E06 ON "wireguard_interfaces" (name)');
		$this->addSql('CREATE TABLE "wireguard_peer_addresses" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, peer_id INTEGER DEFAULT NULL, address BLOB NOT NULL --(DC2Type:ip)
		, prefix INTEGER NOT NULL)');
		$this->addSql('CREATE INDEX IDX_AB85CDC120D91DB4 ON "wireguard_peer_addresses" (peer_id)');
		$this->addSql('CREATE TABLE "wireguard_peers" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, interface_id INTEGER DEFAULT NULL, public_key VARCHAR(255) NOT NULL, psk VARCHAR(255) DEFAULT NULL, keepalive INTEGER NOT NULL, endpoint VARCHAR(255) NOT NULL, port INTEGER NOT NULL)');
		$this->addSql('CREATE INDEX IDX_23ACBD91AB0BE982 ON "wireguard_peers" (interface_id)');
	}

	public function down(Schema $schema): void {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

		$this->addSql('DROP TABLE "wireguard_interface_ipv4s"');
		$this->addSql('DROP TABLE "wireguard_interface_ipv6s"');
		$this->addSql('DROP TABLE "wireguard_interfaces"');
		$this->addSql('DROP TABLE "wireguard_peer_addresses"');
		$this->addSql('DROP TABLE "wireguard_peers"');
	}
}
