import { AccountState, UserCredentials, UserInfo, UserRole, UserSignedIn } from '@iqrf/iqrf-gateway-webapp-client';
import * as Sentry from '@sentry/vue';
import { AxiosError } from 'axios';
import { jwtDecode, JwtPayload } from 'jwt-decode';
import { defineStore } from 'pinia';

import { useApiClient } from '@/services/ApiClient';
import router from '@/router';
import { useLocaleStore } from './locale';


interface UserState {
	user: UserSignedIn | null;
	expiration: number;
}

export const useUserStore = defineStore('user', {
	state: (): UserState => ({
		user: null,
		expiration: 0,
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
			const sentryUser: Sentry.User = {
				id: user.id.toString(),
				username: user.username,
				ip_address: '{{auto}}',
			};
			if (user.email !== null) {
				sentryUser.email = user.email;
			}
			Sentry.setUser(sentryUser);
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
		signIn(credentials: UserCredentials): Promise<void> {
			return useApiClient().getAuthenticationService().signIn(credentials)
				.then(async (user: UserSignedIn): Promise<void> => {
					await this.processJwt(user.token);
					this.setUserInfo(user);
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
			Sentry.setUser(null);
			router.push('/sign/in');
			return Promise.resolve();
		}
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
			if (state.user === null)  {
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
	},
	persist: true
});
