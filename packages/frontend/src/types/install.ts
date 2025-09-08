/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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

/**
 * Installation error
 */
export enum InstallationError {
	/// Missing dependencies
	MissingDependencies = 'missingDependencies',
	/// Missing PHP extensions
	MissingPhpExtensions = 'missingPhpExtensions',
	/// Missing database migrations
	MissingMigrations = 'missingMigrations',
	/// Misconfigured sudo
	MisconfiguredSudo = 'misconfiguredSudo',
}

/**
 * Installation errors
 */
export interface InstallationErrors {
	/// Has missing dependencies?
	missingDependencies: boolean,
	/// Has missing PHP extensions?
	missingPhpExtensions: boolean,
	/// Has missing database migrations?
	missingMigrations: boolean,
	/// Has misconfigured sudo?
	misconfiguredSudo: boolean,
}

/**
 * Installation step
 */
export enum InstallationStep {
	/// Admin user creation
	UserCreation = 'userCreation',
	/// Admin user preferences
	UserPreferences = 'userPreferences',
	/// Mail server configuration
	MailServerConfiguration = 'mailServerConfiguration',
	/// SSH server configuration
	SshServerConfiguration = 'sshServerConfiguration',
	/// Installation completed
	InstallationCompleted = 'installationCompleted',
}
