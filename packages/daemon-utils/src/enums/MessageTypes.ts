/**
 * Generic messages
 */
export enum GenericMessages {
	/// Message error
	MessageError = 'messageError',
	/// Raw DPA message
	Raw = 'iqrfRaw',
}

/**
 * Embedded Exploration message types
 */
export enum EmbedExplorationMessages {
	/// Enumerate peripherals
	Enumerate = 'iqrfEmbedExplore_Enumerate',
	/// Get information from more peripherals
	MorePeripheralsInformation = 'iqrfEmbedExplore_MorePeripheralsInformation',
	/// Get peripheral information
	PeripheralInformation = 'iqrfEmbedExplore_PeripheralInformation',
}

/**
 * Embedded Coordinator message types
 */
export enum EmbedCoordinatorMessages {
	/// Get addressing information
	AddrInfo = 'iqrfEmbedCoordinator_AddrInfo',
	/// Authorize bond
	AuthorizeBond = 'iqrfEmbedCoordinator_AuthorizeBond',
	/// Backup coordinator data
	Backup = 'iqrfEmbedCoordinator_Backup',
	/// Bond node
	BondNode = 'iqrfEmbedCoordinator_BondNode',
	/// Get bonded devices
	BondedDevices = 'iqrfEmbedCoordinator_BondedDevices',
	/// Clear all bonds
	ClearAllBonds = 'iqrfEmbedCoordinator_ClearAllBonds',
	/// Get discovered devices
	DiscoveredDevices = 'iqrfEmbedCoordinator_DiscoveredDevices',
	/// Run discovery
	Discovery = 'iqrfEmbedCoordinator_Discovery',
	/// Restore coordinator data
	Restore = 'iqrfEmbedCoordinator_Restore',
	/// Set DPA param
	SetDpaParams = 'iqrfEmbedCoordinator_SetDpaParams',
	/// Set request and response hops
	SetHops = 'iqrfEmbedCoordinator_SetHops',
	/// Set MID
	SetMid = 'iqrfEmbedCoordinator_SetMID',
	/// SmartConnect
	SmartConnect = 'iqrfEmbedCoordinator_SmartConnect',
}

/**
 * Embedded Node message types
 */
export enum EmbedNodeMessages {
	/// Backup node data
	Backup = 'iqrfEmbedNode_Backup',
	/// Read node information
	Read = 'iqrfEmbedNode_Read',
	/// Remove bond
	RemoveBond = 'iqrfEmbedNode_RemoveBond',
	/// Restore node data
	Restore = 'iqrfEmbedNode_Restore',
	/// Validate bonds
	ValidateBonds = 'iqrfEmbedNode_ValidateBonds',
}

/**
 * Embedded OS message types
 */
export enum EmbedOsMessages {
	/// Execute request batch
	Batch = 'iqrfEmbedOs_Batch',
	/// Factory settings
	FactorySettings = 'iqrfEmbedOs_FactorySettings',
	/// Indicate
	Indicate = 'iqrfEmbedOs_Indicate',
	/// Load code
	LoadCode = 'iqrfEmbedOs_LoadCode',
	/// Read OS data
	Read = 'iqrfEmbedOs_Read',
	/// Read TR configuration
	ReadTrConfig = 'iqrfEmbedOs_ReadCfg',
	/// Reset device
	Reset = 'iqrfEmbedOs_Reset',
	/// Restart device
	Restart = 'iqrfEmbedOs_Restart',
	/// Run RFPGM
	Rfpgm = 'iqrfEmbedOs_Rfpgm',
	/// Execute request batch at selected nodes
	SelectiveBatch = 'iqrfEmbedOs_SelectiveBatch',
	/// Set security
	SetSecurity = 'iqrfEmbedOs_SetSecurity',
	/// Sleep
	Sleep = 'iqrfEmbedOs_Sleep',
	/// Test RF Signal
	TestRfSignal = 'iqrfEmbedOs_TestRfSignal',
	/// Write TR configuration
	WriteTrConfig = 'iqrfEmbedOs_WriteCfg',
	/// Write TR configuration byte
	WriteTrConfigByte = 'iqrfEmbedOs_WriteCfgByte',
}

/**
 * Embedded EEPROM message types
 */
export enum EmbedEepromMessages {
	/// Read from EEPROM
	Read = 'iqrfEmbedEeprom_Read',
	/// Write to EEPROM
	Write = 'iqrfEmbedEeprom_Write',
}

/**
 * Embedded EEPROM message types
 */
export enum EmbedEeepromMessages {
	/// Read from EEEPROM
	Read = 'iqrfEmbedEeeprom_Read',
	/// Write to EEEPROM
	Write = 'iqrfEmbedEeeprom_Write',
}

/**
 * Embedded RAM message types
 */
export enum EmbedRamMessages {
	/// Read from RAM
	Read = 'iqrfEmbedRam_Read',
	/// Write to RAM
	Write = 'iqrfEmbedRam_Write',
}

/**
 * Embedded LEDR message types
 */
export enum EmbedLedrMessages {
	/// Continuous flashing of LED
	Flashing = 'iqrfEmbedLedr_Flashing',
	/// Generate one LED pulse
	Pulse = 'iqrfEmbedLedr_Pulse',
	/// Set LED on or off
	Set = 'iqrfEmbedLedr_Set',
	/// Set LED off
	SetOff = 'iqrfEmbedLedr_SetOff',
	/// Set LED on
	SetOn = 'iqrfEmbedLedr_SetOn',
}

/**
 * Embedded LEDG message types
 */
export enum EmbedLedgMessages {
	/// Continuous flashing of LED
	Flashing = 'iqrfEmbedLedg_Flashing',
	/// Generate one LED pulse
	Pulse = 'iqrfEmbedLedg_Pulse',
	/// Set LED on or off
	Set = 'iqrfEmbedLedg_Set',
	/// Set LED off
	SetOff = 'iqrfEmbedLedg_SetOff',
	/// Set LED on
	SetOn = 'iqrfEmbedLedg_SetOn',
}

/**
 * Embedded SPI message types
 */
export enum EmbedSpiMessages {
	/// Write and Read
	WriteRead = 'iqrfEmbedSpi_WriteRead',
}

/**
 * Embedded IO message types
 */
export enum EmbedIoMessages {
	/// Set IO direction
	Direction = 'iqrfEmbedIo_Direction',
	/// Get IO values
	Get = 'iqrfEmbedIo_Get',
	/// Set IO values
	Set = 'iqrfEmbedIo_Set',
}

/**
 * Embedded Thermometer message types
 */
export enum EmbedThermometerMessages {
	/// Read thermometer value
	Read = 'iqrfEmbedThermometer_Read',
}

/**
 * Embedded UART message types
 */
export enum EmbedUartMessages {
	/// Clear, read and optionally write data
	ClearWriteRead = 'iqrfEmbedUart_ClearWriteRead',
	/// Close UART
	Close = 'iqrfEmbedUart_Close',
	/// Open UART
	Open = 'iqrfEmbedUart_Open',
	/// Read and optionally write data
	WriteRead = 'iqrfEmbedUart_WriteRead',
}

/**
 * Embedded FRC message types
 */
export enum EmbedFrcMessages {
	/// Get extra FRC response data that did not fit in buffers
	ExtraResult = 'iqrfEmbedFrc_ExtraResult',
	/// Send FRC request
	Send = 'iqrfEmbedFrc_Send',
	/// Send selective FRC request
	SendSelective = 'iqrfEmbedFrc_SendSelective',
	/// Set FRC parameters
	SetParams = 'iqrfEmbedFrc_SetParams',
}

/**
 * Standard BinaryOutput message types
 */
export enum StandardBinaryOutputMessages {
	/// Enumerate binary outputs
	Enumerate = 'iqrfBinaryOutput_Enumerate',
	/// Set binary outputs
	SetOutput = 'iqrfBinaryOutput_SetOutput',
}

/**
 * Standard DALI message types
 */
export enum StandardDaliMessages {
	/// Send DALI commands using FRC
	Frc = 'iqrfDali_Frc',
	/// Send DALI commands
	SendCommands = 'iqrfDali_SendCommands',
	/// Send DALI commands and receive answers asynchronously
	SendCommandsAsync = 'iqrfDali_SendCommandsAsync',
}

/**
 * Standard Light message types
 */
export enum StandardLightMessages {
	/// Decrement light power
	DecrementPower = 'iqrfLight_DecrementPower',
	/// Enumerate lights
	Enumerate = 'iqrfLight_Enumerate',
	/// Increment light power
	IncrementPower = 'iqrfLight_IncrementPower',
	/// Set light power
	SetPower = 'iqrfLight_SetPower',
}

/**
 * Standard Sensor message types
 */
export enum StandardSensorMessages {
	/// Enumerate sensors
	Enumerate = 'iqrfSensor_Enumerate',
	/// Read sensors using FRC
	Frc = 'iqrfSensor_Frc',
	/// Read sensors with types
	ReadSensorsWithTypes = 'iqrfSensor_ReadSensorsWithTypes',
}

/**
 * Sensor data message types
 */
export enum SensorDataMessages {
	/// Get config
	GetConfig = 'iqrfSensorData_GetConfig',
	/// Invoke worker
	Invoke = 'iqrfSensorData_Invoke',
	/// Set config
	SetConfig = 'iqrfSensorData_SetConfig',
	/// Start worker
	Start = 'iqrfSensorData_Start',
	/// Stop worker
	Stop = 'iqrfSensorData_Stop',
}

/**
 * Scheduler message types
 */
export enum SchedulerMessages {
	/// Add task
	AddTask = 'mngScheduler_AddTask',
	/// Edit task
	EditTask = 'mngScheduler_EditTask',
	/// Get task
	GetTask = 'mngScheduler_GetTask',
	/// List tasks
	ListTasks = 'mngScheduler_List',
	/// Remove all tasks
	RemoveAll = 'mngScheduler_RemoveAll',
	/// Remove task
	RemoveTask = 'mngScheduler_RemoveTask',
	/// Start task
	StartTask = 'mngScheduler_StartTask',
	/// Stop task
	StopTask = 'mngScheduler_StopTask',
}

/**
 * Daemon management message types
 */
export enum ManagementMessages {
	/// Daemon exit
	Exit = 'mngDaemon_Exit',
	/// Change mode
	Mode = 'mngDaemon_Mode',
	/// Reload coordinator
	ReloadCoordinator = 'mngDaemon_ReloadCoordinator',
	/// Update cache
	UpdateCache = 'mngDaemon_UpdateCache',
	/// Get version
	Version = 'mngDaemon_Version',
}

/**
 * IQMESH service message types
 */
export enum IqmeshServiceMessages {
	/// Autonetwork
	Autonetwork = 'iqmeshNetwork_AutoNetwork',
	/// Backup
	Backup = 'iqmeshNetwork_Backup',
	/// Local bonding
	BondNode = 'iqmeshNetwork_BondNodeLocal',
	/// Get or set DPA hops
	DpaHops = 'iqmeshNetwork_DpaHops',
	/// Get / Set DPA value
	DpaValue = 'iqmeshNetwork_DpaValue',
	/// Get or set FRC parameters
	FrcParams = 'iqmeshNetwork_FrcParams',
	/// Resolve internal duplicated addresses
	MaintenanceDuplicatedAddresses = 'iqmeshNetwork_MaintenanceDuplicatedAddresses',
	/// Test node responses for specified FRC response time
	MaintenanceFrcResponseTime = 'iqmeshNetwork_MaintenanceFrcResponseTime',
	/// Resolve inconsintent MIDs in coordinator memory
	MaintenanceInconsistentMids = 'iqmeshNetwork_MaintenanceInconsistentMIDsInCoord',
	/// Test RF for coordinator or all nodes
	MaintenanceTestRf = 'iqmeshNetwork_MaintenanceTestRF',
	/// Resolve unused prebonded nodes
	MaintenanceUselessPrebondedNodes = 'iqmeshNetwork_MaintenanceUselessPrebondedNodes',
	/// OTA upload
	OtaUpload = 'iqmeshNetwork_OtaUpload',
	/// Ping devices
	Ping = 'iqmeshNetwork_Ping',
	/// Read TR configuration
	ReadTrConfig = 'iqmeshNetwork_ReadTrConfig',
	/// Unbond device(s) from network
	RemoveBond = 'iqmeshNetwork_RemoveBond',
	/// Unbond device(s) in coordinator memory
	RemoveBondCoordinator = 'iqmeshNetwork_RemoveBondOnlyInC',
	/// Restart devices
	Restart = 'iqmeshNetwork_Restart',
	/// Restore
	Restore = 'iqmeshNetwork_Restore',
	/// Smart connect
	SmartConnect = 'iqmeshNetwork_SmartConnect',
	/// Write TR configuration
	WriteTrConfig = 'iqmeshNetwork_WriteTrConfig',
}

/**
 * Daemon notification message types
 */
export enum NotificationMessages {
	/// Monitor worker invocation
	InvokeMonitor = 'ntfDaemon_InvokeMonitor',
	/// Monitor notification (response only)
	Monitor = 'ntfDaemon_Monitor',
}
