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

import {IqrfRepositoryConfig} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {ActionTree, GetterTree, MutationTree} from 'vuex';

import {useApiClient} from '@/services/ApiClient';


interface RepositoryState {
	config: IqrfRepositoryConfig|null
}

const state: RepositoryState = {
	config: null,
};

const actions: ActionTree<RepositoryState, any> = {
	get({commit}) {
		return useApiClient().getConfigServices().getIqrfRepositoryService().fetch()
			.then((config: IqrfRepositoryConfig) => {
				commit('SET', config);
			})
			.catch(() => {return;});
	}
};

const getters: GetterTree<RepositoryState, any> = {
	configuration: (state: RepositoryState): IqrfRepositoryConfig|null => {
		if (state.config) {
			return state.config;
		} else {
			return null;
		}
	},
};

const mutations: MutationTree<RepositoryState> = {
	SET(state: RepositoryState, config: IqrfRepositoryConfig) {
		state.config = config;
	}
};

export default {
	namespaced: true,
	state,
	actions,
	getters,
	mutations,
};
