/**
 * IQRF communication interface base interface
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
export interface IIqrfSpi extends IIqrfBase {
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
export interface IIqrfUart extends IIqrfBase {
    /**
     * Serial port baud rate
     */
    baudRate: number
    
    /**
     * Should UART component instance reset?
     */
    uartReset?: boolean
}
