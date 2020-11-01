/**
 * Mapping interface
 */
export interface IMapping {
	id?: number,
	type: string,
	name: string,
	IqrfInterface: string
	busEnableGpioPin: number
	pgmSwitchGpioPin: number
	powerEnableGpioPin: number
	baudRate?: number
}
