import { type ChannelState, type DaemonMode } from '../enums';

/**
 * Monitor message data
 */
export interface MonitorData {
	/// DPA channel state
	dpaChannelState: ChannelState;
	/// DPA queue length
	dpaQueueLen: number;
	/// IQRF channel state
	iqrfChannelState: ChannelState;
	/// Message queue length
	msgQueueLen: number;
	/// Message number
	num: number;
	/// Mode of operation
	operMode: DaemonMode;
	/// Timestamp
	timestamp: number;
}

/**
 * Daemon monitor message
 */
export interface MonitorMessage {
	/// Message data
	data: MonitorData;
	/// Message type
	mType: string;
}
