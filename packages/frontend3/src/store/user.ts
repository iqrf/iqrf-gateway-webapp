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
	type UserCredentials,
	type UserInfo, type UserLanguage,
	type UserRole,
	UserSessionExpiration,
	type UserSignedIn,
} from '@iqrf/iqrf-gateway-webapp-client/types';
import { setUser, type User as SentryUser } from '@sentry/vue';
import { jwtDecode, type JwtPayload } from 'jwt-decode';
import { defineStore } from 'pinia';
import { type RouteLocationNormalizedLoaded } from 'vue-router';

import router from '@/router';
import { useApiClient } from '@/services/ApiClient';
import { useLocaleStore } from '@/store/locale';

/**
 * User store state
 */
interface UserState {
	/// User information
	user: UserSignedIn | null;
	/// User session expiration timestamp
	expiration: number;
	/// Requested session expiration
	requestedSessionExpiration: UserSessionExpiration | null;
}

export const useUserStore = defineStore('user', {
	state: (): UserState => ({
		user: null,
		expiration: 0,
		requestedSessionExpiration: null,
	}),
	actions: {
		/**
		 * Refreshes the user information
		 */
		async refreshUserInfo(): Promise<void> {
			const user: UserInfo = await useApiClient().getAccountService().getInfo();
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
		},
		/**
		 * Sets the user information
		 * @param {UserSignedIn} user User information
		 */
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
		/**
		 * Processes JWT token
		 * @param {string} token JWT token to process
		 */
		processJwt(token: string): void {
			const jwt: JwtPayload = jwtDecode(token);
			if (jwt.exp === undefined) {
				throw new Error('Expiration missing in JWT token.');
			}
			if (jwt.iat === undefined) {
				throw new Error('Token issue timestamp missing in JWT token.');
			}
			const now: Date = new Date();
			const epoch: number = Math.round(now.getTime() / 1_000);
			const diff: number = epoch - jwt.iat;
			this.expiration = jwt.exp + diff;
		},
		/**
		 * Sets the requested session expiration
		 * @param {UserSessionExpiration} expiration Requested session expiration
		 */
		setRequestedExpiration(expiration: UserSessionExpiration): void {
			this.requestedSessionExpiration = expiration;
		},
		/**
		 * Refreshes the user session
		 */
		async refreshSession(): Promise<void> {
			try {
				const user: UserSignedIn = await useApiClient().getAccountService().refreshToken();
				this.processJwt(user.token);
				this.setUserInfo(user);
			} catch (error) {
				console.error(error);
				throw error;
			}
		},
		async signIn(credentials: UserCredentials): Promise<void> {
			try {
				const user: UserSignedIn = await useApiClient().getAccountService().signIn(credentials);
				this.processSignInResponse(user);
				this.setRequestedExpiration(credentials.expiration ?? UserSessionExpiration.Default);
			} catch (error) {
				console.error(error);
				throw error;
			}
		},
		/**
		 * Processes the sign in response
		 * @param {UserSignedIn} response Sign in response
		 */
		processSignInResponse(response: UserSignedIn): void {
			this.processJwt(response.token);
			this.setUserInfo(response);
			const localeStore = useLocaleStore();
			localeStore.setLocale(response.language);
		},
		/**
		 * Signs out the user
		 * @param {boolean} restoreLocation Restore location after sign in
		 */
		async signOut(restoreLocation: boolean = true): Promise<void> {
			this.expiration = 0;
			this.user = null;
			setUser(null);
			const currentRoute: RouteLocationNormalizedLoaded = router.currentRoute.value;
			await router.push({
				name: 'SignIn',
				// Prevent duplicate redirect to sign in page
				query: restoreLocation && currentRoute.name !== 'SignIn' && currentRoute.path !== '/' ? { redirect: currentRoute.path } : {},
			});
		},
	},
	getters: {
		/**
		 * Checks if the user is logged in
		 * @param {UserState} state User state
		 * @return {boolean} Is the user logged in?
		 */
		isLoggedIn(state: UserState): boolean {
			if (state.user === null) {
				return false;
			}
			const now = new Date();
			const epoch = Math.round(now.getTime() / 1_000);
			return state.expiration > epoch;
		},
		/**
		 * Checks if the user is verified
		 * @param {UserState} state User state
		 * @return {boolean} Is the user verified?
		 */
		isVerified(state: UserState): boolean {
			if (state.user === null) {
				return false;
			}
			return state.user.state === AccountState.Verified;
		},
		/**
		 * Returns the user ID
		 * @param {UserState} state User state
		 * @return {number|null} User ID
		 */
		getId(state: UserState): number | null {
			if (state.user === null) {
				return null;
			}
			return state.user.id;
		},
		/**
		 * Returns the user name
		 * @param {UserState} state User state
		 * @return {string|null} User name
		 */
		getName(state: UserState): string | null {
			if (state.user === null) {
				return null;
			}
			return state.user.username;
		},
		/**
		 * Checks if the user has email
		 * @param {UserState} state User state
		 * @return {boolean} Does the user have email?
		 */
		hasEmail(state: UserState): boolean {
			if (state.user === null) {
				return false;
			}
			return state.user.email !== null;
		},
		/**
		 * Returns the user email
		 * @param {UserState} state User state
		 * @return {string|null} User email
		 */
		getEmail(state: UserState): string | null {
			if (state.user === null) {
				return null;
			}
			return state.user.email;
		},
		/**
		 * Returns the user language
		 * @param {UserState} state User state
		 * @return {UserLanguage|null} User language
		 */
		getLanguage(state: UserState): UserLanguage | null {
			return state.user?.language ?? null;
		},
		/**
		 * Returns the user role
		 * @param {UserState} state User state
		 * @return {UserRole|null} User role
		 */
		getRole(state: UserState): UserRole | null {
			return state.user?.role ?? null;
		},
		/**
		 * Returns the user JWT token
		 * @param {UserState} state User state
		 * @return {string|null} JWT token
		 */
		getToken(state: UserState): string | null {
			return state.user?.token ?? null;
		},
		/**
		 * Returns the session expiration
		 * @param {UserState} state User state
		 * @return {number} Session expiration
		 */
		getExpiration(state: UserState): number {
			return state.expiration * 1_000;
		},
		/**
		 * Returns the requested session expiration
		 * @param {UserState} state User state
		 * @return {UserSessionExpiration} Requested session expiration
		 */
		getLastRequestedExpiration(state: UserState): UserSessionExpiration {
			if (state.requestedSessionExpiration === null) {
				return UserSessionExpiration.Default;
			}
			return state.requestedSessionExpiration;
		},
	},
	persist: true,
});
