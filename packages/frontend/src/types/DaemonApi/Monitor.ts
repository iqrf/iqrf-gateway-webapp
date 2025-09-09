import {
	type ChannelState,
	type DaemonMode,
} from '@iqrf/iqrf-gateway-daemon-utils/enums';

/**
 * Monitor notification message data interface
 */
export interface MonitorData {
	/**
	 * Device data reading in progress
	 */
	dataReadingInProgress: boolean;
	/**
	 * DPA channel state
	 */
	dpaChannelState: ChannelState;
	/**
	 * DPA queue length
	 */
	dpaQueueLen: number;
	/**
	 * Network enumeration in progress
	 */
	enumInProgress: boolean;
	/**
	 * IQRF channel state
	 */
	iqrfChannelState: ChannelState;
	/**
	 * Network queue length
	 */
	msgQueueLen: number;
	/**
	 * Message number
	 */
	num: number;
	/**
	 * Mode of operation
	 */
	operMode: DaemonMode;
	/**
	 * Timestamp
	 */
	timestamp: number;
}
