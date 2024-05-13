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

import {
	type GatewayBriefInformation,
} from '@iqrf/iqrf-gateway-webapp-client/types/Gateway/Info';
import { defineStore } from 'pinia';

import { useApiClient } from '@/services/ApiClient';

/// Gateway store state
interface GatewayState {
	/// Brief information about the gateway
	info: GatewayBriefInformation|null;
}

export const useGatewayStore = defineStore('gateway', {
	state: (): GatewayState => ({
		info: null,
	}),
	actions: {
		/**
		 * Fetches brief information about the gateway
		 * @return {Promise<void>}
		 */
		async fetchInfo(): Promise<void> {
			await useApiClient().getGatewayServices().getInfoService().fetchBrief()
				.then((info: GatewayBriefInformation): GatewayBriefInformation => {
					this.info = info;
					return info;
				})
				.catch((): void => {
					this.info = null;
				});
		},
	},
	getters: {
		/// Returns board type of the gateway
		board: (state: GatewayState): string|null => {
			if (state.info === null) {
				return null;
			}
			return state.info.board;
		},
	},
});
