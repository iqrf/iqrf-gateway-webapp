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
