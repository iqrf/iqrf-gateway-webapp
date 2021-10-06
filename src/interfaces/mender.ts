import {MenderMessageTypes} from '../enums/Maintenance/Mender';

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
