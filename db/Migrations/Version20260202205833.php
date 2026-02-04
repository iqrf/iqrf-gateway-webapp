<?php

declare(strict_types = 1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260202205833 extends AbstractMigration {

	public function getDescription(): string {
		return '';
	}

	public function up(Schema $schema): void {
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('CREATE TEMPORARY TABLE __temp__wireguard_peers AS SELECT id, interface_id, public_key, psk, keepalive, endpoint, port FROM wireguard_peers');
		$this->addSql('DROP TABLE wireguard_peers');
		$this->addSql('CREATE TABLE wireguard_peers (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, interface_id INTEGER NOT NULL, public_key VARCHAR(255) NOT NULL, psk VARCHAR(255) DEFAULT NULL, keepalive INTEGER NOT NULL, endpoint VARCHAR(255) NOT NULL, port INTEGER NOT NULL, CONSTRAINT FK_23ACBD91AB0BE982 FOREIGN KEY (interface_id) REFERENCES wireguard_interfaces (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
		$this->addSql('INSERT INTO wireguard_peers (id, interface_id, public_key, psk, keepalive, endpoint, port) SELECT id, interface_id, public_key, psk, keepalive, endpoint, port FROM __temp__wireguard_peers');
		$this->addSql('DROP TABLE __temp__wireguard_peers');
		$this->addSql('CREATE INDEX IDX_23ACBD91AB0BE982 ON wireguard_peers (interface_id)');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_23ACBD9166F9D463 ON wireguard_peers (public_key)');
	}

	public function down(Schema $schema): void {
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('CREATE TEMPORARY TABLE __temp__wireguard_peers AS SELECT public_key, psk, keepalive, endpoint, port, id, interface_id FROM wireguard_peers');
		$this->addSql('DROP TABLE wireguard_peers');
		$this->addSql('CREATE TABLE wireguard_peers (public_key VARCHAR(255) NOT NULL, psk VARCHAR(255) DEFAULT NULL, keepalive INTEGER NOT NULL, endpoint VARCHAR(255) NOT NULL, port INTEGER NOT NULL, id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, interface_id INTEGER NOT NULL, CONSTRAINT FK_23ACBD91AB0BE982 FOREIGN KEY (interface_id) REFERENCES wireguard_interfaces (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
		$this->addSql('INSERT INTO wireguard_peers (public_key, psk, keepalive, endpoint, port, id, interface_id) SELECT public_key, psk, keepalive, endpoint, port, id, interface_id FROM __temp__wireguard_peers');
		$this->addSql('DROP TABLE __temp__wireguard_peers');
		$this->addSql('CREATE INDEX IDX_23ACBD91AB0BE982 ON wireguard_peers (interface_id)');
	}

}
