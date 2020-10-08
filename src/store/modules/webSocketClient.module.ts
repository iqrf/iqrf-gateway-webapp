import Vue from 'vue';
import i18n from '../../i18n';
import {v4 as uuidv4} from 'uuid';
import {ActionTree, GetterTree, MutationTree} from 'vuex';

export class WebSocketOptions {
	public request: any = null;
	public timeout: number|null = null;
	public message: string|null = null;
	public callback: CallableFunction = () => {return;};

	constructor(request: any, timeout: number|null = null, message: string|null = null, callback: CallableFunction = () => {return;}) {
		this.request = request;
		this.timeout = timeout;
		this.message = message;
		this.callback = callback;
	}
}

export class WebSocketMessage {
	public msgId: string;
	public timeout: number|null;

	constructor(msgId: string, timeout: number|null = null) {
		this.msgId = msgId;
		this.timeout = timeout;
	}
}

const state = {
	socket: {
		isConnected: false,
		hasReconnected: false,
		reconnectError: false,
	},
	requests: {},
	responses: {},
	messages: [],
};

const actions: ActionTree<any, any> = {
	sendRequest({state, commit, dispatch}, options: WebSocketOptions): Promise<string> {
		const request = options.request;
		if (request.data !== undefined && request.data.msgId === undefined) {
			request.data.msgId = uuidv4();
		}
		if (request.mType === 'iqmeshNetwork_AutoNetwork') {
			Vue.prototype.$socket.sendObj(request);
			commit('SOCKET_ONSEND', request);
			return Promise.resolve(request.data.msgId);
		}
		let timeout = null;
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
	}
};

const getters: GetterTree<any, any> = {
	isSocketConnected(state: any) {
		return state.socket.isConnected;
	},
};

const mutations: MutationTree<any> = {
	SOCKET_ONOPEN(state: any, event: Event) {
		Vue.prototype.$socket = event.currentTarget;
		if (state.hasReconnected) {
			setTimeout(() => {
				state.socket.isConnected = true;
			}, 1000);
		} else {
			state.socket.isConnected = true;
		}
		
	},
	SOCKET_ONCLOSE(state: any) {
		state.socket.isConnected = false;
	},
	SOCKET_ONERROR(state: any, event: Event) {
		console.error(state, event);
	},
	SOCKET_ONMESSAGE(state: any, message: any) {
		state.responses[message.data.msgId] = message;
	},
	SOCKET_ONSEND(state: any, request: any) {
		state.requests[request.data.msgId] = request;
	},
	SOCKET_RECONNECT(state: any, count: number) {
		// eslint-disable-next-line no-console
		console.info(state, count);
		state.hasReconnected = true;
	},
	SOCKET_RECONNECT_ERROR(state: any) {
		state.socket.reconnectError = true;
	},
	CLEAR_MESSAGES(state: any) {
		state.messages = [];
	},
	REMOVE_MESSAGE(state: any, message: number) {
		state.messages.splice(message, 1);
	}
};

export default {
	state,
	actions,
	getters,
	mutations,
};
