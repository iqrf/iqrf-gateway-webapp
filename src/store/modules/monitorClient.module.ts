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
import {MonitorClientState} from '../../interfaces/wsClient';

const state: MonitorClientState = {
	isConnected: false,
	reconnecting: false,
	receivedMessages: 0,
	mode: 'unknown',
	modal: false,
	queueLen: 'unknown',
};

const actions: ActionTree<MonitorClientState, any> = {
	showModal({commit}): void {
		commit('MONITOR_SHOW_MODAL');
	},
};

const getters: GetterTree<MonitorClientState, any> = {
	monitor_getMode(state: MonitorClientState): string {
		return state.mode;
	},
	monitor_getModalState(state: MonitorClientState): boolean {
		return state.modal;
	},
	monitor_getQueueLen(state: MonitorClientState): number|string {
		return state.queueLen;
	}
};

const mutations: MutationTree<MonitorClientState> = {
	MONITOR_SOCKET_ONOPEN(state: MonitorClientState, event: Event) {
		if (state.reconnecting) {
			state.reconnecting = false;
		}
		state.isConnected = true;
	},
	MONITOR_SOCKET_ONCLOSE(state: MonitorClientState) {
		state.isConnected = false;
		state.mode = 'unknown';
		state.queueLen = 'unknown';
	},
	MONITOR_SOCKET_RECONNECT(state: MonitorClientState) {
		state.reconnecting = true;
	},
	MONITOR_SOCKET_ONERROR(state: MonitorClientState, event: Event) {
		console.error(state, event);
	},
	MONITOR_SOCKET_ONMESSAGE(state: MonitorClientState) {
		state.receivedMessages += 1;
	},
	MONITOR_SHOW_MODAL(state: MonitorClientState): void {
		state.modal = true;
	},
	MONITOR_HIDE_MODAL(state: MonitorClientState): void {
		state.modal = false;
	},
	MONITOR_SET_MODE(state: MonitorClientState, mode: string): void {
		state.mode = mode;
	},
	MONITOR_UPDATE_QUEUE(state: MonitorClientState, len: number): void {
		state.queueLen = len;
	},
};

export default {
	state,
	actions,
	getters,
	mutations,
};
