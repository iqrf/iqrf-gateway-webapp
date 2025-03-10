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

/**
 * System service state
 */
export interface ServiceState {
	/// Is the service active?
	active: boolean | null;
	/// Is the service enabled?
	enabled: boolean | null;
	/// Service name
	name: string;
	/// Service status
	status: string | null;
}

/**
 * System service status
 */
export interface ServiceStatus {
	/// Is the service active?
	active?: boolean;
	/// Is the service enabled?
	enabled?: boolean;
	/// Service status
	status: string;
}
