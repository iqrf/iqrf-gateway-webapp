/**
 * Daemon configuration component interface
 */
export interface IComponent {
	/**
	 * Indicates whether component is enabled
	 */
	enabled: boolean

	/**
	 * Name of library associated with component
	 */
	libraryName: string
	
	/**
	 * Path to library associated with component
	 */
	libraryPath: string

	/**
	 * Component name
	 */
	name: string

	/**
	 * Component instantiation priority
	 */
	startLevel: number
}