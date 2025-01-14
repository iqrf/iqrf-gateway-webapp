/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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
import {ActionTree, GetterTree, MutationTree} from 'vuex';
import {useApiClient} from '@/services/ApiClient';
import {FeatureConfig, Features} from '@iqrf/iqrf-gateway-webapp-client/types';

/**
 * Feature state
 */
interface FeatureState {

	/**
	 * Features
	 */
	features: Features | object,

}

const state: FeatureState = {
	features: {},
};

const actions: ActionTree<FeatureState, any> = {
	fetch({commit}) {
		return useApiClient().getFeatureService().list()
			.then((features: Features) => {
				commit('SET', features);
			});
	},
};

const getters: GetterTree<FeatureState, any> = {
	isEnabled: (state: FeatureState) => (name: string): boolean|undefined => {
		try {
			return state.features[name].enabled;
		} catch {
			return undefined;
		}
	},
	configuration: (state: FeatureState) => (name: string): FeatureConfig|undefined => {
		try {
			return state.features[name];
		} catch {
			return undefined;
		}
	},
};

const mutations: MutationTree<FeatureState> = {
	SET(state: FeatureState, features: Features) {
		state.features = features;
	}
};

export default {
	namespaced: true,
	state,
	actions,
	getters,
	mutations,
};
