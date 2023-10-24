
/**
 * Daemon management message types
 */
export enum ManagementMessages {
	Exit = 'mngDaemon_Exit',
	Mode = 'mngDaemon_Mode',
	ReloadCoordinator = 'mngDaemon_ReloadCoordinator',
	UpdateCache = 'mngDaemon_UpdateCache',
	Version = 'mngDaemon_Version',
}
