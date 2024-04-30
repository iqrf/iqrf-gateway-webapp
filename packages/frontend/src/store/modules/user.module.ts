/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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
import {
	AccountState,
	UserBase,
	UserInfo,
	UserRole,
	UserSignedIn
} from '@iqrf/iqrf-gateway-webapp-client/types/User';
import * as Sentry from '@sentry/vue';
import {AxiosError} from 'axios';
import {jwtDecode, JwtPayload} from 'jwt-decode';
import {ActionTree, GetterTree, MutationTree} from 'vuex';

import {UserRoleIndex} from '@/services/AuthenticationService';
import {useApiClient} from '@/services/ApiClient';

/**
 * User state
 */
interface UserState {
	user: UserSignedIn|null,
	expiration: number,
}

const state: UserState = {
	user: null,
	expiration: 0,
};

const actions: ActionTree<UserState, any> = {
	updateInfo({commit}) {
		return useApiClient().getAccountService().fetchInfo()
			.then((user: UserInfo) => {
				commit('SET_INFO', user);
			})
			.catch((error: AxiosError) => {
				console.error(error);
				return Promise.reject(error);
			});
	},
	setJwt({commit}, user: UserSignedIn) {
		const now = new Date();
		const epoch = Math.round(now.getTime() / 1000);
		const jwt: JwtPayload = jwtDecode(user.token);
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
		return useApiClient().getAuthenticationService().signIn(credentials)
			.then((user: UserSignedIn) => {
				dispatch('setJwt', user);
				const sentryUser: Sentry.User = {
					username: user.username,
					ip_address: '{{auto}}',
				};
				if (user.email !== null) {
					sentryUser.email = user.email;
				}
				Sentry.setUser(sentryUser);
			})
			.catch((error: AxiosError) => {
				console.error(error);
				return Promise.reject(error);
			});
	},
	signOut({commit}) {
		Sentry.setUser(null);
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
		return state.user.state === AccountState.Unverified;
	},
	hasEmail(state: UserState): boolean {
		return state.user !== null && state.user.email !== null;
	},
	get(state: UserState): UserBase|null {
		if (state.user === null) {
			return null;
		}
		return {
			username: state.user.username,
			email: state.user.email,
			role: state.user.role,
			language: state.user.language,
		};
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
	getLanguage(state: UserState): string|null {
		if (state.user === null) {
			return null;
		}
		return state.user.language;
	},
	getRole(state: UserState): UserRole|null {
		if (state.user === null) {
			return null;
		}
		return state.user.role;
	},
	getRoleIndex(state: UserState): UserRoleIndex|null {
		if (state.user === null) {
			return null;
		}
		return Object.values(UserRole).indexOf(state.user.role) as UserRoleIndex;
	},
	getToken(state: UserState): string|null {
		if (state.user === null) {
			return null;
		}
		return state.user.token;
	},
	getExpiration(state: UserState): number {
		return state.expiration;
	},
};

const mutations: MutationTree<UserState> = {
	SET_INFO(state: UserState, data: UserInfo) {
		if (state.user === null) {
			return;
		}
		state.user = {...state.user, ...data};
	},
	SET_EXPIRATION(state: UserState, expiration: number) {
		state.expiration = expiration;
	},
	SIGN_IN(state: UserState, data: UserSignedIn) {
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
