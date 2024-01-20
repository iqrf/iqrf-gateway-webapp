export interface TrConfigEmbeddedPeripherals {
	values?: Array<number>
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

export interface TrConfig {
	// pers
	embPers: TrConfigEmbeddedPeripherals
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
	rfBand: string
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