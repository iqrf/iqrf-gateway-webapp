/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
import AuthenticationService, {AccountState, User, UserRole} from '../../services/AuthenticationService';
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
	setJwt({commit}, user: User) {
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
	},
	signIn({dispatch}, credentials) {
		return AuthenticationService.login(credentials)
			.then((user: User) => {
				dispatch('setJwt', user);
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
	isLoggedIn(state: UserState): boolean {
		const now = new Date();
		const epoch = Math.round(now.getTime() / 1000);
		return (state.user !== null && state.expiration > epoch);
	},
	isUnverified(state: UserState): boolean|null {
		if (state.user === null) {
			return null;
		}
		return state.user.state === AccountState.UNVERIFIED;
	},
	getId(state: UserState): number|null {
		if (state.user === null) {
			return null;
		}
		return state.user.id;
	},
	getName(state: UserState): string|null {
		if (state.user === null) {
			return null;
		}
		return state.user.username;
	},
	getEmail(state: UserState): string|null {
		if (state.user === null) {
			return null;
		}
		return state.user.email;
	},
	getRole(state: UserState): UserRole|null {
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
