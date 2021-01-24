/**
 * Daemon configuration component
 */
export interface IComponent {
	enabled: boolean
	libraryName: string
	libraryPath: string
	name: string
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
