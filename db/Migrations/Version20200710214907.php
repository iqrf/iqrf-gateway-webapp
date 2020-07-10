<?php

declare(strict_types = 1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * API key repository migration
 */
final class Version20200710214907 extends AbstractMigration {

	/**
	 * Returns the migration description
	 * @return string Migration description
	 */
	public function getDescription(): string {
		return 'Added API key repository';
	}

	public function up(Schema $schema): void {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

		$this->addSql('CREATE TABLE "api_keys" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, "key" VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, valid_to DATE DEFAULT NULL)');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_9579321F8A90ABA9 ON "api_keys" ("key")');
	}

	public function down(Schema $schema): void {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

		$this->addSql('DROP TABLE "api_keys"');
		$this->addSql('DROP INDEX UNIQ_9AF95CD0D7DF1668');
	}
}
