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
import store from '..';

export class WebSocketOptions {
	public request: Record<string, any>|null;
	public timeout: number|null;
	public message: string|null;
	public callback: CallableFunction;

	constructor(request: Record<string, any>|null, timeout: number|null = null, message: string|null = null, callback: CallableFunction = () => {return;}) {
		this.request = request;
		this.timeout = timeout;
		this.message = message;
		this.callback = callback;
	}
}

export class WebSocketMessage {
	public msgId: string;
	public timeout: number|undefined;

	constructor(msgId: string, timeout: number|null = null) {
		this.msgId = msgId;
		this.timeout = timeout ?? undefined;
	}
}

/**
 * WebSocket state
 */
export interface WebSocketState {

	/**
	 * Is WebSocket client connected to the server?
	 */
	isConnected: boolean;

	/**
	 * Has been WebSocket client reconnected to the server?
	 */
	hasReconnected: boolean;

	/**
	 * Has reconnect error occurred?
	 */
	reconnectError: boolean;

}

/**
 * WebSocket client state
 */
export interface WebSocketClientState {

	/**
	 * WebSocket state
	 */
	socket: WebSocketState;

	/**
	 * Sent requests
	 */
	requests: Record<string, any>;

	/**
	 * Received responses
	 */
	responses: Record<string, any>;

	/**
	 * Sent messages
	 */
	messages: Array<WebSocketMessage>;

	/**
	 * IQRF Gateway Daemon version object
	 */
	version: DaemonVersion;

}

/**
 * Daemon version interface
 */
export interface DaemonVersion {

	/**
	 * IQRF Gateway Daemon version
	 */
	daemonVersion: string;

	/**
	 * Daemon api message id
	 */
	msgId: string;
}

const state: WebSocketClientState = {
	socket: {
		isConnected: false,
		hasReconnected: false,
		reconnectError: false,
	},
	requests: {},
	responses: {},
	messages: [],
	version: {
		daemonVersion: '',
		msgId: '',
	},
};

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

const actions: ActionTree<WebSocketClientState, any> = {
	daemon_sendRequest({state, commit, dispatch}, options: WebSocketOptions): Promise<string>|undefined {
		const request = options.request;
		if (request === null) {
			console.error('Request is null');
			return undefined;
		}
		if (!serviceModeWhitelist.includes(request.mType) && store.getters['monitor_getMode'] === 'service') {
			commit('spinner/HIDE');
			commit('MONITOR_SHOW_MODAL');
			return;
		}
		if (request.data !== undefined && request.data.msgId === undefined) {
			request.data.msgId = uuidv4();
		}
		if (request.mType === 'iqmeshNetwork_AutoNetwork') {
			dispatch('daemon_sendMessage', request);
			return Promise.resolve(request.data.msgId);
		}
		let timeout: number|null = null;
		if (options.timeout) {
			timeout = window.setTimeout(() => {
				commit('spinner/HIDE');
				dispatch('removeMessage', request.data.msgId);
				options.callback();
				if (options.message === null) {
					return;
				}
				Vue.$toast.error(i18n.t(options.message).toString());
			}, options.timeout);
		}
		state.messages.push(new WebSocketMessage(request.data.msgId, timeout));
		dispatch('daemon_sendMessage', request);
		return Promise.resolve(request.data.msgId);
	},
	removeMessage({state, commit}, msgId): void {
		const message = state.messages.findIndex((message: WebSocketMessage): boolean => {
			return message.msgId === msgId;
		});
		if (message === -1) {
			return;
		}
		window.clearTimeout(state.messages[message].timeout);
		commit('DAEMON_REMOVE_MESSAGE', message);
	},
	getVersion({state, dispatch}): void {
		const options = new WebSocketOptions(null, 10000);
		state.version.msgId = uuidv4();
		options.request = {
			'mType': 'mngDaemon_Version',
			'data': {
				'msgId': state.version.msgId,
				'returnVerbose': true,
			},
		};
		dispatch('daemon_sendMessage', options.request);
	},
};

const getters: GetterTree<WebSocketClientState, any> = {
	daemon_isSocketConnected(state: WebSocketClientState) {
		return state.socket.isConnected;
	},
	daemon_getVersion(state: WebSocketClientState) {
		return state.version.daemonVersion;
	},
};

const mutations: MutationTree<WebSocketClientState> = {
	DAEMON_SOCKET_ONOPEN(state: WebSocketClientState, event: Event) {
		Vue.prototype.$socket = event.currentTarget;
		if (state.socket.hasReconnected) {
			setTimeout(() => {
				state.socket.isConnected = true;
			}, 1000);
		} else {
			state.socket.isConnected = true;
		}

	},
	DAEMON_SOCKET_ONCLOSE(state: WebSocketClientState) {
		state.socket.isConnected = false;
	},
	DAEMON_SOCKET_ONERROR(state: WebSocketClientState, event: Event) {
		console.error(state, event);
	},
	DAEMON_SOCKET_ONMESSAGE(state: WebSocketClientState, message: Record<string, any>) {
		if (message.mType === 'mngDaemon_Version' && message.data.msgId === state.version.msgId) {
			state.version.daemonVersion = message.data.rsp.version.substr(0, 6);
		}
		state.responses[message.data.msgId] = message;
	},
	DAEMON_SOCKET_ONSEND(state: WebSocketClientState, request: Record<string, any>) {
		state.requests[request.data.msgId] = request;
	},
	DAEMON_SOCKET_RECONNECT(state: WebSocketClientState) {
		state.socket.hasReconnected = true;
	},
	DAEMON_SOCKET_RECONNECT_ERROR(state: WebSocketClientState) {
		state.socket.reconnectError = true;
	},
	DAEMON_CLEAR_MESSAGES(state: WebSocketClientState) {
		state.messages = [];
	},
	DAEMON_REMOVE_MESSAGE(state: WebSocketClientState, message: number) {
		state.messages.splice(message, 1);
	},
	DAEMON_TRIM_MESSAGE_QUEUE(state: WebSocketClientState) {
		const overload = state.messages.slice(32, state.messages.length - 1);
		state.messages.splice(32, state.messages.length - 1);
		overload.forEach((item: WebSocketMessage) => {
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
	state,
	actions,
	getters,
	mutations,
};
