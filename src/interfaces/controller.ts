/**
 * IQRF GW Controller configuration interface
 */
export interface IController {
	/**
	 * Daemon API configuration
	 */
	daemonApi: IControllerDaemonApi

	/**
	 * Factory reset configuration
	 */
	factoryReset: IControllerFactoryReset

	/**
	 * Logger configuration
	 */
	logger: IControllerLogger

	/**
	 * Reset button configuration
	 */
	resetButton: IControllerResetButton

	/**
	 * Status LED configuraiton
	 */
	statusLed: IControllerStatusLed

	/**
	 * WebSocket servers configuraiton
	 */
	wsServers: IControllerWsServers
}

/**
 * Controller AutoNetwork API call stop conditions configuration interface
 */
interface IControllerAutoNetworkStopConditions {
	/**
	 * Abort AutoNetwork if too many nodes were found
	 */
	abortOnTooManyNodesFound: boolean

	/**
	 * Maximum number of empty consecutive waves
	 */
	emptyWaves: number

	/**
	 * Maximum number of waves
	 */
	waves: number
}

/**
 * Controller AutoNetwork API call configuration interface
 */
interface IControllerAutoNetwork {
	/**
	 * Number of retry transactions
	 */
	actionRetries: number

	/**
	 * Run discovery before AutoNetwork starts
	 */
	discoveryBeforeStart: boolean

	/**
	 * Discovery TX power
	 */
	discoveryTxPower: number

	/**
	 * Verbose response
	 */
	returnVerbose: boolean

	/**
	 * Skip discovery each AutoNetwork wave
	 */
	skipDiscoveryEachWave: boolean

	/**
	 * AutoNetwork stop conditions configuration
	 */
	stopConditions: IControllerAutoNetworkStopConditions
}

/**
 * Controller Discovery API call configuration interface
 */
interface IControllerDiscovery {
	/**
	 * Maximum address included in discovery process
	 */
	maxAddr: number

	/**
	 * Verbose response
	 */
	returnVerbose: boolean

	/**
	 * TX power
	 */
	txPower: number
}

/**
 * Controller Daemon API call configuration interface
 */
interface IControllerDaemonApi {
	/**
	 * AutoNetwork API call configuration
	 */
	autoNetwork: IControllerAutoNetwork

	/**
	 * Discovery API call configuration
	 */
	discovery: IControllerDiscovery
}

/**
 * Controller factory reset configuration interface
 */
interface IControllerFactoryReset {
	/**
	 * Reset coordinator
	 */
	coordinator: boolean

	/**
	 * Reset IQRF GW Daemon
	 */
	daemon: boolean

	/**
	 * Reset network
	 */
	network: boolean

	/**
	 * Reset IQRF GW Webapp
	 */
	webapp: boolean
}

/**
 * Controller logger configuration interface
 */
interface IControllerLogger {
	/**
	 * Path to logging file
	 */
	filePath: string

	/**
	 * Logging severity level
	 */
	severity: string

	/**
	 * Logging sinks
	 */
	sinks: IControllerLoggerSinks
}

/**
 * Controller logger sinks configuration interface
 */
interface IControllerLoggerSinks {
	/**
	 * Log to file
	 */
	file: boolean

	/**
	 * Log to syslog
	 */
	syslog: boolean
}

/**
 * Controller reset button configuration interface
 */
interface IControllerResetButton {
	/**
	 * Daemon API call to be executed
	 */
	api: string
	
	/**
	 * Reset button GPIO pin number
	 */
	button: number
}

/**
 * Controller configuration status led interface
 */
interface IControllerStatusLed {
	/**
	 * Green LED GPIO pin number
	 */
	greenLed: number

	/**
	 * Red LED GPIO pin number
	 */
	redLed: number
}

/**
 * Controller configuration websocket servers interface
 */
interface IControllerWsServers {
	/**
	 * Daemon API WebSocket server address
	 */
	api: string

	/**
	 * Daemon monitor WebSocket server address
	 */
	monitor: string
}
