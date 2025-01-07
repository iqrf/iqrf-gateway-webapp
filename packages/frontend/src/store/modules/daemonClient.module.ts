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
import store from '@/store';
import Vue from 'vue';
import i18n from '@/plugins/i18n';

import {v4 as uuidv4} from 'uuid';
import {ActionTree, GetterTree, MutationTree} from 'vuex';

import DaemonMessage from '@/ws/DaemonMessage';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

import {DaemonClientState} from '@/interfaces/wsClient';

/**
 * Daemon client state
 */
const state: DaemonClientState = {
	isConnected: false,
	reconnecting: false,
	hasReconnected: false,
	receivedMessages: 0,
	requests: {},
	responses: {},
	messages: [],
	version: '',
	versionMsgId: '',
};

/**
 * List of whitelisted requests while Daemon is in service mode
 */
const serviceModeWhitelist = [
	'mngDaemon_Exit',
	'mngDaemon_Mode',
	'mngDaemon_Version',
	'mngDaemon_Upload',
	'cfgDaemon_Component',
	'mngScheduler_AddTask',
	'mngScheduler_GetTask',
	'mngScheduler_List',
	'mngScheduler_RemoveAll',
	'mngScheduler_RemoveTask',
];

/**
 * Build Daemon message options
 * @param timeout Request timeout
 * @param message Timeout toast message
 * @param callback Callback to execute on request timeout
 * @returns {DaemonMessageOptions} Daemon API request options
 */
export function buildDaemonMessageOptions(timeout: number|null = null, message: string|null = null, callback: CallableFunction = () => {return;}): DaemonMessageOptions {
	return new DaemonMessageOptions(null, timeout, message, callback);
}

const actions: ActionTree<DaemonClientState, any> = {
	sendRequest(
		{ state, dispatch },
		options: DaemonMessageOptions
	): Promise<string> | undefined {
		const request = options.request;
		if (request === null) {
			console.error('Request is null');
			return undefined;
		}
		if (
			!serviceModeWhitelist.includes(request.mType) &&
			store.getters['monitorClient/getMode'] === 'service'
		) {
			this.commit('spinner/HIDE');
			this.commit('monitorClient/SHOW_MODAL');
			return;
		}
		if (request.data !== undefined && request.data.msgId === undefined) {
			request.data.msgId = uuidv4();
		}
		if (request.mType === 'iqmeshNetwork_AutoNetwork') {
			this.dispatch('daemon_sendMessage', request);
			return Promise.resolve(request.data.msgId);
		}
		let timeout: number | null = null;
		if (options.timeout) {
			timeout = window.setTimeout(() => {
				this.commit('spinner/HIDE');
				dispatch('removeMessage', request.data.msgId);
				options.callback();
				if (options.message === null) {
					return;
				}
				if (typeof options.message !== 'string') {
					Vue.$toast.error(options.message.toString());
				} else {
					Vue.$toast.error(i18n.t(options.message).toString());
				}
			}, options.timeout);
		}
		state.messages.push(new DaemonMessage(request.data.msgId, timeout));
		this.dispatch('daemon_sendMessage', request);
		return Promise.resolve(request.data.msgId);
	},
	removeMessage({ state, commit }, msgId): void {
		const message = state.messages.findIndex(
			(message: DaemonMessage): boolean => {
				return message.msgId === msgId;
			}
		);
		if (message === -1) {
			return;
		}
		window.clearTimeout(state.messages[message].timeout);
		commit('REMOVE_MESSAGE', message);
	},
	getVersion({ state }): void {
		const options = new DaemonMessageOptions(null, 10000);
		state.versionMsgId = uuidv4();
		options.request = {
			mType: 'mngDaemon_Version',
			data: {
				msgId: state.versionMsgId,
				returnVerbose: true,
			},
		};
		this.dispatch('daemon_sendMessage', options.request);
	},
};

const getters: GetterTree<DaemonClientState, any> = {
	isConnected(state: DaemonClientState) {
		return state.isConnected;
	},
	getVersion(state: DaemonClientState) {
		return state.version;
	},
};

const mutations: MutationTree<DaemonClientState> = {
	SOCKET_ONOPEN(state: DaemonClientState, event: Event) {
		if (state.hasReconnected) {
			setTimeout(() => {
				state.isConnected = true;
			}, 1000);
		} else {
			state.isConnected = true;
		}
	},
	SOCKET_ONCLOSE(state: DaemonClientState) {
		state.isConnected = false;
	},
	SOCKET_ONERROR(state: DaemonClientState, event: Event) {
		console.error(state, event);
	},
	SOCKET_ONMESSAGE(state: DaemonClientState, message: Record<string, any>) {
		if (message.mType === 'mngDaemon_Version' && message.data.msgId === state.versionMsgId) {
			const tokens = RegExp(/v\d+\.\d+\.\d+/g).exec(message.data.rsp.version);
			if (tokens !== null && tokens.length > 0) {
				state.version = tokens[0];
			}
		}
		state.responses[message.data.msgId] = message;
	},
	SOCKET_ONSEND(state: DaemonClientState, request: Record<string, any>) {
		state.requests[request.data.msgId] = request;
	},
	SOCKET_RECONNECT(state: DaemonClientState) {
		state.hasReconnected = true;
	},
	CLEAR_MESSAGES(state: DaemonClientState) {
		state.messages = [];
	},
	REMOVE_MESSAGE(state: DaemonClientState, message: number) {
		state.messages.splice(message, 1);
	},
	TRIM_MESSAGE_QUEUE(state: DaemonClientState) {
		const overload = state.messages.slice(32, state.messages.length - 1);
		state.messages.splice(32, state.messages.length - 1);
		overload.forEach((item: DaemonMessage) => {
			if (item.msgId in state.requests) {
				delete state.requests[item.msgId];
			}
			if (item.msgId in state.responses) {
				delete state.responses[item.msgId];
			}
		});
	},
};

export default {
	namespaced: true,
	state,
	actions,
	getters,
	mutations,
};
