import {
	type Feature,
	type FeatureConfig,
	type Features,
} from '@iqrf/iqrf-gateway-webapp-client/types';
import { defineStore } from 'pinia';

import { useApiClient } from '@/services/ApiClient';

interface FeatureState {
	features: Features|null;
}

export const useFeatureStore = defineStore('features', {
	state: (): FeatureState => ({
		features: null,
	}),
	actions: {
		async fetch(): Promise<void> {
			return useApiClient().getFeatureService().fetchAll()
				.then((features: Features): void => {
					this.features = features;
				});
		},
	},
	getters: {
		isEnabled: (state: FeatureState) => (name: Feature): boolean|undefined => {
			if (state.features === null) {
				return undefined;
			}
			try {
				return state.features[name].enabled;
			} catch (e) {
				return undefined;
			}
		},
		getConfiguration: (state: FeatureState) => (name: Feature): FeatureConfig|undefined => {
			if (state.features === null) {
				return undefined;
			}
			try {
				return state.features[name];
			} catch (e) {
				return undefined;
			}
		},
	},
});
