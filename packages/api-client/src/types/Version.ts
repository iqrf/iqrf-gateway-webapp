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
 * IQRF Gateway software versions
 */
export interface Versions {
	/// IQRF Cloud Provisioning version
	'iqrf-cloud-provisioning': string | null;
	/// IQRF Gateway Controller version
	'iqrf-gateway-controller': string | null;
	/// IQRF Gateway Daemon version
	'iqrf-gateway-daemon': string;
	/// IQRF Gateway MQTT-InfluxDB Bridge version
	'iqrf-gateway-influxdb-bridge': string | null;
	/// IQRF Gateway Setter version
	'iqrf-gateway-setter': string | null;
	/// IQRF Gateway Uploader version
	'iqrf-gateway-uploader': string | null;
	/// IQRF Gateway Webapp version
	'iqrf-gateway-webapp': string;
	/// Mender Client version
	'mender-client': string | null;
	/// Mender Connect version
	'mender-connect': string | null;
}

/**
 * Version
 */
export interface VersionBase {
	/// Version
	version: string;
}

/**
 * Version of IQRF Gateway Daemon
 */
export type VersionIqrfGatewayDaemon = VersionBase;

/**
 * Version of IQRF Gateway Webapp
 */
export interface VersionIqrfGatewayWebapp extends VersionBase {
	/// Git commit hash
	commit: string;
	/// GitLab pipeline ID
	pipeline: string;
}
