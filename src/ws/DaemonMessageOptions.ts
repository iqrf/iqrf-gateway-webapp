/**
 * Daemon request options class
 */
class DaemonMessageOptions {
	/**
	 * Request object
	 */
	public request: Record<string, any>|null;

	/**
	 * Request timeout
	 */
	public timeout: number|null;

	/**
	 * Request timeout toast message
	 */
	public message: string|null;

	/**
	 * Request timeout callback
	 */
	public callback: CallableFunction;

	/**
	 * Constructor
	 * @param {Record<string, any>|null} request Request object
	 * @param {number|null} timeout Request timeout
	 * @param {string|null} message Request timeout toast message
	 * @param {CallableFunction} callback Request timeout callback
	 */
	constructor(request: Record<string, any>|null, timeout: number|null = null, message: string|null = null, callback: CallableFunction = () => {return;}) {
		this.request = request;
		this.timeout = timeout;
		this.message = message;
		this.callback = callback;
	}
}

export default DaemonMessageOptions;