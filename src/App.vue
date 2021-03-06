<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

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
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import InstallationService, {InstallationCheck} from './services/InstallationService';
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
	},
	computed: {
		...mapGetters({
			installationChecked: 'installation/isChecked',
		}),
	},
})

export default class App extends Vue {
	/**
	 * Main app watch function
	 */
	private unwatch: CallableFunction = () => {return;}

	/**
	 * Vue lifecycle hook before created
	 */
	beforeCreate(): void {
		InstallationService.check()
			.then((check: InstallationCheck) => {
				const installUrl: boolean = this.$route.path.startsWith('/install/');
				if (!check.phpModules.allExtensionsLoaded) {
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
			})
			.catch((error: AxiosError) => {
				console.error(error);
			});
	}

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		this.unwatch = this.$store.watch(
			(state, getter) => getter.isSocketConnected,
			(newVal, oldVal) => {
				if (!oldVal && newVal) {
					this.$store.dispatch('getVersion');
					this.unwatch();
				}
			}
		);
	}
}
</script>
