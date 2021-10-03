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
import i18n from '../../i18n';
import {v4 as uuidv4} from 'uuid';
import {ActionTree, GetterTree, MutationTree} from 'vuex';

export interface GenericWebSocketState {
	isConnected: boolean;
	reconnected: boolean;
	reconnectError: boolean;
}

export interface WebSocketClientState {
	mender: GenericWebSocketState
}

const state: WebSocketClientState = {
	mender: {
		isConnected: false,
		reconnected: false,
		reconnectError: false,
	}
};

const mutations: MutationTree<WebSocketClientState> = {
	MENDER_SOCKET_ONOPEN(state: WebSocketClientState, event: Event) {
		Vue.prototype.$menderSocket = event.currentTarget;
		if (state.mender.reconnected) {
			setTimeout(() => {
				state.mender.isConnected = true;
			}, 1000);
		} else {
			state.mender.isConnected = true;
		}
	},
	MENDER_SOCKET_ONCLOSE(state: WebSocketClientState) {
		state.mender.isConnected = false;
	},
	MENDER_SOCKET_RECONNECT(state: WebSocketClientState) {
		state.mender.reconnected = true;
	},
	MENDER_SOCKET_ONERROR(state: WebSocketClientState, event: Event) {
		console.error(state, event);
	},
};

export default {
	state,
	mutations,
};
