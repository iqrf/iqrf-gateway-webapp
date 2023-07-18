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
 * Index fix migration
 */
final class Version20230718153247 extends AbstractMigration {

	/**
	 * Returns a migration description
	 * @return string Migration description
	 */
	public function getDescription(): string {
		return 'Index fix migration';
	}

	/**
	 * Applies the migration
	 * @param Schema $schema Database schema
	 */
	public function up(Schema $schema): void {
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('CREATE TEMPORARY TABLE __temp__api_keys AS SELECT id, hash, salt, description, expiration FROM api_keys');
		$this->addSql('DROP TABLE api_keys');
		$this->addSql('CREATE TABLE api_keys (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, hash VARCHAR(255) NOT NULL, salt VARCHAR(22) NOT NULL, description VARCHAR(255) NOT NULL, expiration DATETIME DEFAULT NULL)');
		$this->addSql('INSERT INTO api_keys (id, hash, salt, description, expiration) SELECT id, hash, salt, description, expiration FROM __temp__api_keys');
		$this->addSql('DROP TABLE __temp__api_keys');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_9579321F8FFBE0F7 ON api_keys (salt)');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_9579321FD1B862B8 ON api_keys (hash)');
		$this->addSql('CREATE TEMPORARY TABLE __temp__controller_pin_configs AS SELECT id, name, green_led, red_led, button, sck, sda, device_type FROM controller_pin_configs');
		$this->addSql('DROP TABLE controller_pin_configs');
		$this->addSql('CREATE TABLE controller_pin_configs (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, green_led INTEGER NOT NULL, red_led INTEGER NOT NULL, button INTEGER NOT NULL, sck INTEGER DEFAULT NULL, sda INTEGER DEFAULT NULL, device_type VARCHAR(255) NOT NULL)');
		$this->addSql('INSERT INTO controller_pin_configs (id, name, green_led, red_led, button, sck, sda, device_type) SELECT id, name, green_led, red_led, button, sck, sda, device_type FROM __temp__controller_pin_configs');
		$this->addSql('DROP TABLE __temp__controller_pin_configs');
		$this->addSql('CREATE TEMPORARY TABLE __temp__mappings AS SELECT id, type, name, iqrf_interface, bus_enable_gpio_pin, pgm_switch_gpio_pin, power_enable_gpio_pin, baud_rate, i2c_enable_gpio_pin, spi_enable_gpio_pin, uart_enable_gpio_pin, device_type FROM mappings');
		$this->addSql('DROP TABLE mappings');
		$this->addSql('CREATE TABLE mappings (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, type VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, iqrf_interface VARCHAR(255) NOT NULL, bus_enable_gpio_pin INTEGER NOT NULL, pgm_switch_gpio_pin INTEGER NOT NULL, power_enable_gpio_pin INTEGER NOT NULL, baud_rate INTEGER DEFAULT NULL, i2c_enable_gpio_pin INTEGER DEFAULT NULL, spi_enable_gpio_pin INTEGER DEFAULT NULL, uart_enable_gpio_pin INTEGER DEFAULT NULL, device_type VARCHAR(255) NOT NULL)');
		$this->addSql('INSERT INTO mappings (id, type, name, iqrf_interface, bus_enable_gpio_pin, pgm_switch_gpio_pin, power_enable_gpio_pin, baud_rate, i2c_enable_gpio_pin, spi_enable_gpio_pin, uart_enable_gpio_pin, device_type) SELECT id, type, name, iqrf_interface, bus_enable_gpio_pin, pgm_switch_gpio_pin, power_enable_gpio_pin, baud_rate, i2c_enable_gpio_pin, spi_enable_gpio_pin, uart_enable_gpio_pin, device_type FROM __temp__mappings');
		$this->addSql('DROP TABLE __temp__mappings');
		$this->addSql('CREATE TEMPORARY TABLE __temp__password_recovery AS SELECT uuid, user, created_at FROM password_recovery');
		$this->addSql('DROP TABLE password_recovery');
		$this->addSql('CREATE TABLE password_recovery (uuid CHAR(36) NOT NULL --(DC2Type:uuid)
		, user INTEGER DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(uuid), CONSTRAINT FK_63D401098D93D649 FOREIGN KEY (user) REFERENCES "users" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
		$this->addSql('INSERT INTO password_recovery (uuid, user, created_at) SELECT uuid, user, created_at FROM __temp__password_recovery');
		$this->addSql('DROP TABLE __temp__password_recovery');
		$this->addSql('CREATE INDEX IDX_63D401098D93D649 ON password_recovery (user)');
		$this->addSql('CREATE TEMPORARY TABLE __temp__wireguard_interface_ipv4s AS SELECT id, interface_id, address, prefix FROM wireguard_interface_ipv4s');
		$this->addSql('DROP TABLE wireguard_interface_ipv4s');
		$this->addSql('CREATE TABLE wireguard_interface_ipv4s (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, interface_id INTEGER DEFAULT NULL, address BLOB NOT NULL --(DC2Type:ip)
		, prefix INTEGER NOT NULL, CONSTRAINT FK_EA5C8753AB0BE982 FOREIGN KEY (interface_id) REFERENCES "wireguard_interfaces" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
		$this->addSql('INSERT INTO wireguard_interface_ipv4s (id, interface_id, address, prefix) SELECT id, interface_id, address, prefix FROM __temp__wireguard_interface_ipv4s');
		$this->addSql('DROP TABLE __temp__wireguard_interface_ipv4s');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_EA5C8753AB0BE982 ON wireguard_interface_ipv4s (interface_id)');
		$this->addSql('CREATE TEMPORARY TABLE __temp__wireguard_interface_ipv6s AS SELECT id, interface_id, address, prefix FROM wireguard_interface_ipv6s');
		$this->addSql('DROP TABLE wireguard_interface_ipv6s');
		$this->addSql('CREATE TABLE wireguard_interface_ipv6s (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, interface_id INTEGER DEFAULT NULL, address BLOB NOT NULL --(DC2Type:ip)
		, prefix INTEGER NOT NULL, CONSTRAINT FK_D86AE5D1AB0BE982 FOREIGN KEY (interface_id) REFERENCES "wireguard_interfaces" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
		$this->addSql('INSERT INTO wireguard_interface_ipv6s (id, interface_id, address, prefix) SELECT id, interface_id, address, prefix FROM __temp__wireguard_interface_ipv6s');
		$this->addSql('DROP TABLE __temp__wireguard_interface_ipv6s');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_D86AE5D1AB0BE982 ON wireguard_interface_ipv6s (interface_id)');
		$this->addSql('CREATE TEMPORARY TABLE __temp__wireguard_peer_addresses AS SELECT id, peer_id, address, prefix FROM wireguard_peer_addresses');
		$this->addSql('DROP TABLE wireguard_peer_addresses');
		$this->addSql('CREATE TABLE wireguard_peer_addresses (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, peer_id INTEGER DEFAULT NULL, address BLOB NOT NULL --(DC2Type:ip)
		, prefix INTEGER NOT NULL, CONSTRAINT FK_AB85CDC120D91DB4 FOREIGN KEY (peer_id) REFERENCES "wireguard_peers" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
		$this->addSql('INSERT INTO wireguard_peer_addresses (id, peer_id, address, prefix) SELECT id, peer_id, address, prefix FROM __temp__wireguard_peer_addresses');
		$this->addSql('DROP TABLE __temp__wireguard_peer_addresses');
		$this->addSql('CREATE INDEX IDX_AB85CDC120D91DB4 ON wireguard_peer_addresses (peer_id)');
		$this->addSql('CREATE TEMPORARY TABLE __temp__wireguard_peers AS SELECT id, interface_id, public_key, psk, keepalive, endpoint, port FROM wireguard_peers');
		$this->addSql('DROP TABLE wireguard_peers');
		$this->addSql('CREATE TABLE wireguard_peers (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, interface_id INTEGER DEFAULT NULL, public_key VARCHAR(255) NOT NULL, psk VARCHAR(255) DEFAULT NULL, keepalive INTEGER NOT NULL, endpoint VARCHAR(255) NOT NULL, port INTEGER NOT NULL, CONSTRAINT FK_23ACBD91AB0BE982 FOREIGN KEY (interface_id) REFERENCES "wireguard_interfaces" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
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
		$this->addSql('CREATE TEMPORARY TABLE __temp__api_keys AS SELECT id, hash, salt, description, expiration FROM "api_keys"');
		$this->addSql('DROP TABLE "api_keys"');
		$this->addSql('CREATE TABLE "api_keys" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, hash VARCHAR(255) NOT NULL, salt VARCHAR(22) NOT NULL, description VARCHAR(255) NOT NULL, expiration DATE DEFAULT NULL)');
		$this->addSql('INSERT INTO "api_keys" (id, hash, salt, description, expiration) SELECT id, hash, salt, description, expiration FROM __temp__api_keys');
		$this->addSql('DROP TABLE __temp__api_keys');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_9579321FD1B862B8 ON "api_keys" (hash)');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_9579321F8FFBE0F7 ON "api_keys" (salt)');
		$this->addSql('CREATE TEMPORARY TABLE __temp__controller_pin_configs AS SELECT id, name, device_type, green_led, red_led, button, sck, sda FROM "controller_pin_configs"');
		$this->addSql('DROP TABLE "controller_pin_configs"');
		$this->addSql('CREATE TABLE "controller_pin_configs" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, device_type VARCHAR(255) DEFAULT \'board\' NOT NULL, green_led INTEGER NOT NULL, red_led INTEGER NOT NULL, button INTEGER NOT NULL, sck INTEGER DEFAULT NULL, sda INTEGER DEFAULT NULL)');
		$this->addSql('INSERT INTO "controller_pin_configs" (id, name, device_type, green_led, red_led, button, sck, sda) SELECT id, name, device_type, green_led, red_led, button, sck, sda FROM __temp__controller_pin_configs');
		$this->addSql('DROP TABLE __temp__controller_pin_configs');
		$this->addSql('CREATE TEMPORARY TABLE __temp__mappings AS SELECT id, type, name, device_type, iqrf_interface, bus_enable_gpio_pin, pgm_switch_gpio_pin, power_enable_gpio_pin, baud_rate, i2c_enable_gpio_pin, spi_enable_gpio_pin, uart_enable_gpio_pin FROM mappings');
		$this->addSql('DROP TABLE mappings');
		$this->addSql('CREATE TABLE mappings (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, type VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, device_type VARCHAR(255) DEFAULT \'board\' NOT NULL, iqrf_interface VARCHAR(255) NOT NULL, bus_enable_gpio_pin INTEGER NOT NULL, pgm_switch_gpio_pin INTEGER NOT NULL, power_enable_gpio_pin INTEGER NOT NULL, baud_rate INTEGER DEFAULT NULL, i2c_enable_gpio_pin INTEGER DEFAULT NULL, spi_enable_gpio_pin INTEGER DEFAULT NULL, uart_enable_gpio_pin INTEGER DEFAULT NULL)');
		$this->addSql('INSERT INTO mappings (id, type, name, device_type, iqrf_interface, bus_enable_gpio_pin, pgm_switch_gpio_pin, power_enable_gpio_pin, baud_rate, i2c_enable_gpio_pin, spi_enable_gpio_pin, uart_enable_gpio_pin) SELECT id, type, name, device_type, iqrf_interface, bus_enable_gpio_pin, pgm_switch_gpio_pin, power_enable_gpio_pin, baud_rate, i2c_enable_gpio_pin, spi_enable_gpio_pin, uart_enable_gpio_pin FROM __temp__mappings');
		$this->addSql('DROP TABLE __temp__mappings');
		$this->addSql('CREATE TEMPORARY TABLE __temp__password_recovery AS SELECT uuid, user, created_at FROM "password_recovery"');
		$this->addSql('DROP TABLE "password_recovery"');
		$this->addSql('CREATE TABLE "password_recovery" (uuid CHAR(36) NOT NULL --(DC2Type:uuid)
		, user INTEGER DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(uuid))');
		$this->addSql('INSERT INTO "password_recovery" (uuid, user, created_at) SELECT uuid, user, created_at FROM __temp__password_recovery');
		$this->addSql('DROP TABLE __temp__password_recovery');
		$this->addSql('CREATE INDEX IDX_63D401098D93D649 ON "password_recovery" (user)');
		$this->addSql('CREATE TEMPORARY TABLE __temp__wireguard_interface_ipv4s AS SELECT id, interface_id, address, prefix FROM "wireguard_interface_ipv4s"');
		$this->addSql('DROP TABLE "wireguard_interface_ipv4s"');
		$this->addSql('CREATE TABLE "wireguard_interface_ipv4s" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, interface_id INTEGER DEFAULT NULL, address BLOB NOT NULL --(DC2Type:ip)
		, prefix INTEGER NOT NULL)');
		$this->addSql('INSERT INTO "wireguard_interface_ipv4s" (id, interface_id, address, prefix) SELECT id, interface_id, address, prefix FROM __temp__wireguard_interface_ipv4s');
		$this->addSql('DROP TABLE __temp__wireguard_interface_ipv4s');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_EA5C8753AB0BE982 ON "wireguard_interface_ipv4s" (interface_id)');
		$this->addSql('CREATE TEMPORARY TABLE __temp__wireguard_interface_ipv6s AS SELECT id, interface_id, address, prefix FROM "wireguard_interface_ipv6s"');
		$this->addSql('DROP TABLE "wireguard_interface_ipv6s"');
		$this->addSql('CREATE TABLE "wireguard_interface_ipv6s" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, interface_id INTEGER DEFAULT NULL, address BLOB NOT NULL --(DC2Type:ip)
		, prefix INTEGER NOT NULL)');
		$this->addSql('INSERT INTO "wireguard_interface_ipv6s" (id, interface_id, address, prefix) SELECT id, interface_id, address, prefix FROM __temp__wireguard_interface_ipv6s');
		$this->addSql('DROP TABLE __temp__wireguard_interface_ipv6s');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_D86AE5D1AB0BE982 ON "wireguard_interface_ipv6s" (interface_id)');
		$this->addSql('CREATE TEMPORARY TABLE __temp__wireguard_peer_addresses AS SELECT id, peer_id, address, prefix FROM "wireguard_peer_addresses"');
		$this->addSql('DROP TABLE "wireguard_peer_addresses"');
		$this->addSql('CREATE TABLE "wireguard_peer_addresses" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, peer_id INTEGER DEFAULT NULL, address BLOB NOT NULL --(DC2Type:ip)
		, prefix INTEGER NOT NULL)');
		$this->addSql('INSERT INTO "wireguard_peer_addresses" (id, peer_id, address, prefix) SELECT id, peer_id, address, prefix FROM __temp__wireguard_peer_addresses');
		$this->addSql('DROP TABLE __temp__wireguard_peer_addresses');
		$this->addSql('CREATE INDEX IDX_AB85CDC120D91DB4 ON "wireguard_peer_addresses" (peer_id)');
		$this->addSql('CREATE TEMPORARY TABLE __temp__wireguard_peers AS SELECT id, interface_id, public_key, psk, keepalive, endpoint, port FROM "wireguard_peers"');
		$this->addSql('DROP TABLE "wireguard_peers"');
		$this->addSql('CREATE TABLE "wireguard_peers" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, interface_id INTEGER DEFAULT NULL, public_key VARCHAR(255) NOT NULL, psk VARCHAR(255) DEFAULT NULL, keepalive INTEGER NOT NULL, endpoint VARCHAR(255) NOT NULL, port INTEGER NOT NULL)');
		$this->addSql('INSERT INTO "wireguard_peers" (id, interface_id, public_key, psk, keepalive, endpoint, port) SELECT id, interface_id, public_key, psk, keepalive, endpoint, port FROM __temp__wireguard_peers');
		$this->addSql('DROP TABLE __temp__wireguard_peers');
		$this->addSql('CREATE INDEX IDX_23ACBD91AB0BE982 ON "wireguard_peers" (interface_id)');
	}
}
