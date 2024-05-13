/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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
 * InfluxDB Bridge buckets configuration interface
 */
export interface BridgeConfigBuckets {
	/// Devices bucket name
	devices: string;
	/// Gateway bucket name
	gateway: string;
	/// Sensor bucket name
	sensors: string;
}

/**
 * InfluxDB Bridge configuration interface
 */
export interface BridgeConfigInflux {
	/// Buckets
	buckets: BridgeConfigBuckets;
	/// Host
	host: string;
	/// Organization
	org: string;
	/// Password (v1.x auth)
	password: string;
	/// Port
	port: number;
	/// Token (v2+ auth)
	token: string;
	/// Username (v1.x auth)
	user: string;
}

/**
 * InfluxDB Bridge MQTT configuration interface
 */
export interface BridgeConfigMqtt {
	/// MQTT client ID
	client: string;
	/// MQTT host
	host: string;
	/// Password
	password: string;
	/// MQTT port
	port: number;
	/// Topics to subscribe to
	topics: string[];
	/// Username
	user: string;
}

/**
 * InfluxDB Bridge configuration interface
 */
export interface BridgeConfig {
	/// InfluxDB client configuration
	influx: BridgeConfigInflux;
	/// Logging level
	logLevel: string;
	/// MQTT client configuration
	mqtt: BridgeConfigMqtt;
}
