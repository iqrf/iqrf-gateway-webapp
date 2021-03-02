import AuthenticationService, {User} from '../../services/AuthenticationService';
import {ActionTree, GetterTree, MutationTree} from 'vuex';
import {AxiosError} from 'axios';
import jwt_decode, {JwtPayload} from 'jwt-decode';

/**
 * User state
 */
interface UserState {
	user: User|null,
	expiration: number,
}

const state: UserState = {
	user: null,
	expiration: 0,
};

const actions: ActionTree<UserState, any> = {
	signIn({commit}, credentials) {
		return AuthenticationService.login(credentials)
			.then((user: User) => {
				const now = new Date();
				const epoch = Math.round(now.getTime() / 1000);
				const jwt: JwtPayload = jwt_decode(user.token);
				if (jwt.exp === undefined) {
					return Promise.reject(new Error('Expiration missing in JWT token.'));
				}
				if (jwt.iat === undefined) {
					return Promise.reject(new Error('Token issue timestamp missing in JWT token.'));
				}
				const diff = epoch - jwt.iat;
				commit('SET_EXPIRATION', jwt.exp + diff);
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
		const now = new Date();
		const epoch = Math.round(now.getTime() / 1000);
		return (state.user !== null && state.expiration > epoch);
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
	SET_EXPIRATION(state: UserState, expiration: number) {
		state.expiration = expiration;
	},
	SIGN_IN(state: UserState, data: User) {
		state.user = data;
	},
	SIGN_OUT(state: UserState) {
		state.user = null;
		state.expiration = 0;
	},
};

export default {
	namespaced: true,
	state,
	actions,
	getters,
	mutations,
};
