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

import {MonitorClientState} from '@/interfaces/wsClient';

/**
 * Monitor client state
 */
const state: MonitorClientState = {
	isConnected: false,
	reconnecting: false,
	hasReconnected: false,
	receivedMessages: 0,
	mode: 'unknown',
	modal: false,
	queueLen: 'unknown',
};

const actions: ActionTree<MonitorClientState, any> = {
	setMode({commit}, mode: string): void {
		commit('SET_MODE', mode);
	}
};

const getters: GetterTree<MonitorClientState, any> = {
	getMode(state: MonitorClientState): string {
		return state.mode;
	},
	getModalState(state: MonitorClientState): boolean {
		return state.modal;
	},
	getQueueLen(state: MonitorClientState): number | string {
		return state.queueLen;
	},
};

const mutations: MutationTree<MonitorClientState> = {
	SOCKET_ONOPEN(state: MonitorClientState) {
		if (state.reconnecting) {
			state.reconnecting = false;
		}
		state.isConnected = true;
	},
	SOCKET_ONCLOSE(state: MonitorClientState) {
		state.isConnected = false;
		state.mode = 'unknown';
		state.queueLen = 'unknown';
	},
	SOCKET_RECONNECT(state: MonitorClientState) {
		state.reconnecting = true;
	},
	SOCKET_ONERROR(state: MonitorClientState, event: Event) {
		console.error(state, event);
	},
	SOCKET_ONMESSAGE(state: MonitorClientState) {
		state.receivedMessages += 1;
	},
	SHOW_MODAL(state: MonitorClientState): void {
		state.modal = true;
	},
	HIDE_MODAL(state: MonitorClientState): void {
		state.modal = false;
	},
	SET_MODE(state: MonitorClientState, mode: string): void {
		state.mode = mode;
	},
	UPDATE_QUEUE(state: MonitorClientState, len: number): void {
		state.queueLen = len;
	},
};

export default {
	namespaced: true,
	actions,
	state,
	getters,
	mutations,
};
