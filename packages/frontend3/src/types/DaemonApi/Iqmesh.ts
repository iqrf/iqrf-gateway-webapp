export interface EmbeddedPeripherals {
	values?: number[]
	coordinator?: boolean
	node?: boolean
	os?: boolean
	eeprom: boolean
	eeeprom: boolean
	ram: boolean
	ledg: boolean
	ledr: boolean
	spi?: boolean
	io: boolean
	thermometer: boolean
	pwm?: boolean
	uart?: boolean
	frc?: boolean
}

export interface Discovery {
	discovered: boolean
	parent: number
	vrn: number
	zone: number
}

export interface SlotLimits {
	shortestTimeslot: string
	longestTimeslot: string
	value: number
}

export interface OsRead {
	flags: OsReadFlags
	mid: string
	osBuild: string
	osVersion: string
	rssi: string
	slotLimits: SlotLimits
	supplyVoltage: string
	trMcuType: TrMcuType
}

export interface OsReadFlags {
	dpaHandlerDetected: boolean
	dpaHandlerNotDetectedButEnabled: boolean
	insufficientOsBuild: false
	interfaceType: string
	iqrfOsChanged: boolean
	noInterfaceSupported: boolean
	value: number
}

export interface TrMcuType {
	fccCertified: boolean
	mcuType: string
	trType: string
	value: number
}

export interface PeripheralInformation {
	par1: number
	part: number
	perT: number
	perTe: number
}

export interface PeripheralEnumeration {
	dpaVer: string
	embPers: number[]
	flags: PeripheralEnumerationFlags
	hwpId: number
	hwpIdVer: number
	perNr: number
	userPers: unknown[]
}

export interface PeripheralEnumerationFlags {
	rfModeLp: boolean
	rfModeStd: boolean
	stdAndLpNetwork: boolean
	value: number
}

export interface TrConfiguration {
	// pers
	embPers: EmbeddedPeripherals
	// other
	customDpaHandler: boolean
	dpaPeerToPeer?: boolean
	peerToPeer: boolean
	localFrcReception?: boolean
	ioSetup: boolean
	dpaAutoexec?: boolean
	routingOff?: boolean
	neverSleep?: boolean
	nodeDpaInterface?: boolean
	uartBaudrate: number
	// initphy
	thermometerSensorPresent?: boolean
	serialEepromPresent?: boolean
	transcieverILType?: boolean
	// RF
	rfBand?: string
	rfChannelA: number
	rfChannelB: number
	rfSubChannelA?: number
	rfSubChannelB?: number
	rfAltDsmChannel: number
	stdAndLpNetwork?: boolean
	txPower: number
	rxFilter: number
	lpRxTimeout?: number
	// RFPGM
	rfPgmEnableAfterReset: boolean
	rfPgmTerminateAfter1Min: boolean
	rfPgmTerminateMcuPin: boolean
	rfPgmDualChannel: boolean
	rfPgmLpMode: boolean
	rfPgmIncorrectUpload?: boolean
	// security
	accessPassword?: string
	securityUserKey?: string
}

export interface DeviceEnumeration {
	deviceAddr: number
	discovery: Discovery
	manufacturer: string
	morePeripheralsInfo: PeripheralInformation
	osRead: OsRead
	peripheralEnumeration: PeripheralEnumeration
	product: string
	standards: string[]
	trConfiguration: TrConfiguration
}
