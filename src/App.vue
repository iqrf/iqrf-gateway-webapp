<!--
Copyright 2017-2023 IQRF Tech s.r.o.
Copyright 2019-2023 MICRORISC s.r.o.

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
	<div>
		<Blocking />
		<LoadingSpinner />
		<router-view v-if='installationChecked' />
		<DaemonModeModal />
		<SessionExpirationModal v-if='loggedIn' />
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import InstallationService, {InstallationCheck} from './services/InstallationService';
import SessionExpirationModal from '@/components/SessionExpirationModal.vue';
import {AxiosError} from 'axios';

import Blocking from './components/Blocking.vue';
import DaemonModeModal from './components/DamonModeModal.vue';
import LoadingSpinner from './components/LoadingSpinner.vue';
import {mapGetters} from 'vuex';

@Component({
	components: {
		Blocking,
		DaemonModeModal,
		LoadingSpinner,
		SessionExpirationModal,
	},
	computed: {
		...mapGetters({
			installationChecked: 'installation/isChecked',
			loggedIn: 'user/isLoggedIn',
		}),
	},
})

export default class App extends Vue {
	/**
	 * Main app watch function
	 */
	private unwatch: CallableFunction = () => {return;};

	/**
	 * Vue lifecycle hook before created
	 */
	async beforeCreate(): Promise<void> {
		await this.$store.dispatch(
			'spinner/show',
			{timeout: null, text: this.$t('install.messages.check').toString()}
		);
		await this.$store.dispatch('features/fetch');
		await InstallationService.check()
			.then((check: InstallationCheck) => {
				const installUrl: boolean = this.$route.path.startsWith('/install/');
				if (check.dependencies.length !== 0) {
					this.$router.push({
						name: 'missing-dependency',
						params: {
							json: JSON.stringify(check.dependencies),
						},
					});
				} else if (!check.phpModules.allExtensionsLoaded) {
					this.$router.push({
						name: 'missing-extension',
						params: {
							extensionString: check.phpModules.missing!.extensions.join(', '),
							packageString: check.phpModules.missing?.packages !== undefined ? check.phpModules.missing?.packages.join(' ') : '',
						}
					});
				} else if (check.sudo !== undefined && (!check.sudo.exists || !check.sudo.userSudo)) {
					this.$router.push({
						name: 'sudo-error',
						params: {
							user: check.sudo.user,
							exists: check.sudo.exists.toString(),
							userSudo: check.sudo.userSudo.toString(),
						}
					});
				} else if (!check.allMigrationsExecuted) {
					this.$router.push('/install/error/missing-migration');
				} else if (!check.hasUsers && !installUrl) {
					this.$router.push('/install/');
				} else if (check.hasUsers && installUrl) {
					this.$router.push('/sign/in/');
				}
				if (this.$store.getters['user/isLoggedIn']) {
					this.$store.dispatch('repository/get');
				}
			})
			.catch((error: AxiosError) => {
				this.$store.dispatch('spinner/hide');
				console.error(error);
			});
	}

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		this.unwatch = this.$store.watch(
			(state, getter) => getter['daemonClient/isConnected'],
			(newVal, oldVal) => {
				if (!oldVal && newVal) {
					this.$store.dispatch('daemonClient/getVersion');
					this.unwatch();
				}
			}
		);
	}

}
</script>
