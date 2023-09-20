class Timer {

	/**
	 * Timer interval
	 */
	private intervalId: number;

	/**
	 * Start time
	 */
	private startTime: number;

	/**
	 * Time elapsed
	 */
	private time: number;

	/**
	 * Base constructor
	 */
	constructor() {
		this.intervalId = 0;
		this.startTime = 0;
		this.time = 0;
	}

	/**
	 * Starts the timer
	 */
	start(): void {
		this.startTime = Date.now();
		this.intervalId = window.setInterval(() => {
			this.time = Date.now() - this.startTime;
		}, 400);
	}

	/**
	 * Stops the timer
	 */
	stop(): void {
		window.clearInterval(this.intervalId);
	}

	/**
	 * Returns elapsed time in milliseconds
	 * @returns Elapsed time in milliseconds
	 */
	getTime(): number {
		return this.time;
	}
}

export default Timer;
