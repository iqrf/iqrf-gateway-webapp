/**
 * Daemon API message class
 */
class DaemonMessage {
	/**
	 * Message ID
	 */
	public msgId: string;

	/**
	 * Request timeout
	 */
	public timeout: number|undefined;

	/**
	 * Constructor
	 * @param {string} msgId Message ID
	 * @param {number|null} timeout Request timeout
	 */
	constructor(msgId: string, timeout: number|null = null) {
		this.msgId = msgId;
		this.timeout = timeout ?? undefined;
	}
}

export default DaemonMessage;
