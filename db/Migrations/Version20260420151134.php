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
 * Adds user invitations table
 */
final class Version20260420151134 extends AbstractMigration {

	/**
	 * Returns the migration description
	 * @return string Migration description
	 */
	public function getDescription(): string {
		return 'Adds user invitations';
	}

	/**
	 * Applies the migration
	 * @param Schema $schema Database schema
	 */
	public function up(Schema $schema): void {
		$this->addSql('CREATE TABLE user_invitations (uuid CHAR(36) NOT NULL, created_at DATETIME NOT NULL, user INTEGER NOT NULL, PRIMARY KEY (uuid), CONSTRAINT FK_8A3CD93B8D93D649 FOREIGN KEY (user) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_8A3CD93B8D93D649 ON user_invitations (user)');
	}

	/**
	 * Reverts the migration
	 * @param Schema $schema Database schema
	 */
	public function down(Schema $schema): void {
		$this->addSql('DROP TABLE user_invitations');
	}

}
