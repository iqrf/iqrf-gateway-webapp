import { type DaemonApiRequest } from '../types';

/**
 * Daemon API message class
 */
export class DaemonMessage {
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
	public constructor(msgId: string, timeout: number|null = null) {
		this.msgId = msgId;
		this.timeout = timeout ?? undefined;
	}
}

/**
 * Daemon request options class
 */
export class DaemonMessageOptions {
	/**
	 * Request object
	 */
	public request: DaemonApiRequest | null;

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
	 * @param {DaemonApiRequest | null} request Request object
	 * @param {number | null} timeout Request timeout
	 * @param {string | null} message Request timeout toast message
	 * @param {CallableFunction} callback Request timeout callback
	 */
	public constructor(request: DaemonApiRequest|null, timeout: number|null = null, message: string|null = null, callback: CallableFunction = (): void => { }) {
		this.request = request;
		this.timeout = timeout;
		this.message = message;
		this.callback = callback;
	}
}
