import Vue from 'vue';
import {v4 as uuidv4} from 'uuid';

const state = {
	socket: {
		isConnected: false,
		reconnectError: false,
	},
	requests: {},
	responses: {},
};

const actions = {
	sendRequest: function (context, request) {
		if (request.data.msgId === undefined) {
			request.data.msgId = uuidv4();
		}
		Vue.prototype.$socket.sendObj(request);
		context.commit('SOCKET_ONSEND', request);
	},
};

const getters = {
	isSocketConnected(state) {
		return state.socket.isConnected;
	},
};

const mutations = {
	SOCKET_ONOPEN(state, event) {
		Vue.prototype.$socket = event.currentTarget;
		state.socket.isConnected = true;
	},
	// eslint-disable-next-line no-unused-vars
	SOCKET_ONCLOSE(state, event) {
		state.socket.isConnected = false;
	},
	SOCKET_ONERROR(state, event) {
		console.error(state, event);
	},
	SOCKET_ONMESSAGE(state, message) {
		state.responses[message.data.msgId] = message;
	},
	SOCKET_ONSEND(state, request) {
		state.requests[request.data.msgId] = request;
	},
	SOCKET_RECONNECT(state, count) {
		// eslint-disable-next-line no-console
		console.info(state, count);
	},
	SOCKET_RECONNECT_ERROR(state) {
		state.socket.reconnectError = true;
	},
};

export default {
	state,
	actions,
	getters,
	mutations,
};
