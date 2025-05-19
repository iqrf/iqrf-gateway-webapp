/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
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
