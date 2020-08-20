const state = {
	iqrfChannelState: null,
	operationMode: null
};

const actions = {
	setIqrfChannelState({commit}, state) {
		commit('SET_IQRF_CHANNEL_STATE', state);
	},
	setDaemonOperationMode({commit}, mode) {
		commit('SET_DAEMON_OPERATION_MODE', mode);
	},
};

const mutations = {
	SET_IQRF_CHANNEL_STATE(state, iqrfChannelState) {
		state.iqrfChannelState = iqrfChannelState;
	},
	SET_DAEMON_OPERATION_MODE(state, operationMode) {
		state.operationMode = operationMode;
	}
};

export default {
	namespaced: true,
	state,
	actions,
	mutations
};
