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
 * IQRF GW Translator configuration interface
 */
export interface ITranslator {
	/**
	 * Translator MQTT configuration
	 */
	mqtt: ITranslatorMqtt

	/**
	 * Translator REST configuraiton
	 */
	rest: ITranslatorRest
}

/**
 * Translator MQTT configuration interface
 */
interface ITranslatorMqtt {
	/**
	 * MQTT broker address
	 */
	addr: string

	/**
	 * Client ID
	 */
	cid: string

	/**
	 * MQTT port
	 */
	port: number

	/**
	 * User password
	 */
	pw: string

	/**
	 * MQTT request topic
	 */
	request_topic: string

	/**
	 * MQTT response topic
	 */
	response_topic: string

	/**
	 * User name
	 */
	user: string

	/**
	 * TLS configuration
	 */
	tls: ITranslatorTls
}

/**
 * Translator MQTT TLS configuration interface
 */
interface ITranslatorTls {
	/**
	 * TLS enabled
	 */
	enabled: boolean

	/**
	 * Server certificate
	 */
	trust_store: string

	/**
	 * Client certificate
	 */
	key_store: string

	/**
	 * Client private key
	 */
	private_key: string

	/**
	 * Require broker certificate
	 */
	require_broker_certificate: boolean
}

/**
 * Translator REST configuration interface
 */
interface ITranslatorRest {
	/**
	 * REST API address
	 */
	addr: string

	/**
	 * API key
	 */
	api_key: string

	/**
	 * REST API port
	 */
	port: number
}
