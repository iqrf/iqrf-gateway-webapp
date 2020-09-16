const state = {
	user: null,
};

const getters = {
	isLoggedIn(state) {
		return state.user !== null;
	},
	getId(state) {
		if (state.user === null) {
			return null;
		}
		return state.user.id;
	},
	getName(state) {
		if (state.user === null) {
			return null;
		}
		return state.user.username;
	},
	getRole(state) {
		if (state.user === null) {
			return null;
		}
		return state.user.role;
	}
};

const mutations = {
	SIGN_IN(state, data) {
		state.user = data;
	},
	SIGN_OUT(state) {
		state.user = null;
	},
};

export default {
	namespaced: true,
	state,
	getters,
	mutations,
};
