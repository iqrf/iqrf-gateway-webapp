import { type IndicateControl } from '../../enums';

/**
 * Embedded OS Batch request interface
 */
export interface BatchRequest {
	/// HWPID filter
	hwpid: number;
	/// Peripheral command
	pcmd: number;
	/// Peripheral number
	pnum: number;
	/// Request data
	rdata?: number[];
}

export interface BatchRequestRaw {
	/// HWPID filter
	hwpid: string;
	/// Peripheral command
	pcmd: string;
	/// Peripheral number
	pnum: string;
	/// Request data
	rdata?: string;
}

/**
 * Embedded OS Batch parameters interface
 */
export interface BatchParams {
	/// Requests to execute
	requests: BatchRequest[];
}

/**
 * Embedded OS Batch raw parameters interface
 */
export interface BatchParamsRaw {
	/// Converted requests to execute
	requests: BatchRequestRaw[];
}

/**
 * Embedded OS TR configuration parameters interface
 */
export interface EmbedTrConfigParams {
	/// Checksum
	checksum: number;
	/// Configuration block
	configuration: number[];
	/// RFPGM
	rfpgm: number;
}

/**
 * Embedded OS TR configuration byte parameters interface
 */
export interface TrConfigByte {
	/// Address of TR config memory block
	address: number;
	/// Configuration item mask
	mask: number;
	/// Value to write
	value: number;
}

/**
 * Embedded OS TR configuration bytes parameters interface
 */
export interface EmbedTrConfigByteParams {
	/// Configuration bytes
	bytes: TrConfigByte[];
}

/**
 * Embedded OS Indicate parameters interface
 */
export interface IndicateParams {
	/// Indicate control
	control: IndicateControl;
}

/**
 * Embedded OS Load Code parameters interface
 */
export interface LoadCodeParams {
	/// EEEPROM address to store data at
	address: number;
	/// Checksum
	checkSum: number;
	/// Action flags
	flags: number;
	/// Length of data to store
	length: number;
}

/**
 * Embedded OS Set Security parameters interface
 */
export interface SetSecurityParams {
	/// Password or user key
	data: number[];
	/// Security type
	type: number;
}

/**
 * Embedded OS Selective Batch parameters interface
 */
export interface SelectiveBatchParams extends BatchParams {
	/// Node addresses to execute request batch
	selectedNodes: number[];
}

/**
 * Embedded OS Selective Batch raw parameters interface
 */
export interface SelectiveBatchParamsRaw extends BatchParamsRaw {
	/// Node addresses to execute request batch
	selectedNodes: number[];
}

/**
 * Embedded OS Sleep parameters interface
 */
export interface SleepParams {
	/// Control parameters
	control: number;
	/// Sleep time
	time: number;
}

/**
 * Embedded OS Test RF Signal parameters interface
 */
export interface TestRfSignalParams {
	/// Channel
	channel: number;
	/// RX filter
	rxFilter: number;
	/// Time in 10ms unit
	time: number;
}
