/**
 * Standard DALI SendCommands parameters interface
 */
export interface SendDaliCommandsParams {
	/// DALI commands
	commands: number[];
}

/**
 * Standard DALI FRC parameters interface
 */
export interface SendDaliFrcCommandParams {
	/// DALI command
	command: number;
	/// Returns extended / verbose format
	extFormat?: boolean;
	/// Get extra FRC data that did not fit in response
	getExtraResult?: boolean;
	/// Selected nodes
	selectedNodes?: number[];
}
