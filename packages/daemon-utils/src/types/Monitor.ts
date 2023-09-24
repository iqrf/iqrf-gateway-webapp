import type {DaemonMode, ChannelState} from '../enums/Monitor';

/**
 * Monitor message data
 */
export interface MonitorData {
	/**
	 * Message number
	 */
	num: number;

	/**
	 * Timestamp
	 */
	timestamp: number;

	/**
	 * DPA queue length
	 */
	dpaQueueLen: number;

	/**
	 * IQRF channel state
	 */
	iqrfChannelState: ChannelState;

	/**
	 * DPA channel state
	 */
	dpaChannelState: ChannelState;

	/**
	 * Message queue length
	 */
	msgQueueLen: number;

	/**
	 * Mode of operation
	 */
	operMode: DaemonMode;
}

/**
 * Daemon monitor message
 */
export interface MonitorMessage {
	mType: string;
	data: MonitorData
}
