/**
 * Copyright 2017-2022 IQRF Tech s.r.o.
 * Copyright 2019-2022 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
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
