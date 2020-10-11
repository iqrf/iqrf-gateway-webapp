/**
 * SPI mapping interface
 */
export interface ISPIMapping {
	IqrfInterface: string
	busEnableGpioPin: number
	pgmSwitchGpioPin: number
	powerEnableGpioPin: number
}

/**
 * UART mapping interface
 */
export interface IUartMapping extends ISPIMapping {
    baudRate: number
}

/**
 * Mappings interface
 */
export interface IMappings {
	spi: Record<string, Record<string, ISPIMapping>>
	uart: Record<string, Record<string, IUartMapping>>
}
