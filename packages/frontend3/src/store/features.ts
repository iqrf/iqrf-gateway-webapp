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
