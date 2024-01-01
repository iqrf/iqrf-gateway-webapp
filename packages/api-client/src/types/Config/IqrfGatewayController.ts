/**
 * Copyright 2023-2024 MICRORISC s.r.o.
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
 * IQRF Gateway Controller mapping device type
 */
export enum IqrfGatewayControllerMappingDevice {

	/**
	 * Adapter
	 */
	Adapter = 'adapter',

	/**
	 * Board
	 */
	Board = 'board',

}

/**
 * IQRF Gateway Controller mapping type
 */
export enum IqrfGatewayControllerMappingType {

	/**
	 * USB CDC
	 */
	CDC = 'cdc',

	/**
	 * SPI
	 */
	SPI = 'spi',

	/**
	 * UART
	 */
	UART = 'uart',

}

/**
 * IQRF Gateway Controller mapping configuration
 */
export interface IqrfGatewayControllerMapping {

	/**
	 * Mapping ID
	 */
	id?: number;

	/**
	 * Mapping name
	 */
	name: string;

	/**
	 * Device type
	 */
	deviceType: IqrfGatewayControllerMappingDevice;

	/**
	 * Green LED GPIO pin
	 */
	greenLed: number;

	/**
	 * Red LED GPIO pin
	 */
	redLed: number;

	/**
	 * Button GPIO pin
	 */
	button: number;

	/**
	 * I2C clock GPIO pin
	 */
	sck?: number;

	/**
	 * I2C data GPIO pin
	 */
	sda?: number;

}
