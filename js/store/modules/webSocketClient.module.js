import Vue from 'vue';

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
	SOCKET_ONOPEN(state, event)  {
		Vue.prototype.$socket = event.currentTarget;
		state.socket.isConnected = true;
	},
	// eslint-disable-next-line no-unused-vars
	SOCKET_ONCLOSE(state, event)  {
		state.socket.isConnected = false;
	},
	SOCKET_ONERROR(state, event)  {
		console.error(state, event);
	},
	SOCKET_ONMESSAGE(state, message)  {
		// eslint-disable-next-line no-console
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
