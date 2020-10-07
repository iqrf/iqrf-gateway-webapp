import AuthenticationService, {User} from '../../services/AuthenticationService';
import {ActionTree, GetterTree, MutationTree} from 'vuex';
import {AxiosError} from 'axios';

/**
 * User state
 */
interface UserState {
	user: User|null,
}

const state: UserState = {
	user: null,
};

const actions: ActionTree<UserState, any> = {
	signIn({commit}, credentials) {
		return AuthenticationService.login(credentials)
			.then((user: User) => {
				commit('SIGN_IN', user);
			})
			.catch((error: AxiosError) => {
				console.error(error);
				return Promise.reject(error);
			});
	},
	signOut({commit}) {
		commit('SIGN_OUT');
	},
};

const getters: GetterTree<UserState, any> = {
	isLoggedIn(state: UserState) {
		return state.user !== null;
	},
	getId(state: UserState) {
		if (state.user === null) {
			return null;
		}
		return state.user.id;
	},
	getName(state: UserState) {
		if (state.user === null) {
			return null;
		}
		return state.user.username;
	},
	getRole(state: UserState) {
		if (state.user === null) {
			return null;
		}
		return state.user.role;
	},
	getToken(state: UserState) {
		if (state.user === null) {
			return null;
		}
		return state.user.token;
	},
};

const mutations: MutationTree<UserState> = {
	SIGN_IN(state: UserState, data: User) {
		state.user = data;
	},
	SIGN_OUT(state: UserState) {
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
