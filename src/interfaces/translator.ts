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
