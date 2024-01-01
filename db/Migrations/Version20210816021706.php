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
 * Password recovery database migration
 */
final class Version20210816021706 extends AbstractMigration {
	/**
	 * Returns the migration description
	 * @return string Migration description
	 */
	public function getDescription(): string {
		return 'Password recovery database migration';
	}

	public function up(Schema $schema): void {
		// this up() migration is auto-generated, please modify it to your needs
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

		$this->addSql('CREATE TABLE "password_recovery" (uuid CHAR(36) NOT NULL --(DC2Type:uuid)
        , user INTEGER DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(uuid))');
		$this->addSql('CREATE INDEX IDX_63D401098D93D649 ON "password_recovery" (user)');
		$this->addSql('DROP INDEX IDX_FE223588D93D649');
		$this->addSql('CREATE TEMPORARY TABLE __temp__email_verification AS SELECT uuid, user, created_at FROM email_verification');
		$this->addSql('DROP TABLE email_verification');
		$this->addSql('CREATE TABLE email_verification (uuid CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:uuid)
        , user INTEGER DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(uuid), CONSTRAINT FK_FE223588D93D649 FOREIGN KEY (user) REFERENCES "users" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
		$this->addSql('INSERT INTO email_verification (uuid, user, created_at) SELECT uuid, user, created_at FROM __temp__email_verification');
		$this->addSql('DROP TABLE __temp__email_verification');
		$this->addSql('CREATE INDEX IDX_FE223588D93D649 ON email_verification (user)');
	}

	public function down(Schema $schema): void {
		// this down() migration is auto-generated, please modify it to your needs
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

		$this->addSql('DROP TABLE "password_recovery"');
		$this->addSql('DROP INDEX IDX_FE223588D93D649');
		$this->addSql('CREATE TEMPORARY TABLE __temp__email_verification AS SELECT uuid, user, created_at FROM "email_verification"');
		$this->addSql('DROP TABLE "email_verification"');
		$this->addSql('CREATE TABLE "email_verification" (uuid CHAR(36) NOT NULL --(DC2Type:uuid)
        , user INTEGER DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(uuid))');
		$this->addSql('INSERT INTO "email_verification" (uuid, user, created_at) SELECT uuid, user, created_at FROM __temp__email_verification');
		$this->addSql('DROP TABLE __temp__email_verification');
		$this->addSql('CREATE INDEX IDX_FE223588D93D649 ON "email_verification" (user)');
	}
}
