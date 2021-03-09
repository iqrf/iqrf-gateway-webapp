import {DaemonModeEnum} from '../services/DaemonModeService';

import {RequiredInterface} from './requiredInterfaces';

/**
 * Daemon IDE countepart component configuration interface
 */
export interface IIdeCounterpart {
	/**
	 * Component name
	 */
	component: string

	/**
	 * Component instance
	 */
	instance: string

	/**
	 * Gateway identification mode byte
	 */
	gwIdentModeByte: number

	/**
	 * Gateway identification name
	 */
	gwIdentName: string

	/**
	 * Gateway identification ip stack 
	 */
	gwIdentIpStack: string

	/**
	 * Gateway identification net bios
	 */
	gwIdentNetBios: string

	/**
	 * Gateway identification public ip
	 */
	gwIdentPublicIp: string

	/**
	 * Daemon startup mode
	 */
	operMode?: DaemonModeEnum

	/**
	 * Component required interfaces
	 */
	RequiredInterfaces: Array<RequiredInterface>
}
