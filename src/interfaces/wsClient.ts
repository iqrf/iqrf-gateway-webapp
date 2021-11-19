import {MenderActions} from '../enums/Maintenance/Mender';

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
	 * Number of received messages
	 */
	receivedMessages: number;
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
