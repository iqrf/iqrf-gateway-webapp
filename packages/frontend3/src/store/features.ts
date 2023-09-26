import { Client } from '@iqrf/iqrf-gateway-webapp-client';
import { FeatureConfig, Features } from '@iqrf/iqrf-gateway-webapp-client/types';
import { defineStore } from 'pinia';
import UrlBuilder from '@/helpers/urlBuilder';

interface FeatureState {
	features: Features;
}

export const useFeatureStore = defineStore('features', {
	state: (): FeatureState => ({
		features: {},
	}),
	actions: {
		async fetch(): Promise<void> {
			return (new Client({
				config: {
					baseURL: (new UrlBuilder()).getRestApiUrl()
				}
			})).getFeatureService().fetchAll()
				.then((features: Features): void => {
					this.features = features;
				});
		}
	},
	getters: {
		isEnabled: (state: FeatureState) => (name: string): boolean|undefined => {
			try {
				return state.features[name].enabled;
			} catch (e) {
				return undefined;
			}
		},
		getConfiguration: (state: FeatureState) => (name: string): FeatureConfig|undefined => {
			try {
				return state.features[name];
			} catch (e) {
				return undefined;
			}
		},
	},
});
