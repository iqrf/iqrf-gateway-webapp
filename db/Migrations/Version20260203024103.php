<?php

/**
 * Copyright 2017-2026 IQRF Tech s.r.o.
 * Copyright 2019-2026 MICRORISC s.r.o.
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

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add unique constraint to WireGuard interface private key
 */
final class Version20260203024103 extends AbstractMigration {

	/**
	 * Returns the migration description
	 * @return string Migration description
	 */
	public function getDescription(): string {
		return 'Add unique constraint to WireGuard interface private key';
	}

	/**
	 * Applies the migration
	 * @param Schema $schema Database schema
	 */
	public function up(Schema $schema): void {
		$this->addSql('CREATE TEMPORARY TABLE __temp__wireguard_interfaces AS SELECT id, name, private_key, port FROM wireguard_interfaces');
		$this->addSql('DROP TABLE wireguard_interfaces');
		$this->addSql('CREATE TABLE wireguard_interfaces (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, private_key VARCHAR(255) NOT NULL, port INTEGER DEFAULT NULL)');
		$this->addSql('INSERT INTO wireguard_interfaces (id, name, private_key, port) SELECT id, name, private_key, port FROM __temp__wireguard_interfaces');
		$this->addSql('DROP TABLE __temp__wireguard_interfaces');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_25B210A65E237E06 ON wireguard_interfaces (name)');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_25B210A6F7F984A6 ON wireguard_interfaces (private_key)');
	}

	/**
	 * Reverts the migration
	 * @param Schema $schema Database schema
	 */
	public function down(Schema $schema): void {
		$this->addSql('CREATE TEMPORARY TABLE __temp__wireguard_interfaces AS SELECT name, private_key, port, id FROM wireguard_interfaces');
		$this->addSql('DROP TABLE wireguard_interfaces');
		$this->addSql('CREATE TABLE wireguard_interfaces (name VARCHAR(255) NOT NULL, private_key VARCHAR(255) NOT NULL, port INTEGER DEFAULT NULL, id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL)');
		$this->addSql('INSERT INTO wireguard_interfaces (name, private_key, port, id) SELECT name, private_key, port, id FROM __temp__wireguard_interfaces');
		$this->addSql('DROP TABLE __temp__wireguard_interfaces');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_25B210A65E237E06 ON wireguard_interfaces (name)');
	}

}
