const state = {
	enabled: null,
	text: null,
};

const getters = {
	isEnabled(state) {
		return state.enabled;
	},
	text(state) {
		return state.text;
	},
};

const mutations = {
	HIDE(state) {
		state.enabled = false;
		state.text = null;
	},
	SHOW(state, text = null) {
		state.enabled = true;
		state.text = text;
	},
	UPDATE_TEXT(state, text) {
		state.text = text;
	},
};

export default {
	namespaced: true,
	state,
	getters,
	mutations,
};
