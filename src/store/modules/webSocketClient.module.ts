import Vue from 'vue';
import i18n from '../../i18n';
import {v4 as uuidv4} from 'uuid';
import {ActionTree, GetterTree, MutationTree} from 'vuex';

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
	 * Daemon mode for IQMESH services
	 */
	mode: DaemonModeCheck;

	/**
	 * IQRF Gateway Daemon version object
	 */
	version: DaemonVersion;

}

/**
 * Daemon mode interface
 */
export interface DaemonModeCheck {

	/**
	 * Mode check 
	 */
	check: boolean;

	/**
	 * Daemon mode suitable for IQMESH services
	 */
	ready: boolean;

	/**
	 * Show Daemon mode modal
	 */
	modal: boolean;
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
	mode: {
		check: false,
		ready: false,
		modal: false,
	},
	version: {
		daemonVersion: '',
		msgId: '',
	},
};

const iqmeshRequests = [
	'iqmeshNetwork_AutoNetwork',
	'iqmeshNetwork_Backup',
	'iqmeshNetwork_BondNodeLocal',
	'iqmeshNetwork_EnumerateDevice',
	'iqmeshNetwork_ReadTrConf',
	'iqmeshNetwork_RemoveBond',
	'iqmeshNetwork_RemoveBondOnlyInC',
	'iqmeshNetwork_Restore',
	'iqmeshNetwork_SmartConnect',
	'iqmeshNetwork_WriteTrConf',
];

const actions: ActionTree<WebSocketClientState, any> = {
	sendRequest({state, commit, dispatch}, options: WebSocketOptions): Promise<string>|undefined {
		const request = options.request;
		if (request === null) {
			console.error('Request is null');
			return undefined;
		}
		if (iqmeshRequests.includes(request.mType) && !state.mode.ready) {
			commit('spinner/HIDE');
			state.mode.modal = true;
			return;
		} 
		if (request.data !== undefined && request.data.msgId === undefined) {
			request.data.msgId = uuidv4();
		}
		if (request.mType === 'iqmeshNetwork_AutoNetwork') {
			Vue.prototype.$socket.sendObj(request);
			commit('SOCKET_ONSEND', request);
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
		Vue.prototype.$socket.sendObj(request);
		commit('SOCKET_ONSEND', request);
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
		commit('REMOVE_MESSAGE', message);
	},
	getVersion({state, commit}): void {
		const options = new WebSocketOptions(null, 10000);
		state.version.msgId = uuidv4();
		options.request = {
			'mType': 'mngDaemon_Version',
			'data': {
				'msgId': state.version.msgId,
				'returnVerbose': true,
			},
		};
		Vue.prototype.$socket.sendObj(options.request);
		commit('SOCKET_ONSEND', options.request);
	},
	getMode({state, commit}): void {
		if (!state.socket.isConnected) {
			return;
		}
		const options = new WebSocketOptions(null, 10000);
		options.request = {
			'mType': 'mngDaemon_Mode',
			'data': {
				'msgId': uuidv4(),
				'req': {
					'operMode': '',
				},
				'returnVerbose': true,
			},
		};
		state.mode.check = true;
		Vue.prototype.$socket.sendObj(options.request);
		commit('SOCKET_ONSEND', options.request);
	},
	hideDaemonModal({commit}): void {
		commit('HIDE_MODE_MODAL');
	},
};

const getters: GetterTree<WebSocketClientState, any> = {
	isSocketConnected(state: WebSocketClientState) {
		return state.socket.isConnected;
	},
	daemonVersion(state: WebSocketClientState) {
		return state.version.daemonVersion;
	},
	daemonModeModal(state: WebSocketClientState) {
		return state.mode.modal;
	},
};

const mutations: MutationTree<WebSocketClientState> = {
	SOCKET_ONOPEN(state: WebSocketClientState, event: Event) {
		Vue.prototype.$socket = event.currentTarget;
		if (state.socket.hasReconnected) {
			setTimeout(() => {
				state.socket.isConnected = true;
			}, 1000);
		} else {
			state.socket.isConnected = true;
		}
		
	},
	SOCKET_ONCLOSE(state: WebSocketClientState) {
		state.socket.isConnected = false;
	},
	SOCKET_ONERROR(state: WebSocketClientState, event: Event) {
		console.error(state, event);
	},
	SOCKET_ONMESSAGE(state: WebSocketClientState, message: Record<string, any>) {
		if (message.mType === 'mngDaemon_Version' && message.data.msgId === state.version.msgId) {
			state.version.daemonVersion = message.data.rsp.version.substr(0, 6);
		} else if (message.mType === 'mngDaemon_Mode' && state.mode.check) {
			state.mode.check = false;
			const mode = message.data.rsp.operMode;
			if (mode === 'operational' || mode === 'forwarding') {
				state.mode.ready = true;
			} else {
				state.mode.ready = false;
			}
		}
		state.responses[message.data.msgId] = message;
	},
	SOCKET_ONSEND(state: WebSocketClientState, request: Record<string, any>) {
		state.requests[request.data.msgId] = request;
	},
	SOCKET_RECONNECT(state: WebSocketClientState, count: number) {
		// eslint-disable-next-line no-console
		console.info(state, count);
		state.socket.hasReconnected = true;
	},
	SOCKET_RECONNECT_ERROR(state: WebSocketClientState) {
		state.socket.reconnectError = true;
	},
	CLEAR_MESSAGES(state: WebSocketClientState) {
		state.messages = [];
	},
	REMOVE_MESSAGE(state: WebSocketClientState, message: number) {
		state.messages.splice(message, 1);
	},
	HIDE_MODE_MODAL(state: WebSocketClientState) {
		state.mode.modal = false;
	},
};

export default {
	state,
	actions,
	getters,
	mutations,
};
