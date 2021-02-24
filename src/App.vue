<template>
	<div>
		<router-view />
		<DaemonModeModal />
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import InstallationService, {InstallationCheck} from './services/InstallationService';
import {AxiosError} from 'axios';
import DaemonModeModal from './components/DamonModeModal.vue';

@Component({
	components: {
		DaemonModeModal,
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
		this.$store.commit('spinner/SHOW');
		InstallationService.check()
			.then((check: InstallationCheck) => {
				const installUrl: boolean = this.$route.path.startsWith('/install/');
				if (!check.sudo.exists || !check.sudo.webappSudo) {
					this.$router.push({
						name: 'sudo-error',
						params: {
							exists: check.sudo.exists.toString(),
							webappSudo: check.sudo.webappSudo.toString()
						}
					});
				} else if (!check.phpModules.allExtensionsLoaded) {
					this.$router.push({
						name: 'missing-extension',
						params: {
							extensionString: check.phpModules.missing!.join(', ')
						}
					});
				} else if (!check.allMigrationsExecuted) {
					this.$router.push('/install/error/missing-migration');
				} else if (!check.hasUsers && !installUrl) {
					this.$router.push('/install/');
				} else if (check.hasUsers && installUrl) {
					this.$router.push('/sign/in/');
				}
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => {
				this.$store.commit('spinner/HIDE');
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
