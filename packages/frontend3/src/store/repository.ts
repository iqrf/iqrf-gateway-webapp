import { type IqrfRepositoryConfig } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { defineStore } from 'pinia';

import { useApiClient } from '@/services/ApiClient';

interface RepositoryStore {
	config: IqrfRepositoryConfig|null;
}

export const useRepositoryStore = defineStore('repository', {
	state: (): RepositoryStore => ({
		config: null,
	}),
	actions: {
		async fetch(): Promise<void> {
			useApiClient().getConfigServices().getIqrfRepositoryService().fetch()
				.then((config: IqrfRepositoryConfig) => this.config = config)
				.catch(() => {return;});
		},
	},
	getters: {
		configuration: (state: RepositoryStore): IqrfRepositoryConfig|null => {
			return state.config;
		},
	},
});
