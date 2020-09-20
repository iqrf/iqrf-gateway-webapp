import AuthenticationService from '../../services/AuthenticationService';
import {ActionTree, GetterTree, MutationTree} from 'vuex';
import {AxiosError, AxiosResponse} from 'axios';

const state = {
	user: null,
};

const actions: ActionTree<any, any> = {
	signIn({commit}, credentials) {
		return AuthenticationService.login(credentials.username, credentials.password)
			.then((responses: AxiosResponse[]) => {
				const apiResponse = responses[0];
				commit('SIGN_IN', apiResponse.data);
				return responses;
			})
			.catch((error: AxiosError) => {
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

const getters: GetterTree<any, any> = {
	isLoggedIn(state: any) {
		return state.user !== null;
	},
	getId(state: any) {
		if (state.user === null) {
			return null;
		}
		return state.user.id;
	},
	getName(state: any) {
		if (state.user === null) {
			return null;
		}
		return state.user.username;
	},
	getRole(state: any) {
		if (state.user === null) {
			return null;
		}
		return state.user.role;
	},
	getToken(state: any) {
		if (state.user === null) {
			return null;
		}
		return state.user.token;
	},
};

const mutations: MutationTree<any> = {
	SIGN_IN(state: any, data: any) {
		state.user = data;
	},
	SIGN_OUT(state: any) {
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
