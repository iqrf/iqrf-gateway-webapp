<?php

declare(strict_types = 1);

namespace Database\Migrations;

use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210725062840 extends AbstractMigration {

	/**
	 * Returns the migration description
	 * @return string Migration description
	 */
	public function getDescription(): string {
		return 'Added SSH key repository';
	}

	/**
	 * Applies the migration
	 * @param Schema $schema Database schema
	 */
	public function up(Schema $schema): void {
		$this->abortIf(!$this->connection->getDatabasePlatform() instanceof SQLitePlatform, 'Migration can only be executed safely on \'sqlite\'.');

		$this->addSql('CREATE TABLE "ssh_keys" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, type VARCHAR(255) NOT NULL, "key" VARCHAR(2048) NOT NULL, hash VARCHAR(64) NOT NULL, description VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL)');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_5153CD718A90ABA9 ON "ssh_keys" ("key")');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_5153CD71D1B862B8 ON "ssh_keys" (hash)');
	}

	/**
	 * Reverts the migration
	 * @param Schema $schema Database schema
	 */
	public function down(Schema $schema): void {
		$this->abortIf(!$this->connection->getDatabasePlatform() instanceof SQLitePlatform, 'Migration can only be executed safely on \'sqlite\'.');

		$this->addSql('DROP TABLE "ssh_keys"');
	}

}
