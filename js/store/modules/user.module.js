import AuthenticationService from '../../services/AuthenticationService';

const state = {
	user: null,
};

const actions = {
	signIn({commit}, credentials) {
		return AuthenticationService.login(credentials.username, credentials.password)
			.then((responses) => {
				const apiResponse = responses[0];
				commit('SIGN_IN', apiResponse.data);
				return responses;
			})
			.catch((error) => {
				console.error(error);
				return Promise.reject(error);
			});
	},
	signOut({commit}) {
		return AuthenticationService.logout()
			.then(() => {
				commit('SIGN_OUT');
			});
	},
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
	},
	getToken(state) {
		if (state.user === null) {
			return null;
		}
		return state.user.token;
	},
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
	actions,
	getters,
	mutations,
};
