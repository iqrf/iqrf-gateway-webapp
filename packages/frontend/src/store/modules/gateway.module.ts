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
import {
	GatewayBriefInformation
} from '@iqrf/iqrf-gateway-webapp-client/types/Gateway';
import {ActionTree, MutationTree} from 'vuex';

import {useApiClient} from '@/services/ApiClient';

interface GatewayState {
	/// Brief information about the gateway
	info: GatewayBriefInformation|null
}

const state: GatewayState = {
	info: null,
};

const actions: ActionTree<GatewayState, any> = {
	getInfo({commit}) {
		return useApiClient().getGatewayServices().getInfoService().getBrief()
			.then((info: GatewayBriefInformation) => commit('SET', info))
			.catch((): void => {return;});
	}
};

const getters = {
	board: (state: GatewayState): string|null => {
		if (state.info === null) {
			return null;
		}
		return state.info.board;
	}
};

const mutations: MutationTree<GatewayState> = {
	SET(state: GatewayState, info: GatewayBriefInformation): void {
		state.info = info;
	}
};

export default {
	namespaced: true,
	state,
	actions,
	getters,
	mutations,
};
