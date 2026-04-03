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

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Adds user and API key Role table where access scopes can be assigned
 */
final class Version20260403015259 extends AbstractMigration
{
    /**
	 * Returns the migration description
	 * @return string Migration description
	 */
	public function getDescription(): string
	{
		return 'Add Role table and updates user roles';
	}

	/**
	 * Applies the migration
	 * @param Schema $schema Database schema
	 */
    public function up(Schema $schema): void
    {
        // Add new Role table
        $this->addSql('CREATE TABLE roles (name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, scopes CLOB NOT NULL, system BOOLEAN DEFAULT 0 NOT NULL, system_key VARCHAR(64) DEFAULT NULL, id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B63E2EC75E237E06 ON roles (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B63E2EC747280172 ON roles (system_key)');
		// Add system roles
		$this->addSql(
			'INSERT INTO roles (name, description, scopes, system, system_key) VALUES (:name, :description, :scopes, :system, :systemKey)',
			[
				'name' => 'admin',
				'description' => 'System administrator role',
				'scopes' => json_encode([
					'account:read',
					'account:write',
					'config:automaticUpgrades:read',
					'config:automaticUpgrades:write',
					'config:features:read',
					'config:features:write',
					'config:iqrfGatewayController:read',
					'config:iqrfGatewayController:write',
					'config:iqrfGatewayDaemon:read',
					'config:iqrfGatewayDaemon:write',
					'config:iqrfGatewayInfluxdbBridge:read',
					'config:iqrfGatewayInfluxdbBridge:write',
					'config:iqrfRepository:read',
					'config:iqrfRepository:write',
					'config:journal:read',
					'config:journal:write',
					'config:mailer:read',
					'config:mailer:write',
					'config:mender:read',
					'config:mender:write',
					'config:monit:read',
					'config:monit:write',
					'config:time:read',
					'config:time:write',
					'config:translator:read',
					'config:translator:write',
					'gateway:backup:execute',
					'gateway:diagnostic:read',
					'gateway:information:read',
					'gateway:information:write',
					'gateway:mender:execute',
					'gateway:power:execute',
					'gateway:power:read',
					'gateway:service:execute',
					'gateway:service:read',
					'gateway:version:read',
					'ipNetwork:physicalConnections:execute',
					'ipNetwork:physicalConnections:read',
					'ipNetwork:physicalConnections:write',
					'ipNetwork:vpns:execute',
					'ipNetwork:vpns:read',
					'ipNetwork:vpns:write',
					'iqrfNetwork:macros:read',
					'iqrfNetwork:trUpload:execute',
					'openapi:read',
					'security:apiKeys:read',
					'security:apiKeys:write',
					'security:certificates:read',
					'security:certificates:write',
					'security:role:read',
					'security:role:write',
					'security:shellUser:write',
					'security:sshkeys:read',
					'security:sshkeys:write',
					'security:users:read',
					'security:users:write',
				], JSON_THROW_ON_ERROR),
				'system' => true,
				'systemKey' => 'admin',
			]
		);
		$this->addSql(
			'INSERT INTO roles (name, description, scopes, system, system_key) VALUES (:name, :description, :scopes, :system, :systemKey)',
			[
				'name' => 'normal',
				'description' => 'Normal user role',
				'scopes' => json_encode([
					'account:read',
					'account:write',
					'gateway:diagnostic:read',
					'gateway:information:read',
					'gateway:power:execute',
					'gateway:power:read',
					'gateway:version:read',
				], JSON_THROW_ON_ERROR),
				'system' => true,
				'systemKey' => 'normal',
			]
		);
		$this->addSql(
			'INSERT INTO roles (name, description, scopes, system, system_key) VALUES (:name, :description, :scopes, :system, :systemKey)',
			[
				'name' => 'viewer',
				'description' => 'Viewer user role',
				'scopes' => json_encode([
					'account:read',
					'account:write',
					'gateway:information:read',
					'gateway:version:read',
				], JSON_THROW_ON_ERROR),
				'system' => true,
				'systemKey' => 'viewer',
			]
		);


        $this->addSql('CREATE TEMPORARY TABLE __temp__api_keys_v2 AS SELECT expiration, hash, salt, revoked_at, description, state, id, created_at, revoked_by_id, created_by_id FROM api_keys_v2');
        $this->addSql('DROP TABLE api_keys_v2');
        $this->addSql('CREATE TABLE api_keys_v2 (expiration DATETIME NOT NULL, hash VARCHAR(64) NOT NULL, salt VARCHAR(32) NOT NULL, revoked_at DATETIME DEFAULT NULL, description VARCHAR(255) NOT NULL, state INTEGER DEFAULT 0 NOT NULL, id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created_at DATETIME NOT NULL, revoked_by_id INTEGER DEFAULT NULL, created_by_id INTEGER DEFAULT NULL, role_id INTEGER NOT NULL, CONSTRAINT FK_FAC0465EFB8FE773 FOREIGN KEY (revoked_by_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_FAC0465EB03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_FAC0465ED60322AC FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO api_keys_v2 (expiration, hash, salt, revoked_at, description, state, id, created_at, revoked_by_id, created_by_id) SELECT expiration, hash, salt, revoked_at, description, state, id, created_at, revoked_by_id, created_by_id FROM __temp__api_keys_v2');
        $this->addSql('DROP TABLE __temp__api_keys_v2');
        $this->addSql('CREATE INDEX IDX_FAC0465EB03A8386 ON api_keys_v2 (created_by_id)');
        $this->addSql('CREATE INDEX IDX_FAC0465EFB8FE773 ON api_keys_v2 (revoked_by_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FAC0465E8FFBE0F7 ON api_keys_v2 (salt)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FAC0465ED1B862B8 ON api_keys_v2 (hash)');
        $this->addSql('CREATE INDEX IDX_FAC0465ED60322AC ON api_keys_v2 (role_id)');

		// Update user table
        $this->addSql('CREATE TEMPORARY TABLE __temp__users AS SELECT id, username, password, role, language, email, state FROM users');
        $this->addSql('DROP TABLE users');
        $this->addSql('CREATE TABLE users (
			id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
			username VARCHAR(255) NOT NULL,
			password VARCHAR(255) DEFAULT NULL,
			language VARCHAR(7) DEFAULT \'en\' NOT NULL,
			email VARCHAR(255) DEFAULT NULL,
			state INTEGER DEFAULT 0 NOT NULL,
			role_id INTEGER NOT NULL,
			CONSTRAINT FK_1483A5E9D60322AC FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
		)');

        $this->addSql(<<<'SQL'
			INSERT INTO users (id, username, password, language, email, state, role_id)
			SELECT
			id,
			username,
			password,
			language,
			email,
			state,
			(
				SELECT r.id
				FROM roles r
				WHERE r.system_key = CASE __temp__users.role
					WHEN 'admin'  THEN 'admin'
					WHEN 'normal' THEN 'normal'
					WHEN 'basic'  THEN 'viewer'
				END
			)
			FROM __temp__users
			SQL
		);
		$this->addSql('DROP TABLE __temp__users');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9F85E0677 ON users (username)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('CREATE INDEX IDX_1483A5E9D60322AC ON users (role_id)');
    }

	/**
	 * Reverts the migration
	 * @param Schema $schema Database schema
	 */
    public function down(Schema $schema): void
    {
        // Revert API keys
		$this->addSql('CREATE TEMPORARY TABLE __temp__api_keys_v2 AS
			SELECT
				expiration,
				hash,
				salt,
				revoked_at,
				description,
				state,
				id,
				created_at,
				revoked_by_id,
				created_by_id,
				role_id
			FROM api_keys_v2
		');
        $this->addSql('DROP TABLE api_keys_v2');
        $this->addSql('CREATE TABLE api_keys_v2 (
			expiration DATETIME NOT NULL,
			hash VARCHAR(64) NOT NULL,
			salt VARCHAR(32) NOT NULL,
			revoked_at DATETIME DEFAULT NULL,
			description VARCHAR(255) NOT NULL,
			state INTEGER DEFAULT 0 NOT NULL,
			id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
			created_at DATETIME NOT NULL,
			revoked_by_id INTEGER DEFAULT NULL,
			created_by_id INTEGER DEFAULT NULL,
			scopes CLOB NOT NULL,
			CONSTRAINT FK_FAC0465EFB8FE773 FOREIGN KEY (revoked_by_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE,
			CONSTRAINT FK_FAC0465EB03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
		');
        $this->addSql('INSERT INTO api_keys_v2 (
				expiration,
				hash,
				salt,
				revoked_at,
				description,
				state,
				id,
				created_at,
				revoked_by_id,
				created_by_id,
				scopes
			) SELECT
				t.expiration,
				t.hash,
				t.salt,
				t.revoked_at,
				t.description,
				t.state,
				t.id,
				t.created_at,
				t.revoked_by_id,
				t.created_by_id,
				r.scopes
			FROM __temp__api_keys_v2 t
			JOIN roles r ON r.id = t.role_id
		');
		$this->addSql('DROP TABLE __temp__api_keys_v2');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FAC0465ED1B862B8 ON api_keys_v2 (hash)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FAC0465E8FFBE0F7 ON api_keys_v2 (salt)');
        $this->addSql('CREATE INDEX IDX_FAC0465EFB8FE773 ON api_keys_v2 (revoked_by_id)');
        $this->addSql('CREATE INDEX IDX_FAC0465EB03A8386 ON api_keys_v2 (created_by_id)');

		// Revert user role changes
        $this->addSql('CREATE TEMPORARY TABLE __temp__users AS
			SELECT
				email,
				password,
				username,
				language,
				state,
				id,
				role_id
			FROM users
		');
        $this->addSql('DROP TABLE users');
        $this->addSql(
			'CREATE TABLE users (
				email VARCHAR(255) DEFAULT NULL,
				password VARCHAR(255) DEFAULT NULL,
				username VARCHAR(255) NOT NULL,
				language VARCHAR(7) DEFAULT \'en\' NOT NULL,
				state INTEGER DEFAULT 0 NOT NULL,
				id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
				role VARCHAR(15) DEFAULT \'normal\' NOT NULL
			)'
		);
        $this->addSql(<<<'SQL'
			INSERT INTO users (
				email,
				password,
				username,
				language,
				state,
				id,
				role
			) SELECT
				email,
				password,
				username,
				language,
				state,
				id,
				(
					SELECT CASE r.system_key
						WHEN 'admin'  THEN 'admin'
						WHEN 'normal' THEN 'normal'
						WHEN 'viewer' THEN 'basic'
						ELSE 'normal'
					END
					FROM roles r
					WHERE r.id = __temp__users.role_id
				)
			FROM __temp__users
			SQL
		);
        $this->addSql('DROP TABLE __temp__users');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9F85E0677 ON users (username)');

		// Remove role table
		$this->addSql('DROP TABLE roles');
    }
}
