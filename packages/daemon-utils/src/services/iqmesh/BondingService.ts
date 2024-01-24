import { type DaemonMessageOptions } from '../../utils';
import { IqmeshServiceMessages } from '../../enums';

/**
 * IQMESH Bonding API service
 */
export class BondingService {

	/**
	 * Unbond a device or all devices from network
	 * @param {number} address Device address to unbond
	 * @param {boolean} network Unbond all devices
	 * @param {number} hwpid Hardware profile ID
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static removeBond(address: number, network: boolean = false, hwpid: number = 65535, options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: IqmeshServiceMessages.RemoveBond,
			data: {
				req: {
					deviceAddr: address,
					wholeNetwork: network,
					hwpId: hwpid,
				},
				returnVerbose: true,
			},
		};
		return options;
	}

	/**
	 * Unbond device(s) in coordinator memory only
	 * @param {number[]} addresses Device addresses
	 * @param {boolean} clearAll Clear all bonds
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static removeBondCoordinator(addresses: number[], clearAll: boolean = false, options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: IqmeshServiceMessages.RemoveBondCoordinator,
			data: {
				req: {
					deviceAddr: addresses,
					clearAllBonds: clearAll,
				},
				returnVerbose: true,
			},
		};
		return options;
	}
}
