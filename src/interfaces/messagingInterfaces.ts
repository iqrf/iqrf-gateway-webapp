import {RequiredInterface} from './requiredInterfaces';

/**
 * MqMessaging instance interface
 */
export interface IMqInstance {
	acceptAsyncMsg: boolean
	component: string
	instance: string
	LocalMqName: string
	RemoteMqName: string
}

/**
 * MqttMessaging instance interface
 */
export interface IMqttInstance {
	acceptAsyncMsg: boolean
	component: string
	instance: string
	BrokerAddr: string
	ClientId: string
	ConnectTimeout: number
	EnableServerCertAuth: boolean
	EnabledCipherSuites: string
	EnabledSSL: boolean
	KeepAliveInterval: number
	KeyStore: string
	MaxReconnect: number
	MinReconnect: number
	Password: string
	Persistence: number
	PrivateKey: string
	PrivateKeyPassword: string
	Qos: number
	TopicRequest: string
	TopicResponse: string
	TrustStore: string
	User: string
}

/**
 * WebsocketInterface interface
 */
export interface WsInterface {
	instanceMessaging: string
	instanceService: string
	acceptAsyncMsg: boolean
	port: number
	acceptOnlyLocalhost: boolean
	service: IWsService
	messaging: WsMessaging
}

/**
 * WebsocketMessaging interface
 */
export interface WsMessaging {
	acceptAsyncMsg: boolean
	component: string
	instance: string
	RequiredInterfaces: Array<RequiredInterface>
}

/**
 * Messaging instance interface for modal windows
 */
export interface ModalInstance {
	messaging: string
	service: string
}

/**
 * WebsocketService interface
 */
export interface IWsService {
	/**
	 * Component name
	 */
	component: string

	/**
	 * Component instance name
	 */
	instance: string

	/**
	 * Websocket port
	 */
	WebsocketPort: number

	/**
	 * Accept connections only from localhost?
	 */
	acceptOnlyLocalhost: boolean

	/**
	 * Use TLS?
	 */
	tlsEnabled?: boolean

	/**
	 * TLS operating mode
	 */
	tlsMode?: string

	/**
	 * Path to certificate for TLS
	 */
	certificate?: string

	/**
	 * Path to private key for TLS
	 */
	privateKey?: string
}

/**
 * UdpMessaging instance interface
 */
export interface UdpInstance {
	component: string
	instance: string
	LocalPort: number
	RemotePort: number
}