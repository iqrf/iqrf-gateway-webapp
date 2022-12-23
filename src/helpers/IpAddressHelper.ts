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
import ip from 'ip-regex';
import {IConnection} from '@/interfaces/Network/Connection';

export default class IpAddressHelper {

	/**
	 * Converts dot representation of ip to integer value
	 * @param {string} address IPv4 address string
	 * @returns {number} integer value of ipv4
	 */
	public static ipv4ToInt(address: string): number {
		return address.split('.').reduce((acc, oct) => {return acc * 256 + parseInt(oct, 10);}, 0) >>> 0;
	}

	/**
	 * Checks if passed IP is in subnet with gateway
	 * @param {string} address Address to check
	 * @param {string} mask Subnet mask
	 * @param {string} gateway Gateway address
	 * @returns {boolean} Is address in the same subnet as gateway?
	 */
	public static ipv4SubnetCheck(address: string, mask: string, gateway: string|null): boolean {
		if (gateway === null) {
			return false;
		}
		if (!ip.v4({exact: true}).test(address) ||
			!ip.v4({exact: true}).test(mask) ||
			!ip.v4({exact: true}).test(gateway)) {
			return true;
		}
		const addressInt = this.ipv4ToInt(address);
		const maskInt = this.ipv4ToInt(mask);
		const gatewayInt = this.ipv4ToInt(gateway);
		return ((addressInt & maskInt) === (gatewayInt & maskInt));
	}

	/**
	 * Checks if passed IP is in subnet with gateway
	 * @param {IConnection} connection Connection to check
	 * @returns {boolean} Is address in the same subnet as gateway?
	 */
	public static ipv4ConnectionSubnetCheck(connection: IConnection): boolean {
		if (connection.ipv4.method === 'auto') {
			return true;
		}
		if (connection.ipv4.addresses.length === 0) {
			return false;
		}
		const address = connection.ipv4.addresses[0].address;
		const mask = connection.ipv4.addresses[0].mask;
		const gateway = connection.ipv4.gateway;
		return IpAddressHelper.ipv4SubnetCheck(address, mask, gateway);
	}

}
