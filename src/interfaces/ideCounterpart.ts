/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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

import {DaemonModeEnum} from '@/enums/Gateway/DaemonMode';
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
