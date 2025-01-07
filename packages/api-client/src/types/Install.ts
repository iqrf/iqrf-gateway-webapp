/**
 * Copyright 2023-2025 MICRORISC s.r.o.
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

import { type Feature } from './Feature';

/**
 * Missing dependency
 */
export interface InstallationCheckDependency {
	/// Required command from the dependency
	command: string,
	/// Is the dependency critical?
	critical: boolean,
	/// Name of the feature that requires the missing command
	feature?: Feature,
	/// Name of the package from which the missing dependency comes
	package: string,
}

/**
 * Missing PHP extensions
 */
export interface InstallationCheckPhpMissingExtensions {
	/// Missing PHP extensions
	extensions: string[],
	/// Missing PHP packages
	packages?: string[],
}

/**
 * PHP extensions check
 */
export interface InstallationCheckPhpExtensions {
	/// Are all required PHP extensions loaded?
	allExtensionsLoaded: boolean,
	/// Missing PHP extensions
	missing?: InstallationCheckPhpMissingExtensions,
}

/**
 * sudo check
 */
export interface InstallationCheckSudo {
	///Does the sudo command exist?
	exists: boolean,
	///Name of the user that is used to run the application
	user: string,
	/// Is the user allowed to run sudo commands?
	userSudo: boolean,
}


/**
 * Installation checks
 */
export interface InstallationChecks {
	/// Are all database migrations executed?
	allMigrationsExecuted: boolean,
	/// Missing dependencies
	dependencies: InstallationCheckDependency[],
	/// Are there any users in the database?
	hasUsers?: boolean,
	/// IQRF Gateway ID
	gwId: string | null,
	/// PHP extensions check
	phpModules: InstallationCheckPhpExtensions,
	/// sudo check
	sudo?: InstallationCheckSudo,
}
