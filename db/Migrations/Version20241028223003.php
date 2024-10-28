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
 * Adds user preferences
 */
final class Version20241028223003 extends AbstractMigration {

	/**
	 * Returns a migration description
	 * @return string Migration description
	 */
	public function getDescription(): string {
		return 'Adds user preferences';
	}

	/**
	 * Applies the migration
	 * @param Schema $schema Database schema
	 */
	public function up(Schema $schema): void {
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('CREATE TABLE user_preferences (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user INTEGER DEFAULT NULL, time_format INTEGER NOT NULL, theme_preference INTEGER DEFAULT 0 NOT NULL, CONSTRAINT FK_402A6F608D93D649 FOREIGN KEY (user) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_402A6F608D93D649 ON user_preferences (user)');
		$this->addSql('CREATE TEMPORARY TABLE __temp__email_verification AS SELECT uuid, user, created_at FROM email_verification');
		$this->addSql('DROP TABLE email_verification');
		$this->addSql('CREATE TABLE email_verification (uuid CHAR(36) NOT NULL --(DC2Type:uuid)
		, user INTEGER DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(uuid), CONSTRAINT FK_FE223588D93D649 FOREIGN KEY (user) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
		$this->addSql('INSERT INTO email_verification (uuid, user, created_at) SELECT uuid, user, created_at FROM __temp__email_verification');
		$this->addSql('DROP TABLE __temp__email_verification');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_FE223588D93D649 ON email_verification (user)');
		$this->addSql('CREATE TEMPORARY TABLE __temp__users AS SELECT id, username, password, role, language, email, state FROM users');
		$this->addSql('DROP TABLE users');
		$this->addSql('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(255) DEFAULT NULL, role VARCHAR(15) DEFAULT \'normal\' NOT NULL, language VARCHAR(7) DEFAULT \'en\' NOT NULL, email VARCHAR(255) DEFAULT NULL, state INTEGER DEFAULT 0 NOT NULL)');
		$this->addSql('INSERT INTO users (id, username, password, role, language, email, state) SELECT id, username, password, role, language, email, state FROM __temp__users');
		$this->addSql('DROP TABLE __temp__users');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9F85E0677 ON users (username)');
	}

	/**
	 * Reverts the migration
	 * @param Schema $schema Database schema
	 */
	public function down(Schema $schema): void {
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('DROP TABLE user_preferences');
		$this->addSql('CREATE TEMPORARY TABLE __temp__email_verification AS SELECT uuid, user, created_at FROM email_verification');
		$this->addSql('DROP TABLE email_verification');
		$this->addSql('CREATE TABLE email_verification (uuid CHAR(36) NOT NULL --(DC2Type:uuid)
		, user INTEGER DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(uuid), CONSTRAINT FK_FE223588D93D649 FOREIGN KEY (user) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
		$this->addSql('INSERT INTO email_verification (uuid, user, created_at) SELECT uuid, user, created_at FROM __temp__email_verification');
		$this->addSql('DROP TABLE __temp__email_verification');
		$this->addSql('CREATE INDEX IDX_FE223588D93D649 ON email_verification (user)');
		$this->addSql('CREATE TEMPORARY TABLE __temp__users AS SELECT id, username, email, password, role, state, language FROM users');
		$this->addSql('DROP TABLE users');
		$this->addSql('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, role VARCHAR(15) NOT NULL, state INTEGER DEFAULT 0 NOT NULL, language VARCHAR(7) NOT NULL)');
		$this->addSql('INSERT INTO users (id, username, email, password, role, state, language) SELECT id, username, email, password, role, state, language FROM __temp__users');
		$this->addSql('DROP TABLE __temp__users');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9F85E0677 ON users (username)');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
	}

}
