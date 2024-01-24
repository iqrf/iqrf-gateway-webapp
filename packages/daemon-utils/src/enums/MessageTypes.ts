/**
 * Generic messages
 */
export enum GenericMessages {
	/// Raw DPA message
	Raw = 'iqrfRaw'
}

/**
 * Scheduler message types
 */
export enum SchedulerMessages {
	/// List tasks
	ListTasks = 'mngScheduler_List',
	/// Get task
	GetTask = 'mngScheduler_GetTask',
	/// Add task
	AddTask = 'mngScheduler_AddTask',
	/// Edit task
	EditTask = 'mngScheduler_EditTask',
	/// Remove task
	RemoveTask = 'mngScheduler_RemoveTask',
	/// Remove all tasks
	RemoveAll = 'mngScheduler_RemoveAll',
	/// Start task
	StartTask = 'mngScheduler_StartTask',
	/// Stop task
	StopTask = 'mngScheduler_StopTask'
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
	/// Unbond device(s) from network
	RemoveBond = 'iqmeshNetwork_RemoveBond',
	/// Unbond device(s) in coordinator memory
	RemoveBondCoordinator = 'iqmeshNetwork_RemoveBondOnlyInC',
	/// Manager SensorData service
	SensorData = 'iqmeshNetwork_SensorData',
}
