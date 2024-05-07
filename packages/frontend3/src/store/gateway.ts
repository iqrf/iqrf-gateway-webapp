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
