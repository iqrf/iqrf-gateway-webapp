/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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
import FeatureService, {Feature, Features} from '@/services/FeatureService';
import {ActionTree, GetterTree, MutationTree} from 'vuex';

/**
 * Feature state
 */
interface FeatureState {

	/**
	 * Features
	 */
	features: Features,

}

const state: FeatureState = {
	features: {},
};

const actions: ActionTree<FeatureState, any> = {
	fetch({commit}) {
		return FeatureService.fetchAll()
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
	configuration: (state: FeatureState) => (name: string): Feature|undefined => {
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
