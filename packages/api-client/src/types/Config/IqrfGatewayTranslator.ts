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

export interface IqrfGatewayTranslatorRestConfig {

	/**
	 * IQRF Gateway Webapp API key
	 */
	api_key: string;

	/**
	 * IQRF Gateway Webapp address
	 */
	addr: string;

	/**
	 * IQRF Gateway Webapp port
	 */
	port: number;

}

export interface IqrfGatewayTranslatorMqttConfig {

	/**
	 * MQTT client ID
	 */
	cid: string;

	/**
	 * MQTT broker address
	 */
	addr: string;

	/**
	 * MQTT broker port
	 */
	port: number;

	/**
	 * MQTT broker username
	 */
	user: string;

	/**
	 * MQTT broker password
	 */
	pw: string;

	/**
	 * MQTT request topic
	 */
	request_topic: string;

	/**
	 * MQTT response topic
	 */
	response_topic: string;

}

/**
 * IQRF Gateway Translator configuration
 */
export interface IqrfGatewayTranslatorConfig {

	/**
	 * IQRF Gateway Translator REST API configuration
	 */
	rest: IqrfGatewayTranslatorRestConfig;

	/**
	 * IQRF Gateway Translator MQTT configuration
	 */
	mqtt: IqrfGatewayTranslatorMqttConfig;

}
