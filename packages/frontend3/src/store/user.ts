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

import { AccountState, type UserCredentials, type UserInfo, type UserRole, UserSessionExpiration, type UserSignedIn } from '@iqrf/iqrf-gateway-webapp-client/types';
import { setUser, type User as SentryUser } from '@sentry/vue';
import { type AxiosError } from 'axios';
import { jwtDecode, type JwtPayload } from 'jwt-decode';
import { defineStore } from 'pinia';

import router from '@/router';
import { useApiClient } from '@/services/ApiClient';
import { useLocaleStore } from '@/store/locale';

interface UserState {
	user: UserSignedIn | null;
	expiration: number;
	requestedSessionExpiration: UserSessionExpiration | null;
}

export const useUserStore = defineStore('user', {
	state: (): UserState => ({
		user: null,
		expiration: 0,
		requestedSessionExpiration: null,
	}),
	actions: {
		async refreshUserInfo(): Promise<void> {
			return useApiClient().getAccountService().fetchInfo()
				.then((user: UserInfo): void => {
					if (this.user === null) {
						return;
					}
					this.user.id = user.id;
					this.user.username = user.username;
					this.user.email = user.email;
					if (this.user.language !== user.language) {
						const localeStore = useLocaleStore();
						this.user.language = user.language;
						localeStore.setLocale(user.language);
					}
					this.user.role = user.role;
					this.user.state = user.state;
				});
		},
		setUserInfo(user: UserSignedIn): void {
			this.user = user;
			const sentryUser: SentryUser = {
				id: user.id.toString(),
				username: user.username,
				ip_address: '{{auto}}',
			};
			if (user.email !== null) {
				sentryUser.email = user.email;
			}
			setUser(sentryUser);
		},
		processJwt(token: string): Promise<void> {
			const jwt: JwtPayload = jwtDecode(token);
			if (jwt.exp === undefined) {
				return Promise.reject(new Error('Expiration missing in JWT token.'));
			}
			if (jwt.iat === undefined) {
				return Promise.reject(new Error('Token issue timestamp missing in JWT token.'));
			}
			const now = new Date();
			const epoch = Math.round(now.getTime() / 1000);
			const diff = epoch - jwt.iat;
			this.expiration = jwt.exp + diff;
			return Promise.resolve();
		},
		setRequestedExpiration(expiration: UserSessionExpiration): void {
			this.requestedSessionExpiration = expiration;
		},
		signIn(credentials: UserCredentials): Promise<void> {
			return useApiClient().getAuthenticationService().signIn(credentials)
				.then(async (user: UserSignedIn): Promise<void> => {
					await this.processJwt(user.token);
					this.setUserInfo(user);
					this.setRequestedExpiration(credentials.expiration ?? UserSessionExpiration.Default);
					const localeStore = useLocaleStore();
					localeStore.setLocale(user.language);
				})
				.catch((error: AxiosError) => {
					console.error(error);
					return Promise.reject(error);
				});
		},
		signOut(): Promise<void> {
			this.expiration = 0;
			this.user = null;
			setUser(null);
			router.push('/sign/in');
			return Promise.resolve();
		},
	},
	getters: {
		isLoggedIn(state: UserState): boolean {
			if (state.user === null) {
				return false;
			}
			const now = new Date();
			const epoch = Math.round(now.getTime() / 1000);
			return state.expiration > epoch;
		},
		isVerified(state: UserState): boolean {
			if (state.user === null) {
				return false;
			}
			return state.user.state === AccountState.Verified;
		},
		getId(state: UserState): number | null {
			if (state.user === null) {
				return null;
			}
			return state.user.id;
		},
		getName(state: UserState): string | null {
			if (state.user === null) {
				return null;
			}
			return state.user.username;
		},
		hasEmail(state: UserState): boolean {
			if (state.user === null) {
				return false;
			}
			return state.user.email !== null;
		},
		getEmail(state: UserState): string | null {
			if (state.user === null) {
				return null;
			}
			return state.user.email;
		},
		getRole(state: UserState): UserRole | null {
			if (state.user === null) {
				return null;
			}
			return state.user.role;
		},
		getToken(state: UserState): string | null {
			if (state.user === null) {
				return null;
			}
			return state.user.token;
		},
		getExpiration(state: UserState): number {
			return state.expiration;
		},
		getLastRequestedExpiration(state: UserState): UserSessionExpiration {
			if (state.requestedSessionExpiration === null) {
				return UserSessionExpiration.Default;
			}
			return state.requestedSessionExpiration;
		},
	},
	persist: true,
});
