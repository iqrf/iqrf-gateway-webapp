import { useDaemonStore } from '@/store/daemonSocket';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';

export class IqmeshNetworkService {

	private store;

	constructor() {
		this.store = useDaemonStore();
	}

	deviceEnumeration(address: number, options: DaemonMessageOptions): Promise<string> {
		options.request = {
			mType: 'iqmeshNetwork_EnumerateDevice',
			data: {
				repeat: 1,
				req: {
					deviceAddr: address,
					morePeripheralsInfo: true,
				},
				returnVerbose: true,
			},
		};
		return this.store.sendMessage(options);
	}
}
