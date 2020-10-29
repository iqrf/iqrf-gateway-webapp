/**
 * IQRF UART component instance interface
 */
export interface IIqrfUart {
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
     * Serial port baud rate
     */
    baudRate: number
    
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
     * Should UART component instance reset?
     */
	uartReset?: boolean
}

/**
 * UART mapping instance
 */
export interface IUartMapping {
    /**
     * Device name
     */
    IqrfInterface: string
    
    /**
     * Serial port baud rate
     */
    baudRate: number
    
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
}
