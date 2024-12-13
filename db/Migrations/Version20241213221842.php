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
 * Update to Doctrine DBAL 4 and ORM 3
 */
final class Version20241213221842 extends AbstractMigration {

	/**
	 * Returns a migration description
	 * @return string Migration description
	 */
	public function getDescription(): string {
		return 'Update to Doctrine DBAL 4 and ORM 3';
	}

	/**
	 * Applies the migration
	 * @param Schema $schema Database schema
	 */
	public function up(Schema $schema): void {
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('CREATE TEMPORARY TABLE __temp__email_verification AS SELECT uuid, user, created_at FROM email_verification');
		$this->addSql('DROP TABLE email_verification');
		$this->addSql('CREATE TABLE email_verification (uuid CHAR(36) NOT NULL, user INTEGER NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(uuid), CONSTRAINT FK_FE223588D93D649 FOREIGN KEY (user) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
		$this->addSql('INSERT INTO email_verification (uuid, user, created_at) SELECT uuid, user, created_at FROM __temp__email_verification');
		$this->addSql('DROP TABLE __temp__email_verification');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_FE223588D93D649 ON email_verification (user)');
		$this->addSql('CREATE TEMPORARY TABLE __temp__password_recovery AS SELECT uuid, user, created_at FROM password_recovery');
		$this->addSql('DROP TABLE password_recovery');
		$this->addSql('CREATE TABLE password_recovery (uuid CHAR(36) NOT NULL, user INTEGER NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(uuid), CONSTRAINT FK_63D401098D93D649 FOREIGN KEY (user) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
		$this->addSql('INSERT INTO password_recovery (uuid, user, created_at) SELECT uuid, user, created_at FROM __temp__password_recovery');
		$this->addSql('DROP TABLE __temp__password_recovery');
		$this->addSql('CREATE INDEX IDX_63D401098D93D649 ON password_recovery (user)');
		$this->addSql('CREATE TEMPORARY TABLE __temp__user_preferences AS SELECT id, user, time_format, theme_preference FROM user_preferences');
		$this->addSql('DROP TABLE user_preferences');
		$this->addSql('CREATE TABLE user_preferences (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user INTEGER NOT NULL, time_format INTEGER DEFAULT 0 NOT NULL, theme_preference INTEGER DEFAULT 0 NOT NULL, CONSTRAINT FK_402A6F608D93D649 FOREIGN KEY (user) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
		$this->addSql('INSERT INTO user_preferences (id, user, time_format, theme_preference) SELECT id, user, time_format, theme_preference FROM __temp__user_preferences');
		$this->addSql('DROP TABLE __temp__user_preferences');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_402A6F608D93D649 ON user_preferences (user)');
		$this->addSql('CREATE TEMPORARY TABLE __temp__wireguard_interface_ipv4s AS SELECT id, interface_id, address, prefix FROM wireguard_interface_ipv4s');
		$this->addSql('DROP TABLE wireguard_interface_ipv4s');
		$this->addSql('CREATE TABLE wireguard_interface_ipv4s (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, interface_id INTEGER NOT NULL, address BLOB NOT NULL, prefix INTEGER NOT NULL, CONSTRAINT FK_EA5C8753AB0BE982 FOREIGN KEY (interface_id) REFERENCES wireguard_interfaces (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
		$this->addSql('INSERT INTO wireguard_interface_ipv4s (id, interface_id, address, prefix) SELECT id, interface_id, address, prefix FROM __temp__wireguard_interface_ipv4s');
		$this->addSql('DROP TABLE __temp__wireguard_interface_ipv4s');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_EA5C8753AB0BE982 ON wireguard_interface_ipv4s (interface_id)');
		$this->addSql('CREATE TEMPORARY TABLE __temp__wireguard_interface_ipv6s AS SELECT id, interface_id, address, prefix FROM wireguard_interface_ipv6s');
		$this->addSql('DROP TABLE wireguard_interface_ipv6s');
		$this->addSql('CREATE TABLE wireguard_interface_ipv6s (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, interface_id INTEGER NOT NULL, address BLOB NOT NULL, prefix INTEGER NOT NULL, CONSTRAINT FK_D86AE5D1AB0BE982 FOREIGN KEY (interface_id) REFERENCES wireguard_interfaces (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
		$this->addSql('INSERT INTO wireguard_interface_ipv6s (id, interface_id, address, prefix) SELECT id, interface_id, address, prefix FROM __temp__wireguard_interface_ipv6s');
		$this->addSql('DROP TABLE __temp__wireguard_interface_ipv6s');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_D86AE5D1AB0BE982 ON wireguard_interface_ipv6s (interface_id)');
		$this->addSql('CREATE TEMPORARY TABLE __temp__wireguard_peer_addresses AS SELECT id, peer_id, address, prefix FROM wireguard_peer_addresses');
		$this->addSql('DROP TABLE wireguard_peer_addresses');
		$this->addSql('CREATE TABLE wireguard_peer_addresses (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, peer_id INTEGER NOT NULL, address BLOB NOT NULL, prefix INTEGER NOT NULL, CONSTRAINT FK_AB85CDC120D91DB4 FOREIGN KEY (peer_id) REFERENCES wireguard_peers (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
		$this->addSql('INSERT INTO wireguard_peer_addresses (id, peer_id, address, prefix) SELECT id, peer_id, address, prefix FROM __temp__wireguard_peer_addresses');
		$this->addSql('DROP TABLE __temp__wireguard_peer_addresses');
		$this->addSql('CREATE INDEX IDX_AB85CDC120D91DB4 ON wireguard_peer_addresses (peer_id)');
		$this->addSql('CREATE TEMPORARY TABLE __temp__wireguard_peers AS SELECT id, interface_id, public_key, psk, keepalive, endpoint, port FROM wireguard_peers');
		$this->addSql('DROP TABLE wireguard_peers');
		$this->addSql('CREATE TABLE wireguard_peers (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, interface_id INTEGER NOT NULL, public_key VARCHAR(255) NOT NULL, psk VARCHAR(255) DEFAULT NULL, keepalive INTEGER NOT NULL, endpoint VARCHAR(255) NOT NULL, port INTEGER NOT NULL, CONSTRAINT FK_23ACBD91AB0BE982 FOREIGN KEY (interface_id) REFERENCES wireguard_interfaces (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
		$this->addSql('INSERT INTO wireguard_peers (id, interface_id, public_key, psk, keepalive, endpoint, port) SELECT id, interface_id, public_key, psk, keepalive, endpoint, port FROM __temp__wireguard_peers');
		$this->addSql('DROP TABLE __temp__wireguard_peers');
		$this->addSql('CREATE INDEX IDX_23ACBD91AB0BE982 ON wireguard_peers (interface_id)');
	}

	/**
	 * Reverts the migration
	 * @param Schema $schema Database schema
	 */

	public function down(Schema $schema): void {
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('CREATE TEMPORARY TABLE __temp__email_verification AS SELECT uuid, created_at, user FROM email_verification');
		$this->addSql('DROP TABLE email_verification');
		$this->addSql('CREATE TABLE email_verification (uuid CHAR(36) NOT NULL --(DC2Type:uuid)
		, created_at DATETIME NOT NULL, user INTEGER DEFAULT NULL, PRIMARY KEY(uuid), CONSTRAINT FK_FE223588D93D649 FOREIGN KEY (user) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
		$this->addSql('INSERT INTO email_verification (uuid, created_at, user) SELECT uuid, created_at, user FROM __temp__email_verification');
		$this->addSql('DROP TABLE __temp__email_verification');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_FE223588D93D649 ON email_verification (user)');
		$this->addSql('CREATE TEMPORARY TABLE __temp__password_recovery AS SELECT uuid, created_at, user FROM password_recovery');
		$this->addSql('DROP TABLE password_recovery');
		$this->addSql('CREATE TABLE password_recovery (uuid CHAR(36) NOT NULL --(DC2Type:uuid)
		, created_at DATETIME NOT NULL, user INTEGER DEFAULT NULL, PRIMARY KEY(uuid), CONSTRAINT FK_63D401098D93D649 FOREIGN KEY (user) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
		$this->addSql('INSERT INTO password_recovery (uuid, created_at, user) SELECT uuid, created_at, user FROM __temp__password_recovery');
		$this->addSql('DROP TABLE __temp__password_recovery');
		$this->addSql('CREATE INDEX IDX_63D401098D93D649 ON password_recovery (user)');
		$this->addSql('CREATE TEMPORARY TABLE __temp__user_preferences AS SELECT time_format, theme_preference, id, user FROM user_preferences');
		$this->addSql('DROP TABLE user_preferences');
		$this->addSql('CREATE TABLE user_preferences (time_format INTEGER NOT NULL, theme_preference INTEGER DEFAULT 0 NOT NULL, id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user INTEGER DEFAULT NULL, CONSTRAINT FK_402A6F608D93D649 FOREIGN KEY (user) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
		$this->addSql('INSERT INTO user_preferences (time_format, theme_preference, id, user) SELECT time_format, theme_preference, id, user FROM __temp__user_preferences');
		$this->addSql('DROP TABLE __temp__user_preferences');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_402A6F608D93D649 ON user_preferences (user)');
		$this->addSql('CREATE TEMPORARY TABLE __temp__wireguard_interface_ipv4s AS SELECT address, prefix, id, interface_id FROM wireguard_interface_ipv4s');
		$this->addSql('DROP TABLE wireguard_interface_ipv4s');
		$this->addSql('CREATE TABLE wireguard_interface_ipv4s (address BLOB NOT NULL --(DC2Type:ip)
		, prefix INTEGER NOT NULL, id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, interface_id INTEGER DEFAULT NULL, CONSTRAINT FK_EA5C8753AB0BE982 FOREIGN KEY (interface_id) REFERENCES wireguard_interfaces (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
		$this->addSql('INSERT INTO wireguard_interface_ipv4s (address, prefix, id, interface_id) SELECT address, prefix, id, interface_id FROM __temp__wireguard_interface_ipv4s');
		$this->addSql('DROP TABLE __temp__wireguard_interface_ipv4s');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_EA5C8753AB0BE982 ON wireguard_interface_ipv4s (interface_id)');
		$this->addSql('CREATE TEMPORARY TABLE __temp__wireguard_interface_ipv6s AS SELECT address, prefix, id, interface_id FROM wireguard_interface_ipv6s');
		$this->addSql('DROP TABLE wireguard_interface_ipv6s');
		$this->addSql('CREATE TABLE wireguard_interface_ipv6s (address BLOB NOT NULL --(DC2Type:ip)
		, prefix INTEGER NOT NULL, id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, interface_id INTEGER DEFAULT NULL, CONSTRAINT FK_D86AE5D1AB0BE982 FOREIGN KEY (interface_id) REFERENCES wireguard_interfaces (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
		$this->addSql('INSERT INTO wireguard_interface_ipv6s (address, prefix, id, interface_id) SELECT address, prefix, id, interface_id FROM __temp__wireguard_interface_ipv6s');
		$this->addSql('DROP TABLE __temp__wireguard_interface_ipv6s');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_D86AE5D1AB0BE982 ON wireguard_interface_ipv6s (interface_id)');
		$this->addSql('CREATE TEMPORARY TABLE __temp__wireguard_peer_addresses AS SELECT address, prefix, id, peer_id FROM wireguard_peer_addresses');
		$this->addSql('DROP TABLE wireguard_peer_addresses');
		$this->addSql('CREATE TABLE wireguard_peer_addresses (address BLOB NOT NULL --(DC2Type:ip)
		, prefix INTEGER NOT NULL, id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, peer_id INTEGER DEFAULT NULL, CONSTRAINT FK_AB85CDC120D91DB4 FOREIGN KEY (peer_id) REFERENCES wireguard_peers (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
		$this->addSql('INSERT INTO wireguard_peer_addresses (address, prefix, id, peer_id) SELECT address, prefix, id, peer_id FROM __temp__wireguard_peer_addresses');
		$this->addSql('DROP TABLE __temp__wireguard_peer_addresses');
		$this->addSql('CREATE INDEX IDX_AB85CDC120D91DB4 ON wireguard_peer_addresses (peer_id)');
		$this->addSql('CREATE TEMPORARY TABLE __temp__wireguard_peers AS SELECT public_key, psk, keepalive, endpoint, port, id, interface_id FROM wireguard_peers');
		$this->addSql('DROP TABLE wireguard_peers');
		$this->addSql('CREATE TABLE wireguard_peers (public_key VARCHAR(255) NOT NULL, psk VARCHAR(255) DEFAULT NULL, keepalive INTEGER NOT NULL, endpoint VARCHAR(255) NOT NULL, port INTEGER NOT NULL, id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, interface_id INTEGER DEFAULT NULL, CONSTRAINT FK_23ACBD91AB0BE982 FOREIGN KEY (interface_id) REFERENCES wireguard_interfaces (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
		$this->addSql('INSERT INTO wireguard_peers (public_key, psk, keepalive, endpoint, port, id, interface_id) SELECT public_key, psk, keepalive, endpoint, port, id, interface_id FROM __temp__wireguard_peers');
		$this->addSql('DROP TABLE __temp__wireguard_peers');
		$this->addSql('CREATE INDEX IDX_23ACBD91AB0BE982 ON wireguard_peers (interface_id)');
	}

}
