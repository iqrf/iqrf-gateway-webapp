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
 * WEP key length
 */
export enum WepKeyLen {
	BIT64 = '64bit',
	BIT128 = '128bit',
}

/**
 * WEP key type
 */
export enum WepKeyType {
	KEY = 'key',
	PASSPHRASE = 'passphrase',
	UNKNOWN = 'unknown',
}

/**
 * EAP (Extensible Authentication Protocol) phase one authentication method enum
 */
export enum EapPhaseOneMethod {
	FAST = 'fast',
	LEAP = 'leap',
	MD5 = 'md5',
	PEAP = 'peap',
	PWD = 'pwd',
	TLS = 'tls',
	TTLS = 'ttls',
}

/**
 * EAP (Extensible Authentication Protocol) phase two authentication method enum
 */
export enum EapPhaseTwoMethod {
	GTC = 'gtc',
	MD5 = 'md5',
	MSCHAPV2 = 'mschapv2',
}
