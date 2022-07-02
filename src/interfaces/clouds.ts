/**
 * Copyright 2017-2022 IQRF Tech s.r.o.
 * Copyright 2019-2022 MICRORISC s.r.o.
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
 * Hexio IoT Platform interface
 */
export interface IHexioCloud {
	/**
	 * Hexio cloud host
	 */
	broker: string

	/**
	 * Client ID
	 */
	clientId: string

	/**
	 * MQTT request topic
	 */
	topicRequest: string

	/**
	 * MQTT response topic
	 */
	topicResponse: string

	/**
	 * User name
	 */
	username: string

	/**
	 * User password
	 */
	password: string
}

/**
 * Ibm Cloud interface
 */
export interface IIbmCloud {
	/**
	 * Organization ID
	 */
	organizationId: string

	/**
	 * Device type
	 */
	deviceType: string

	/**
	 * Device ID
	 */
	deviceId: string

	/**
	 * Authentication token
	 */
	token: string

	/**
	 * Command and event ID
	 */
	eventId: string
}

/**
 *
 */
export interface IInteliGlueCloud {
	/**
	 * MQTT topic
	 */
	rootTopic: string

	/**
	 * Post number
	 */
	assignedPort: number

	/**
	 * Client ID
	 */
	clientId: string

	/**
	 * Client password
	 */
	password: string
}
