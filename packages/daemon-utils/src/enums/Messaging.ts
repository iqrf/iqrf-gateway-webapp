/**
 * Messaging types
 */
export enum MessagingType {
	/// Buffered MQTT
	BufferedMqtt = 'bmqtt',
	/// Message Queue
	Mq = 'mq',
	/// MQTT
	Mqtt = 'mqtt',
	/// Scheduler
	Scheduler = 'scheduler',
	/// UDP
	Udp = 'udp',
	/// WebSocket
	Websocket = 'ws',
}
