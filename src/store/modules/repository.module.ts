/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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
import {IIqrfRepositoryConfig} from '@/interfaces/Config/Misc';
import IqrfRepositoryConfigService from '@/services/IqrfRepository/IqrfRepositoryConfigService';


interface RepositoryState {
	config: IIqrfRepositoryConfig|null
}

const state: RepositoryState = {
	config: null,
};

const actions: ActionTree<RepositoryState, any> = {
	get({commit}) {
		return IqrfRepositoryConfigService.get()
			.then((config: IIqrfRepositoryConfig) => {
				commit('SET', config);
			});
	}
};

const getters: GetterTree<RepositoryState, any> = {
	configuration: (state: RepositoryState): IIqrfRepositoryConfig|undefined => {
		if (state.config) {
			return state.config;
		} else {
			return undefined;
		}
	},
};

const mutations: MutationTree<RepositoryState> = {
	SET(state: RepositoryState, config: IIqrfRepositoryConfig) {
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
