/**
 * Copyright 2023-2025 MICRORISC s.r.o.
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

import { type DateTime, type Duration } from 'luxon';

/**
 * Power action response
 * @internal
 */
export interface PowerActionResponseRaw {
	/// Restart timestamp
	timestamp: number
}

/**
 * Power action response
 */
export interface PowerActionResponse {
	/// Restart timestamp
	timestamp: DateTime
}

/**
 * Gateway uptime - raw response
 * @internal
 */
export interface GatewayUptimeRaw {
	/// Downtime in seconds
	downtime: number,
	/// Was the shutdown graceful?
	graceful: boolean,
	/// ID
	id: number,
	/// Kernel version
	kernel: string,
	/// Run time in seconds
	running: number,
	/// Shutdown time
	shutdown: string | null,
	/// Sleep time in seconds
	sleeping: number,
	/// Start time
	start: string,
}


/**
 * Gateway uptime
 */
export interface GatewayUptime {
	/// Downtime in seconds
	downtime: Duration,
	/// Was the shutdown graceful?
	graceful: boolean,
	/// ID
	id: number,
	/// Kernel version
	kernel: string,
	/// Run time in seconds
	running: Duration,
	/// Shutdown time
	shutdown: DateTime | null,
	/// Sleep time in seconds
	sleeping: Duration,
	/// Start time
	start: DateTime
}
