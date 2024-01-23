/**
 * IQMESH SensorData service API commands
 */
export enum SensorDataServiceCommands {
	/// Get service configuration
	GetConfig = 'getConfig',
	/// Invoke service worker
	Invoke = 'now',
	/// Start service worker
	Start = 'start',
	/// Stop service worker
	Stop = 'stop',
	/// Set service configuration
	SetConfig = 'setConfig',
}
