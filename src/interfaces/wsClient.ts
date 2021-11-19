import {MenderActions} from '../enums/Maintenance/Mender';
import DaemonMessage from '../ws/DaemonMessage';

export interface GenericClientState {
	/**
	 * Is client connecting?
	 */
	isConnected: boolean;

	/**
	 * Is client reconnecting?
	 */
	reconnecting: boolean;

	/**
	 * Has client reconnected?
	 */
	hasReconnected: boolean;

	/**
	 * Number of received messages
	 */
	receivedMessages: number;
}

export interface DaemonClientState extends GenericClientState {
	/**
	 * Sent requests
	 */
	requests: Record<string, any>;

	/**
	 * Received responses
	 */
	responses: Record<string, any>;

	/**
	 * Message objects
	 */
	messages: Array<DaemonMessage>

	/**
	 * IQRF Gateway Daemon version
	 */
	version: string;

	/**
	 * Daemon API message ID for version request
	 */
	versionMsgId: string;
}
export interface MenderClientState extends GenericClientState {
	/**
	 * Last invoked Mender client action
	 */
	action: MenderActions|null;

	/**
	 * Indicates whether action is in progress
	 */
	inProgress: boolean;
}

export interface MonitorClientState extends GenericClientState {
	/**
	 * Current daemon mode
	 */
	mode: string

	/**
	 * Modal window state
	 */
	modal: boolean;

	/**
	 * Daemon queue length
	 */
	queueLen: number|string;
}
