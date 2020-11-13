/**
 * IQRF interface base interface
 */
interface IIqrfBase {
	/**
	 * Component name
	 */
	component: string
	
	/**
	 * Instance name
	 */
	instance: string
}

/**
 * IQRF communication interface base interface
 */
interface IIqrfComBase extends IIqrfBase {
	/**
	 * Device name
	 */
	IqrfInterface: string

	/**
	 * Power enable GPIO pin
	 */
	powerEnableGpioPin: number
	
	/**
	 * Programming mode switch GPIO pin
	 */
	pgmSwitchGpioPin?: number
	
	/**
	 * Bus enable GPIO pin
	 */
	busEnableGpioPin: number

	/**
	 * I2C interface enable GPIO pin
	 */
	i2cEnableGpioPin?: number

	/**
	 * SPI interface enable GPIO pin
	 */
	spiEnableGpioPin?: number

	/**
	 * UART interface enable GPIO pin
	 */
	uartEnableGpioPin?: number
}

/**
 * IQRF SPI component instance interface
 */
export interface IIqrfSpi extends IIqrfComBase {
	/**
	 * Programming mode switch GPIO pin
	 */
	pgmSwitchGpioPin: number

	/**
	 * Should SPI component instance reset?
	 */
	spiReset: boolean
}

/**
 * IQRF UART component instance interface
 */
export interface IIqrfUart extends IIqrfComBase {
	/**
	 * Serial port baud rate
	 */
	baudRate: number
	
	/**
	 * Should UART component instance reset?
	 */
	uartReset?: boolean
}

/**
 * IQRF CDC component instance interface
 */
export interface IIqrfCdc extends IIqrfBase {
	/**
	 * Device name
	 */
	IqrfInterface: string
}

/**
 * IQRF DPA component instance interface
 */
export interface IIqrfDpa {
	/**
	 * Component name
	 */
	component: string

	/**
	 * Component instance name
	 */
	instance: string

	/**
	 * DPA response and confirmation timeout in milliseconds
	 */
	DpaHandlerTimeout: number
}
