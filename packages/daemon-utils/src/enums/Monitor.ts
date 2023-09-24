/**
 * Daemon Mode enum
 */
export enum DaemonMode {
	Unknown = 'unknown',
	Operational = 'operational',
	Forwarding = 'forwarding',
	Service = 'service'
}

/**
 * Channel state enum
 */
export enum ChannelState {
	Ready = 'Ready',
	NotReady = 'NotReady',
	ExclusiveAccess = 'ExclusiveAccess',
	Unknown = 'unknown'
}
