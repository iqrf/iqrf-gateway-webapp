/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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
 * Daemon api raw message raw data interface
 */
export interface RawMessageRData {
	rData: string
}

/**
 * Daemon api raw message data interface
 */
export interface RawMessageData {
	req: RawMessageRData
	returnVerbose: boolean
	timeout?: number
}

/**
 * Daemon api raw message interface
 */
export interface RawMessage {
	mType: string
	data: RawMessageData
}

/**
 * Os information flags interface
 */
export interface OsInfoFlags {
	dpaHandlerDetected: boolean
	dpaHandlerNotDetectedButEnabled: boolean
	insufficientOsBuild: false
	interfaceType: string
	iqrfOsChanged: boolean
	noInterfaceSupported: boolean
	value: number
}

/**
 * Os information slotlimits interface
 */
export interface SlotLimits {
	shortestTimeslot: string
	longestTimeslot: string
	value: number
}

/**
 * Transciever MCU interface
 */
export interface TrMcu {
	fccCertified: boolean
	mcuType: string
	trType: string
	value: number
}

/**
 * Os information interface
 */
export interface OsInfo {
	flags: OsInfoFlags
	mid: string
	osBuild: string
	osVersion: string
	rssi: string
	slotLimits: SlotLimits
	supplyVoltage: string
	trMcuType: TrMcu
}

/**
 * Peripheral enumeration flags interface
 */
export interface PeripheralEnumerationFlags {
	rfModeLp: boolean
	rfModeStd: boolean
	stdAndLpNetwork: boolean
	value: number
}

/**
 * Peripheral enumeration interface
 */
export interface PeripheralEnumeration {
	dpaVer: string
	embPers: Array<number>
	flags: PeripheralEnumerationFlags
	hwpId: number
	hwpIdVer: number
	perNr: number
	userPers: Array<unknown>
}

/**
 * Peripheral information interface
 */
export interface PeripheralInfo {
	par1: number
	part: number
	perT: number
	perTe: number
}

/**
 * Discovery data interface
 */
export interface Discovery {
	discovered: boolean
	parent: number
	vrn: number
	zone: number
}

/**
 * Embedded peripherals implemented interface
 */
export interface IEmbedPers {
	coordinator: boolean
	eeeprom: boolean
	eeprom: boolean
	io: boolean
	ledg: boolean
	ledr: boolean
	node: boolean
	os: boolean
	pwm: boolean
	ram: boolean
	spi: boolean
	thermometer: boolean
	uart: boolean
}

/**
 * Embedded peripheral state interface
 */
export interface IEmbedPersEnabled {
	enabled: boolean
	name: string
}

/**
 * Transciever configuration interface
 */
export interface ITrConfiguration {
	customDpaHandler: boolean
	dpaAutoexec: boolean
	dpaPeerToPeer: boolean
	embPers: IEmbedPers
	ioSetup: boolean
	lpRxTimeout: number
	neverSleep: boolean
	peerToPeer: boolean
	rfAltDsmChannel: number
	rfBand: string
	rfChannelA: number
	rfChannelB: number
	rfPgmDualChannel: boolean
	rfPgmEnableAfterReset: boolean
	rfPgmIncorrectUpload: boolean
	rfPgmLpMode: boolean
	rfPgmTerminateAfter1Min: boolean
	rfPgmTerminateMcuPin: boolean
	routingOff: boolean
	rxFilter: number
	stdAndLpNetwork: boolean
	txPower: number
	uartBaudrate: number
}

/**
 * Device enumeration interface
 */
export interface IDeviceEnumeration {
	deviceAddr: number
	discovery: Discovery
	manufacturer: string
	morePeripheralsInfo: PeripheralInfo
	osRead: OsInfo
	peripheralEnumeration: PeripheralEnumeration
	product: string
	standards: Array<string>
	trConfiguration: ITrConfiguration
}

/**
 * Daemon API response interface
 */
export interface IDpaResp {
	/**
	 * Message type
	 */
	mType: string
	/**
	 * Response data
	 */
	data: IDpaRespData
}

/**
 * Daemon API response data interface
 */
export interface IDpaRespData {
	/**
	 * IQRF Daemon instance ID
	 */
	insId: string

	/**
	 * Message ID
	 */
	msgId: string

	/**
	 * Raw DPA response data
	 */
	raw: IDpaRespRaw

	/**
	 * Parsed, human readable response data
	 */
	rsp: any

	/**
	 * Execution status code
	 */
	status: number

	/**
	 * Execution status string, present if verbose mode is used
	 */
	statusStr?: string
}

/**
 * Daemon API response raw interface
 */
export interface IDpaRespRaw {
	/**
	 * DPA request data
	 */
	request: string

	/**
	 * DPA request timestamp
	 */
	requestTs: string

	/**
	 * DPA confirmation data
	 */
	confirmation: string

	/**
	 * DPA confirmation timestamp
	 */
	confirmationTs: string

	/**
	 * DPA response data
	 */
	response: string

	/**
	 * DPA response timestamp
	 */
	responseTs: string
}
