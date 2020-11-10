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
