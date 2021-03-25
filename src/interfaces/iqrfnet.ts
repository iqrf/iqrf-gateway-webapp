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

export interface IMessagePairPacket extends IMessagePair {
	/**
	 * Response string
	 */
	response: string|undefined
}

export interface IMessagePairRequest extends IMessagePair {
	/**
	 * Array of responses
	 */
	response: Array<string>
