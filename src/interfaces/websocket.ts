import {RequiredInterface} from './requiredInterfaces';

/**
 * WebsocketInterface interface
 */
export interface WsInterface {
	instanceMessaging: string
	instanceService: string
	acceptAsyncMsg: boolean
	port: number
	acceptOnlyLocalhost: boolean
	service: WsService
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
 * WebsocketService interface
 */
export interface WsService {
	WebsocketPort: number
	acceptOnlyLocalhost: boolean
	component: string
	instance: string
}
