<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260203024103 extends AbstractMigration {

	public function getDescription(): string {
		return '';
	}

	public function up(Schema $schema): void {
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('CREATE TEMPORARY TABLE __temp__wireguard_interfaces AS SELECT id, name, private_key, port FROM wireguard_interfaces');
		$this->addSql('DROP TABLE wireguard_interfaces');
		$this->addSql('CREATE TABLE wireguard_interfaces (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, private_key VARCHAR(255) NOT NULL, port INTEGER DEFAULT NULL)');
		$this->addSql('INSERT INTO wireguard_interfaces (id, name, private_key, port) SELECT id, name, private_key, port FROM __temp__wireguard_interfaces');
		$this->addSql('DROP TABLE __temp__wireguard_interfaces');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_25B210A65E237E06 ON wireguard_interfaces (name)');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_25B210A6F7F984A6 ON wireguard_interfaces (private_key)');
	}

	public function down(Schema $schema): void {
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('CREATE TEMPORARY TABLE __temp__wireguard_interfaces AS SELECT name, private_key, port, id FROM wireguard_interfaces');
		$this->addSql('DROP TABLE wireguard_interfaces');
		$this->addSql('CREATE TABLE wireguard_interfaces (name VARCHAR(255) NOT NULL, private_key VARCHAR(255) NOT NULL, port INTEGER DEFAULT NULL, id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL)');
		$this->addSql('INSERT INTO wireguard_interfaces (name, private_key, port, id) SELECT name, private_key, port, id FROM __temp__wireguard_interfaces');
		$this->addSql('DROP TABLE __temp__wireguard_interfaces');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_25B210A65E237E06 ON wireguard_interfaces (name)');
	}

}
