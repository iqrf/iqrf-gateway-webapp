import Vue from 'vue';
import {v4 as uuidv4} from 'uuid';
import {ActionContext, ActionTree, GetterTree, MutationTree} from 'vuex';

const state = {
	socket: {
		isConnected: false,
		hasReconnected: false,
		reconnectError: false,
	},
	requests: {},
	responses: {},
};

const actions: ActionTree<any, any> = {
	sendRequest: function (context: ActionContext<any, any>, request: any) {
		if (request.data !== undefined && request.data.msgId === undefined) {
			request.data.msgId = uuidv4();
		}
		Vue.prototype.$socket.sendObj(request);
		context.commit('SOCKET_ONSEND', request);
	},
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
};

export default {
	state,
	actions,
	getters,
	mutations,
};
