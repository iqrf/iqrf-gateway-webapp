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