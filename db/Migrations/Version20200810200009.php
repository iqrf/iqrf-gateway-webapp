<?php

declare(strict_types = 1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * API key repository migration
 */
final class Version20200810200009 extends AbstractMigration {
	/**
	 * Returns the migration description
	 * @return string Migration description
	 */
	public function getDescription(): string {
		return 'Added API key repository';
	}

	public function up(Schema $schema): void {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

		$this->addSql('CREATE TABLE "api_keys" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, hash VARCHAR(255) NOT NULL, salt VARCHAR(22) NOT NULL, description VARCHAR(255) NOT NULL, expiration DATE DEFAULT NULL)');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_9579321FD1B862B8 ON "api_keys" (hash)');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_9579321F8FFBE0F7 ON "api_keys" (salt)');
	}

	public function down(Schema $schema): void {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

		$this->addSql('DROP TABLE "api_keys"');
	}
}
