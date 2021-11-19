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
import {MenderActions} from '../../enums/Maintenance/Mender';

import {GetterTree, MutationTree} from 'vuex';
import {MenderClientState} from '../../interfaces/wsClient';

/**
 * Mender client state
 */
const state: MenderClientState = {
	isConnected: false,
	reconnecting: false,
	hasReconnected: false,
	receivedMessages: 0,
	action: null,
	inProgress: false,
};

/**
 * Mender client getters
 */
const getters: GetterTree<MenderClientState, any> = {
	isConnected(state: MenderClientState) {
		return state.isConnected;
	},
	action(state: MenderClientState) {
		return state.action;
	},
	actionInProgress(state: MenderClientState) {
		return state.inProgress;
	},
};

/**
 * Mender client mutations
 */
const mutations: MutationTree<MenderClientState> = {
	SOCKET_ONOPEN(state: MenderClientState, event: Event) {
		if (state.reconnecting) {
			state.reconnecting = false;
		}
		state.isConnected = true;
	},
	SOCKET_ONCLOSE(state: MenderClientState) {
		state.isConnected = false;
	},
	SOCKET_RECONNECT(state: MenderClientState) {
		state.reconnecting = true;
	},
	SOCKET_ONERROR(state: MenderClientState, event: Event) {
		console.error(state, event);
	},
	SOCKET_ONMESSAGE(state: MenderClientState, message: Record<string, any>) {
		state.receivedMessages += 1;
	},
	ACTION_START(state: MenderClientState, action: MenderActions) {
		state.action = action;
		state.inProgress = true;
	},
	ACTION_END(state: MenderClientState) {
		state.inProgress = false;
	},
};

export default {
	namespaced: true,
	state,
	getters,
	mutations,
};
