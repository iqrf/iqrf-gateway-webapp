/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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

import {EapPhaseOneMethod, EapPhaseTwoMethod} from '@/enums/Network/WifiSecurity';

export interface IAccessPoints {
	ssid: string,
	aps: Array<IAccessPoint>
	showDetails?: boolean
}

export interface IAccessPoint {
	inUse: boolean
	bssid: string
	ssid: string
	mode: string
	channel: number
	rate: string
	signal: number
	security: string
	uuid?: string
	interfaceName: string|null
}

export interface IWifiSecurity {
	type: string
	psk: string
	leap: IWifiLeap
	wep: IWifiWep
	eap: IWifiEap
}

/**
 * EAP (Extensible Authentication Protocol) interface
 */
export interface IWifiEap {
	phaseOneMethod: EapPhaseOneMethod|null
	phaseTwoMethod: EapPhaseTwoMethod|null
	anonymousIdentity: string
	cert: string
	identity: string
	password: string
}

/**
 * Cisco LEAP security interface
 */
export interface IWifiLeap {
	username: string
	password: string
}

/**
 * WEP security interface
 */
export interface IWifiWep {
	type: string
	index: number
	keys: Array<string>
}
