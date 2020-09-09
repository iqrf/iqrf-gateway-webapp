const state = {
	enabled: null,
};

const getters = {
	isEnabled(state) {
		return state.enabled;
	},
};

const mutations = {
	HIDE(state) {
		state.enabled = false;
	},
	SHOW(state) {
		state.enabled = true;
	},
};

export default {
	namespaced: true,
	state,
	getters,
	mutations,
};
