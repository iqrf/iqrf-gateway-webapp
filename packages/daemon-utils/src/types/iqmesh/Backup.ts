/**
 * IQMESH Backup parameters interface
 */
export interface IqmeshBackupParams {
	/// Device address
	deviceAddr: number;
	/// Backup the entire network
	wholeNetwork?: boolean;
}

/**
 * IQMESH Restore parameters interface
 */
export interface IqmeshRestoreParams {
	/// Backup data
	data: string;
	/// Device address
	deviceAddr: number;
	/// Restart coordinator after restore
	restartCoordinator?: boolean;
}
