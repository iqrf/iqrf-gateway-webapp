<template>
	<v-app dark>
		<router-view v-if='isChecked' />
		<SessionExpirationDialog v-if='isLoggedIn' />
	</v-app>
</template>

<script lang='ts' setup>
import type { InstallationChecks } from '@iqrf/iqrf-gateway-webapp-client/types';
import { AxiosError } from 'axios';
import { onBeforeMount, watch } from 'vue';
import { storeToRefs } from 'pinia';
import router from '@/router';

import { useApiClient } from '@/services/ApiClient';
import { useDaemonStore } from '@/store/daemonSocket';
import { useFeatureStore } from '@/store/features';
import { useInstallStore } from '@/store/install';
import { useUserStore } from '@/store/user';

import SessionExpirationDialog from '@/components/SessionExpirationDialog.vue';

const daemonStore = useDaemonStore();
const {isConnected} = storeToRefs(daemonStore);

const featureStore = useFeatureStore();

const installStore = useInstallStore();
const {isChecked} = storeToRefs(installStore);

const userStore = useUserStore();
const {isLoggedIn} = storeToRefs(userStore);

onBeforeMount(async () => {
	await featureStore.fetch();
	await useApiClient().getInstallationService().check()
		.then((check: InstallationChecks): void => {
			const isInstallUrl: boolean = router.currentRoute.value.path.startsWith('/install/');
			const errorUrl: boolean = router.currentRoute.value.path.startsWith('/install/error/');
			installStore.populateSteps();
			if (check.hasUsers !== undefined) {
				installStore.setUsers(check.hasUsers);
			}
			if (check.dependencies.length !== 0) {
				installStore.setMissingDependencies(check.dependencies);
				installStore.setChecked();
				router.push({
					name: 'MissingDependency',
				});
			} else if (!check.phpModules.allExtensionsLoaded) {
				if (check.phpModules.missing !== undefined) {
					installStore.setMissingExtensions(check.phpModules.missing);
				}
				installStore.setChecked();
				router.push({
					name: 'MissingExtension',
				});
			} else if (check.sudo !== undefined && (!check.sudo.exists || !check.sudo.userSudo)) {
				// TODO: sudo error
			} else if (!check.allMigrationsExecuted) {
				installStore.setChecked();
				router.push({name: 'MissingMigration'});
			} else if (!check.hasUsers && !isInstallUrl) {
				installStore.setChecked();
				router.push({name: 'InstallDisambiguation'});
			} else if (check.hasUsers && (isInstallUrl || errorUrl)) {
				installStore.setChecked();
				router.push('/sign/in');
			}
			if (isLoggedIn.value) {
				// TODO: GET REPOSITORY
				// TODO: GET GATEWAY INFO
			}
			installStore.setChecked();
		})
		.catch((error: AxiosError) => {
			// SPINNER HIDE
			console.error(error);
		});
});

watch(isConnected, (newVal) => {
	if (newVal) {
		daemonStore.sendVersionRequest();
	}
});

</script>
