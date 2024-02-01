/**
 * IQMESH TrConfiguration embedded peripherals interface
 */
export interface TrConfigPers {
	/// Coordinator
	coordinator?: boolean;
	/// EEEPROM
	eeeprom: boolean;
	/// EEPROM
	eeprom: boolean;
	/// FRC
	frc?: boolean;
	/// IO
	io: boolean;
	/// LEDG
	ledg: boolean;
	/// LEDR
	ledr: boolean;
	/// Node
	node?: boolean;
	/// OS
	os?: boolean;
	/// PWM
	pwm?: boolean;
	/// RAM
	ram: boolean;
	/// SPI
	spi?: boolean;
	/// Thermometer
	thermometer: boolean;
	/// UART
	uart?: boolean;
	/// Raw values
	values?: number[];
}

/**
 * IQMESH TrConfiguration parameters interface
 */
export interface IqmeshTrConfigParams {
	/// Access password
	accessPassword?: string;
	/// Custom DPA handler enabled
	customDpaHandler: boolean;
	/// Run DPA autoexec at boot time
	dpaAutoexec?: boolean;
	/// DPA P2P enabled
	dpaPeerToPeer?: boolean;
	/// Embedded peripherals
	embPers: TrConfigPers;
	/// Run IO setup at boot time
	ioSetup: boolean;
	/// Accept local FRC messages
	localFrcReception?: boolean;
	/// LP RX timeout
	lpRxTimeout?: number;
	/// Unbonded node does not sleep
	neverSleep?: boolean;
	/// Node controllable by SPI or UART interface (DPA < 4.x)
	nodeDpaInterface?: boolean;
	/// User P2P enabled
	peerToPeer: boolean;
	/// Alternative DPA service mode channel
	rfAltDsmChannel: number;
	/// RF band
	rfBand: string;
	/// Main RF channel A of main network
	rfChannelA: number;
	/// Main RF channel B (RFPGM only)
	rfChannelB: number;
	/// Dual channel function
	rfPgmDualChannel: boolean;
	/// RFPGM active after module reset
	rfPgmEnableAfterReset: boolean;
	/// Last RFPGM upload success
	rfPgmIncorrectUpload?: boolean;
	/// Use LP RX mode during RFPGM
	rfPgmLpMode: boolean;
	/// RFPGM deactivated 1 minute after module reset
	rfPgmTerminateAfter1Min: boolean;
	/// RFPGM deactivated using dedicated MCU pin
	rfPgmTerminateMcuPin: boolean;
	/// Main RF subchannel A
	rfSubChannelA?: number;
	/// Main RF subchannel B
	rfSubChannelB?: number;
	/// Disable routing
	routingOff?: boolean;
	/// RX filter
	rxFilter: number;
	/// User key
	securityUserKey?: string;
	/// Serial EEEProm present (read-only)
	serialEepromPresent?: boolean;
	/// STD+LP if set, STD otherwise
	stdAndLpNetwork?: boolean;
	/// On-board thermometer present (read-only)
	thermometerSensorPresent?: boolean;
	/// IL type transciever for Israel region (read-only)
	transcieverILType?: boolean;
	/// TX power
	txPower: number;
	/// UART interface baud rate
	uartBaudrate: number;
}

/**
 * IQMESH ReadTrConfig parameters interface
 */
export interface IqmeshReadTrConfigParams {
	/// Device address
	deviceAddr: number;
	/// HWPID filter
	hwpId: number;
}

/**
 * IQMESH WriteTrConfig parameters interface
 */
export interface IqmeshWriteTrConfigParams extends IqmeshTrConfigParams {
	/// Device address
	deviceAddr: number;
	/// HWPID filter
	hwpId: number;
}
