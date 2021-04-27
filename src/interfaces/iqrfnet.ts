/**
 * Message pair interface
 */
interface IMessagePair {
	/**
	 * Message ID
	 */
	msgId: string
	
	/**
	 * Request string
	 */
	request: string

	/**
	 * Label for history
	 */
	label: string
}

/**
 * DPA packet message pair interface
 */
export interface IMessagePairPacket extends IMessagePair {
	/**
	 * Response string
	 */
	response: string|undefined
}

/**
 * JSON request message pair interface
 */
export interface IMessagePairRequest extends IMessagePair {
	/**
	 * Array of responses
	 */
	response: Array<string>
}
