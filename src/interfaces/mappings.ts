/**
 * Mapping interface
 */
export interface IMapping {
	/**
	 * Mapping ID
	 */
	id?: number

	/**
	 * Mapping type
	 */
	type: string

	/**
	 * Mapping name
	 */
	name: string

	/**
     * Device name
     */
    IqrfInterface: string
    
    /**
     * Serial port baud rate
     */
    baudRate?: number
    
    /**
     * Power enable GPIO pin
     */
    powerEnableGpioPin: number
    
    /**
     * Bus enable GPIO pin
     */
    busEnableGpioPin: number
    
    /**
     * Programming mode switch GPIO pin
     */
    pgmSwitchGpioPin: number

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
