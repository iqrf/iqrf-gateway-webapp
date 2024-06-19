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

import { NetworkConnectionType } from '../types/Network/NetworkConnection';
import { NetworkInterfaceType } from '../types/Network/NetworkInterface';

/**
 * IP network utilities
 */
export class IpNetworkUtils {

	/**
	 * Converts connection type to interface type
	 * @param {NetworkConnectionType | null} connectionType Network connection type
	 * @returns {NetworkInterfaceType | null} Network interface type
	 */
	public static connectionTypeToInterfaceType(connectionType: NetworkConnectionType | null): NetworkInterfaceType | null {
		if (connectionType === null) {
			return null;
		}
		switch (connectionType) {
			case NetworkConnectionType.Ethernet:
				return NetworkInterfaceType.ETHERNET;
			case NetworkConnectionType.WiFi:
				return NetworkInterfaceType.WIFI;
			case NetworkConnectionType.GSM:
				return NetworkInterfaceType.GSM;
			case NetworkConnectionType.VLAN:
				return NetworkInterfaceType.VLAN;
			default:
				return null;
		}
	}

}
