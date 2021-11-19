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
import Vue from 'vue';

import {MenderActions} from '../../enums/Maintenance/Mender';

import {ActionTree, GetterTree, MutationTree} from 'vuex';
import {MenderClientState} from '../../interfaces/mender';

/**
 * Mender client state
 */
const state: MenderClientState = {
	isConnected: false,
	reconnecting: false,
	receivedMessages: 0,
	action: null,
	inProgress: false,
};

/**
 * Mender client getters
 */
const getters: GetterTree<MenderClientState, any> = {
	mender_isConnected(state: MenderClientState) {
		return state.isConnected;
	},
	mender_action(state: MenderClientState) {
		return state.action;
	},
	mender_actionInProgress(state: MenderClientState) {
		return state.inProgress;
	},
};

/**
 * Mender client mutations
 */
const mutations: MutationTree<MenderClientState> = {
	MENDER_SOCKET_ONOPEN(state: MenderClientState, event: Event) {
		Vue.prototype.$menderSocket = event.currentTarget;
		if (state.reconnecting) {
			state.reconnecting = false;
		}
		state.isConnected = true;
	},
	MENDER_SOCKET_ONCLOSE(state: MenderClientState) {
		state.isConnected = false;
	},
	MENDER_SOCKET_RECONNECT(state: MenderClientState) {
		state.reconnecting = true;
	},
	MENDER_SOCKET_ONERROR(state: MenderClientState, event: Event) {
		console.error(state, event);
	},
	MENDER_SOCKET_ONMESSAGE(state: MenderClientState, message: Record<string, any>) {
		state.receivedMessages += 1;
	},
	MENDER_ACTION_START(state: MenderClientState, action: MenderActions) {
		state.action = action;
		state.inProgress = true;
	},
	MENDER_ACTION_END(state: MenderClientState) {
		state.inProgress = false;
	},
};

export default {
	state,
	getters,
	mutations,
};
