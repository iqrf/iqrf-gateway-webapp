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
