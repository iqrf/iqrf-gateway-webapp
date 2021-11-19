import {MenderActions, MenderMessageTypes} from '../enums/Maintenance/Mender';

/**
 * Mender client vuex store state interface
 */
export interface MenderClientState {
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

	/**
	 * Last invoked Mender client action
	 */
	action: MenderActions|null;

	/**
	 * Indicates whether action is in progress
	 */
	inProgress: boolean;
}

/**
 * Mender message interface
 */
export interface MenderMessage {

	/**
	 * Type of Mender message
	 */
	messageType: MenderMessageTypes;

	/**
	 * Message payload
	 */
	payload: string;
}
