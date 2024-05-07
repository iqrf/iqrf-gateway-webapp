/**
 * IQMESH Autonetwork MID list entry interface
 */
export interface AutonetworkMidList {
	/// address to assign to device
	deviceAddr?: number;
	/// Device MID to bond
	deviceMID: string;
}

/**
 * IQMESH Autonework overlapping networks interface
 */
export interface AutonetworkOverlappingNetworks {
	/// Number of network that will be built
	network: number;
	/// Number of total networks to be built
	networks: number;
}

/**
 * IQMESH Autonetwork stop conditions interface
 */
export interface AutonetworkStopConditions {
	/// Too many nodes found
	abortOnTooManyNodesFound?: boolean;
	/// Number of consecutive empty waves
	emptyWaves: number;
	/// Number of new nodes in network
	numberOfNewNodes?: number;
	/// Total number of nodes in network
	numberOfTotalNodes?: number;
	/// Maximum number of waves
	waves?: number;
}

/**
 * IQMESH Autonetwork request parameters interface
 */
export interface IqmeshAutonetworkParams {
	/// Number of DPA retry transactions
	actionRetries?: number;
	/// Addresses to use
	addressSpace: number[];
	/// Run discovery before starting autonetwork process
	discoveryBeforeStart?: boolean;
	/// TX power to use in discovery process
	discoveryTxPower?: number;
	/// List of HWPID filters
	hwpidFiltering: number[];
	/// Only bond devices specified in MID list
	midFiltering?: boolean
	/// MID list
	midList?: AutonetworkMidList[]
	/// Skip discovery in each wave
	skipDiscoveryEachWave?: boolean;
	/// Skip SmartConnect prebonding
	skipPrebonding?: boolean;
	/// Stop conditions
	stopConditions: AutonetworkStopConditions;
	/// Unbond nodes that do not respond after prebonding
	unbondUnrespondingNodes?: boolean;
}

/**
 * IQMESH Local bonding request parameters
 */
export interface IqmeshBondNodeParams {
	/// Bonding mask
	bondingMask?: number;
	/// Maximum number of FRC bond test requests
	bondingTestRetries?: number;
	/// Device address
	deviceAddr: number;
}

/**
 * IQMESH SmartConnect parameters interface
 */
export interface IqmeshSmartConnectParams {
	/// Maximum number of FRC bond test requests
	bondingTestRetries?: number;
	/// Device address
	deviceAddr: number;
	/// Smart connect code
	smartConnectCode: string;
	/// Optional data passed to node
	userData?: number[];
}

/**
 * IQMESH RemoveBond parameters interface
 */
export interface IqmeshRemoveBondParams {
	/// Device address
	deviceAddr: number;
	/// HWPID filter
	hwpId?: number;
	/// Unbond all devices in network
	wholeNetwork?: boolean;
}

/**
 * IQMESH RemoveBondCoordinator parameters interface
 */
export interface IqmeshRemoveBondCoordinatorParams {
	/// Clear all bonds
	clearAllBonds?: boolean;
	/// List of device addresses
	deviceAddr: number[];
}

/**
 * IQMESH RemoveBond parameters interface
 */
export interface IqmeshRemoveBond3Params {
	/// Unbond all nodes (overrides deviceAddr)
	allNodes: boolean;
	/// Unbond in coordinator memory only
	coordinatorOnly: boolean;
	/// Device address or array of device addresses
	deviceAddr: number | number[];
	/// HWPID filter
	hwpId: number;
}
