<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software,
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied
See the License for the specific language governing permissions and
limitations under the License.
-->
<template>
	<v-dialog
		v-model='show'
		width='50%'
		persistent
		no-click-animation
	>
		<v-card>
			<v-card-title>{{ $t('sessionExpiration.title') }}</v-card-title>
			<v-card-text>
				{{ $t('sessionExpiration.prompt') }}
			</v-card-text>
			<v-card-actions>
				<v-spacer />
				<v-btn
					color='primary'
					@click='renewSession'
				>
					{{ `${$t('sessionExpiration.renew')} (${countdown})` }}
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>

<script lang='ts'>
import {UserSignedIn} from '@iqrf/iqrf-gateway-webapp-client/types';
import {Component} from 'vue-property-decorator';
import ModalBase from './ModalBase.vue';

import {extendedErrorToast} from '@/helpers/errorToast';
import {useApiClient} from '@/services/ApiClient';

/**
 * Session expiration dialog component
 */
@Component
export default class SessionExpirationModal extends ModalBase {
	/**
	 * @var {number} expirationWarningTimeout Timeout to expiration warning visibility
	 */
	private expirationWarningTimeout = 0;

	/**
	 * @var {number} logoutTimeout Timeout to logout
	 */
	private logoutTimeout = 0;

	/**
	 * @var {number} logoutTimerInterval Logout timer interval
	 */
	private logoutTimerInterval = 0;

	/**
	 * @var {number} countdown Countdown to logout
	 */
	private countdown = 0;

	/**
	 * Sets up session expiration
	 */
	mounted(): void {
		this.setup();
	}

	/**
	 * Clears expirations
	 */
	beforeDestroy(): void {
		this.clear();
	}

	/**
	 * Initializes session expiration warning and logout timeouts
	 */
	private async setup(): Promise<void> {
		let expiration: number = (this.$store.getters['user/getExpiration'] * 1000);
		const now = new Date().getTime();
		if ((expiration - now) < 300000) {
			await this.renewSession();
			expiration = (this.$store.getters['user/getExpiration'] * 1000);
		}
		const timeout = expiration - now;
		const warning = timeout - 60000;
		this.expirationWarningTimeout = window.setTimeout(() => {
			this.logoutTimerInterval = window.setInterval((expiration: number) => {
				this.countdown = Math.floor((expiration - Date.now()) / 1000);
			}, 300, expiration);
			this.openModal();
		}, warning);
		this.logoutTimeout = window.setTimeout(async () => {
			this.closeModal();
			await this.$store.dispatch('user/signOut')
				.then(async () => {
					await this.$router.push({path: '/sign/in', query: {redirect: this.$router.currentRoute.path}});
					this.$toast.warning(
						this.$t('core.sign.out.expired').toString(),
					);
				});
		}, timeout);
	}

	/**
	 * Renews the session by refreshing jwt token and setting up new expiration
	 */
	private async renewSession(): Promise<void> {
		await useApiClient().getAccountService().refreshToken()
			.then((rsp: UserSignedIn) => {
				this.$store.dispatch('user/setJwt', rsp)
					.then(() => {
						this.closeModal();
						this.clear();
						this.setup();
					})
					.catch((error) => extendedErrorToast(error, 'sessionExpiration.failed'));
			})
			.catch((error) => extendedErrorToast(error, 'sessionExpiration.failed'));
	}

	private clear(): void {
		window.clearTimeout(this.logoutTimeout);
		window.clearTimeout(this.expirationWarningTimeout);
		window.clearInterval(this.logoutTimerInterval);
	}
}
</script>
