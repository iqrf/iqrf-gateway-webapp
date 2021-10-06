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
import ClientSocket from '../../ws/ClientSocket';

interface WebSocketState {
	daemonSocket: ClientSocket|null;
	menderSocket: ClientSocket|null;
	monitorSocket: ClientSocket|null;
}

const state: WebSocketState = {
	daemonSocket: null,
	menderSocket: null,
	monitorSocket: null,
};

const actions: ActionTree<WebSocketState, any> = {
	daemon_initSocket({state}, socket: ClientSocket) {
		if (state.daemonSocket !== null) {
			return;
		}
		state.daemonSocket = socket;
	},
	daemon_sendMessage({state}, message: any) {
		if (state.daemonSocket === null) {
			return;
		}
		state.daemonSocket.send(message);
	},
	mender_initSocket({state}, socket: ClientSocket) {
		if (state.menderSocket !== null) {
			return;
		}
		state.menderSocket = socket;
	},
	mender_connectSocket({state}) {
		if (state.menderSocket === null) {
			return;
		}
		state.menderSocket.connect();
	},
	mender_destroySocket({state}) {
		if (state.menderSocket === null) {
			return;
		}
		state.menderSocket.close();
		state.menderSocket = null;
	},
	monitor_initSocket({state}, socket: ClientSocket) {
		if (state.monitorSocket !== null) {
			return;
		}
		state.monitorSocket = socket;
	},
};

const getters: GetterTree<WebSocketState, any> = {};
const mutations: MutationTree<WebSocketState> = {};

export default {
	state,
	actions,
	getters,
	mutations,
};
