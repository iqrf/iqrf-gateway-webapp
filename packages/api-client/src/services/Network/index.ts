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

import { BaseService } from '../BaseService';

import { MobileOperatorService } from './MobileOperatorService';
import { ModemService } from './ModemService';
import { NetworkConnectionService } from './NetworkConnectionService';
import { NetworkInterfaceService } from './NetworkInterfaceService';
import { WiFiService } from './WiFiService';
import { WireGuardService } from './WireGuardService';

export * from './MobileOperatorService';
export * from './ModemService';
export * from './NetworkConnectionService';
export * from './NetworkInterfaceService';
export * from './WiFiService';
export * from './WireGuardService';

/**
 * Network services
 */
export class NetworkServices extends BaseService {

	/**
	 * Returns mobile operator service
	 * @return {MobileOperatorService} Mobile operator service
	 */
	public getMobileOperatorService(): MobileOperatorService {
		return new MobileOperatorService(this.apiClient);
	}

	/**
	 * Returns modem service
	 * @return {ModemService} Modem service
	 */
	public getModemService(): ModemService {
		return new ModemService(this.apiClient);
	}

	/**
	 * Returns network connection service
	 * @return {NetworkConnectionService} Network connection service
	 */
	public getNetworkConnectionService(): NetworkConnectionService {
		return new NetworkConnectionService(this.apiClient);
	}

	/**
	 * Returns network interface service
	 * @return {NetworkInterfaceService} Network interface service
	 */
	public getNetworkInterfaceService(): NetworkInterfaceService {
		return new NetworkInterfaceService(this.apiClient);
	}

	/**
	 * Returns Wi-Fi service
	 * @return {WiFiService} Wi-Fi service
	 */
	public getWiFiService(): WiFiService {
		return new WiFiService(this.apiClient);
	}

	/**
	 * Returns WireGuard service
	 * @return {WireGuardService} WireGuard service
	 */
	public getWireGuardService(): WireGuardService {
		return new WireGuardService(this.apiClient);
	}

}
