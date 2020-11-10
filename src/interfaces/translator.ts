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
	topic: string

	/**
	 * User name
	 */
	user: string
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
