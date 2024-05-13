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

import { type DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';

import { useDaemonStore } from '@/store/daemonSocket';

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
