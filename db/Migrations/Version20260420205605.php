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
 * Adds unique index to password recovery table
 */
final class Version20260420205605 extends AbstractMigration {

	/**
	 * Returns the migration description
	 * @return string Migration description
	 */
	public function getDescription(): string {
		return 'Adds unique index to password recovery table';
	}

	/**
	 * Applies the migration
	 * @param Schema $schema Database schema
	 */
	public function up(Schema $schema): void {
		$this->addSql('CREATE TEMPORARY TABLE __temp__password_recovery AS SELECT uuid, user, created_at FROM password_recovery');
		$this->addSql('DROP TABLE password_recovery');
		$this->addSql('CREATE TABLE password_recovery (uuid CHAR(36) NOT NULL, user INTEGER NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (uuid), CONSTRAINT FK_63D401098D93D649 FOREIGN KEY (user) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
		$this->addSql('INSERT INTO password_recovery (uuid, user, created_at) SELECT uuid, user, created_at FROM __temp__password_recovery');
		$this->addSql('DROP TABLE __temp__password_recovery');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_63D401098D93D649 ON password_recovery (user)');
	}

	/**
	 * Reverts the migration
	 * @param Schema $schema Database schema
	 */
	public function down(Schema $schema): void {
		$this->addSql('CREATE TEMPORARY TABLE __temp__password_recovery AS SELECT uuid, created_at, user FROM password_recovery');
		$this->addSql('DROP TABLE password_recovery');
		$this->addSql('CREATE TABLE password_recovery (uuid CHAR(36) NOT NULL, created_at DATETIME NOT NULL, user INTEGER NOT NULL, PRIMARY KEY (uuid), CONSTRAINT FK_63D401098D93D649 FOREIGN KEY (user) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
		$this->addSql('INSERT INTO password_recovery (uuid, created_at, user) SELECT uuid, created_at, user FROM __temp__password_recovery');
		$this->addSql('DROP TABLE __temp__password_recovery');
		$this->addSql('CREATE INDEX IDX_63D401098D93D649 ON password_recovery (user)');
	}

}
