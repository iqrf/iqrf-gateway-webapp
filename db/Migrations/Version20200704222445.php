<?php

declare(strict_types = 1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * IQRF OS patch repository migration
 */
final class Version20200704222445 extends AbstractMigration {

	/**
	 * Returns the migration description
	 * @return string Migration description
	 */
	public function getDescription(): string {
		return 'Added IQRF OS patch repository';
	}

	public function up(Schema $schema): void {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

		$this->addSql('CREATE TABLE "iqrf_os_patches" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, module_type VARCHAR(15) NOT NULL, from_version INTEGER NOT NULL, from_build INTEGER NOT NULL, to_version INTEGER NOT NULL, to_build INTEGER NOT NULL, part INTEGER NOT NULL, parts INTEGER NOT NULL, file_name VARCHAR(255) NOT NULL)');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_B8038822D7DF1668 ON "iqrf_os_patches" (file_name)');
	}

	public function down(Schema $schema): void {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

		$this->addSql('DROP TABLE "iqrf_os_patches"');
	}
}
