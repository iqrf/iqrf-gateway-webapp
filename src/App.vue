<template>
	<div>
		<LoadingSpinner />
		<router-view v-if='installationChecked' />
		<DaemonModeModal />
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import InstallationService, {InstallationCheck} from './services/InstallationService';
import {AxiosError} from 'axios';
import DaemonModeModal from './components/DamonModeModal.vue';
import LoadingSpinner from './components/LoadingSpinner.vue';
import {mapGetters} from 'vuex';

@Component({
	components: {
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
