/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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
 * Daemon configuration component
 */
export interface IComponent {
	/**
	 * Component enabled
	 */
	enabled: boolean

	/**
	 * Component library name
	 */
	libraryName: string

	/**
	 * Path to component library
	 */
	libraryPath: string

	/**
	 * Component name
	 */
	name: string

	/**
	 * Component startup priority
	 */
	startLevel: number
}

/**
 * Daemon IQRF interface state change interface
 */
export interface IChangeComponent {
	/**
	 * Interface name
	 */
	name: string

	/**
	 * Interface state
	 */
	enabled: boolean
}

/**
 * Daemon component config fetch interface
 */
export interface IConfigFetch {
	/**
	 * Component name
	 */
	name: string

	/**
	 * Config fetch succeeded
	 */
	success: boolean
}

/**
 * Main daemon configuration interface
 */
export interface IMainConfig {

	/**
	 * Application name
	 */
	applicationName: string

	/**
	 * Resource directory
	 */
	resourceDir: string

	/**
	 * Cache directory
	 */
	cacheDir: string

	/**
	 * Configuration directory
	 */
	configurationDir: string

	/**
	 * Data directory
	 */
	dataDir: string

	/**
	 * Deployment directory
	 */
	deploymentDir: string

	/**
	 * User directory
	 */
	userDir: string
}

/**
 * Component instance configuration base
 */
export interface ComponentInstanceBase {
	/**
	 * Component name
	 */
	component: string

	/**
	 * Instance name
	 */
	instance: string
}
