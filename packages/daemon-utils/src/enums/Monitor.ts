/**
 * Daemon Mode enum
 */
export enum DaemonMode {
	/// Forwarding mode
	Forwarding = 'forwarding',
	/// Operational mode
	Operational = 'operational',
	/// Service mode
	Service = 'service',
	/// Unknown mode (used before Daemon components are fully initialized)
	Unknown = 'unknown',
}

/**
 * Channel state enum
 */
export enum ChannelState {
	/// Exclusive access assigned
	ExclusiveAccess = 'ExclusiveAccess',
	/// Not ready
	NotReady = 'NotReady',
	/// Ready
	Ready = 'Ready',
	/// Unknown mode (used before Daemon components are fully initialized)
	Unknown = 'unknown',
}
