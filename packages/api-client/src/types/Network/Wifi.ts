/**
 * Copyright 2023-2024 MICRORISC s.r.o.
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
 * Access point information
 */
export interface AccessPoint {
	/// Access point BSSID
	bssid: string,
	/// Access point channel
	channel: number,
	/// Is access point connected?
	inUse: boolean,
	/// WiFi interface name
	interfaceName: string|null,
	/// Access point mode
	mode: string,
	/// Access point rate
	rate: string
	/// Access point security
	security: string,
	/// Access point signal
	signal: number,
	/// Access point SSID
	ssid: string,
	/// Access point UUID
	uuid?: string
}

/**
 * WiFi Access Point security enum
 */
export enum WifiApSecurityType {
	/// OWE network
	OWE = 'OWE',
	/// Open network
	Open = 'Open',
	/// WEP network
	WEP = 'WEP',
	/// WPA2-Enterprise network
	WPA2_Enterprise = 'WPA2-Enterprise',
	/// WPA2-Personal network
	WPA2_Personal = 'WPA2-Personal',
	/// WPA3-Enterprise network
	WPA3_Enterprise = 'WPA3-Enterprise',
	/// WPA3-Personal network
	WPA3_Personal = 'WPA3-Personal',
	/// WPA-Enterprise network
	WPA_Enterprise = 'WPA-Enterprise',
	/// WPA-Personal network
	WPA_Personal = 'WPA-Personal',
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

/**
 * EAP (Extensible Authentication Protocol) configuration
 */
export interface EapConfiguration {
	/// EAP (Extensible Authentication Protocol) anonymous identity
	anonymousIdentity?: string,
	/// EAP (Extensible Authentication Protocol) CA certificate path
	cert?: string,
	// EAP (Extensible Authentication Protocol) identity
	identity: string,
	/// EAP (Extensible Authentication Protocol) password
	password: string,
	/// EAP (Extensible Authentication Protocol) phase one authentication method
	phaseOneMethod: EapPhaseOneMethod|null,
	/// EAP (Extensible Authentication Protocol) phase two authentication method
	phaseTwoMethod: EapPhaseTwoMethod|null
}

/**
 * Cisco LEAP (Lightweight Extensible Authentication Protocol) configuration
 */
export interface LeapConfiguration {
	/// Cisco LEAP (Lightweight Extensible Authentication Protocol) password
	password: string,
	/// Cisco LEAP (Lightweight Extensible Authentication Protocol) username
	username: string
}

/**
 * WEP key length
 */
export enum WepKeyLen {
	BIT128 = '128bit',
	BIT64 = '64bit',
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
 * WEP configuration
 */
export interface WepConfiguration {
	/// WEP key length
	keyLen: WepKeyLen,
	/// WEP key type
	keyType: WepKeyType,
	/// WEP keys
	keys: string[],
}


/**
 * WiFi security type
 */
export enum WifiSecurityType {
	/// Cisco LEAP (Lightweight Extensible Authentication Protocol)
	LEAP = 'leap',
	/// Open
	Open = 'open',
	/// WEP (Wired Equivalent Privacy)
	WEP = 'wep',
	/// WPA-EAP (Wi-Fi Protected Access - Extensible Authentication Protocol)
	WPA_EAP = 'wpa-eap',
	/// WPA-PSK (Wi-Fi Protected Access - Pre-Shared Key)
	WPA_PSK = 'wpa-psk',
}

/**
 * WiFi security configuration
 */
export interface WifiSecurity {
	/// EAP (Extensible Authentication Protocol) configuration
	eap?: EapConfiguration,
	/// Cisco LEAP (Lightweight Extensible Authentication Protocol) configuration
	leap?: LeapConfiguration,
	/// Pre-shared key
	psk?: string,
	/// Type of WiFi security
	type: WifiSecurityType,
	/// WEP configuration
	wep?: WepConfiguration,
}

/**
 * WiFi mode
 */
export enum WifiMode {
	/// Ad-hoc mode
	AdHoc = 'adhoc',
	/// Access point mode
	AccessPoint = 'ap',
	/// Infrastructure mode
	Infrastructure = 'infrastructure',
	/// Mesh mode
	Mesh = 'mesh',
}

/**
 * Scanned WiFi network
 */
export interface WifiNetwork {
	/// Access points
	aps: AccessPoint[],
	/// Show details
	showDetails: boolean,
	/// SSID
	ssid: string
}
