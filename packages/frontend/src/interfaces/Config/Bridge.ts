/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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
 * InfluxDB Bridge configuration interface
 */
export interface BridgeConfiguration {
	/// InfluxDB client configuration
	influx: BridgeConfigurationInflux
	/// MQTT client configuration
	mqtt: BridgeConfigurationMqtt
	/// Logging level
	logLevel: string
}

/**
 * Bridge Influx configuration interface
 */
export interface BridgeConfigurationInflux {
	/// Host
	host: string
	/// Port
	port: number
	/// Organization
	org: string
	/// Username (v1.x auth)
	user: string
	/// Password (v1.x auth)
	password: string
	/// Token (v2+ auth)
	token: string
	/// Buckets
	buckets: BridgeConfigurationInfluxBuckets
}

/**
 * Bridge Influx buckets configuration interface
 */
export interface BridgeConfigurationInfluxBuckets {
	/// Gateway bucket name
	gateway: string
	/// Devices bucket name
	devices: string
	/// Sensor bucket name
	sensors: string
}

/**
 * Bridge MQTT configuration interface
 */
export interface BridgeConfigurationMqtt {
	/// MQTT client ID
	client: string
	/// MQTT host
	host: string
	/// MQTT port
	port: number
	/// Username
	user: string
	/// Password
	password: string
	/// Topics to subscribe to
	topics: string[]
}
