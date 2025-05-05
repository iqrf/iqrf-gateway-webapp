/**
 * DPA packet transaction
 */
export interface DpaPacketTransaction {
	/**
	 * Confirmation message
	 */
	confirmation?: string;
	/**
	 * Confirmation timestamp
	 */
	confirmationTs?: string;
	/**
	 * Message ID
	 */
	msgId: string;
	/**
	 * Request message
	 */
	request: string;
	/**
	 * Request timestamp
	 */
	requestTs: string;
	/**
	 * Response message
	 */
	response?: string;
	/**
	 * Response timestamp
	 */
	responseTs?: string;
}


/**
 * JSON API transaction
 */
export interface JsonApiTransaction {
	/**
	 * Message type
	 */
	mType: string;
	/**
	 * Message ID
	 */
	msgId: string;
	/**
	 * Request message
	 */
	request: string;
	/**
	 * List of response messages
	 */
	response: string[];
	/**
	 * Request timestamp
	 */
	timestamp: string;
}
