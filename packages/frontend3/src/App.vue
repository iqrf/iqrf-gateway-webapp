<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
	<v-theme-provider :theme='theme.global.name.value'>
		<v-app>
			<div v-if='componentState === ComponentState.Ready'>
				<router-view v-if='isChecked' />
				<SessionExpirationDialog v-if='isLoggedIn' />
			</div>
			<LoadingScreen v-else-if='componentState === ComponentState.Loading' />
		</v-app>
	</v-theme-provider>
</template>

<script lang='ts' setup>
import {
	type UserLanguage,
	type InstallationChecks,
} from '@iqrf/iqrf-gateway-webapp-client/types';
import { storeToRefs } from 'pinia';
import { useHead } from 'unhead';
import { onBeforeMount, type Ref, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useTheme } from 'vuetify';

import LoadingScreen from '@/components/layout/LoadingScreen.vue';
import SessionExpirationDialog from '@/components/SessionExpirationDialog.vue';
import router from '@/router';
import { useApiClient } from '@/services/ApiClient';
import { useDaemonStore } from '@/store/daemonSocket';
import { useFeatureStore } from '@/store/features';
import { useGatewayStore } from '@/store/gateway';
import { useInstallStore } from '@/store/install';
import { useLocaleStore } from '@/store/locale';
import { useRepositoryStore } from '@/store/repository';
import { useUserStore } from '@/store/user';
import { ComponentState } from '@/types/ComponentState';

const i18n = useI18n();
const theme = useTheme();

const daemonStore = useDaemonStore();
const { isConnected } = storeToRefs(daemonStore);

const featureStore = useFeatureStore();
const gatewayStore = useGatewayStore();

const installStore = useInstallStore();
const { isChecked } = storeToRefs(installStore);

const repositoryStore = useRepositoryStore();

const userStore = useUserStore();
const { isLoggedIn } = storeToRefs(userStore);

const localeStore = useLocaleStore();
const { getLocale: locale } = storeToRefs(localeStore);

const componentState: Ref<ComponentState> = ref(ComponentState.Created);

/**
 * Set HTML head options
 * @param {UserLanguage} newLocale New locale to set
 */
function setHeadOptions(newLocale: UserLanguage): void {
	useHead({
		htmlAttrs: {
			lang: newLocale.toString(),
		},
		titleTemplate: '%s %separator %siteName',
		templateParams: {
			siteName: i18n.t('title').toString(),
			separator: '|',
		},
	});
}

onBeforeMount(async () => {
	componentState.value = ComponentState.Loading;
	if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
		theme.global.name.value = 'dark';
	} else {
		theme.global.name.value = 'light';
	}
	localeStore.setLocale(locale.value);
	setHeadOptions(locale.value);
	await featureStore.fetch();
	try {
		const check: InstallationChecks = await useApiClient().getInstallationService().check();
		componentState.value = ComponentState.Ready;
		const isInstallUrl: boolean = router.currentRoute.value.path.startsWith('/install/');
		const errorUrl: boolean = router.currentRoute.value.path.startsWith('/install/error/');
		installStore.populateSteps();
		if (check.hasUsers !== undefined) {
			installStore.setUsers(check.hasUsers);
		}
		if (check.dependencies.length !== 0) {
			installStore.setMissingDependencies(check.dependencies);
			installStore.setChecked();
			await router.push({
				name: 'MissingDependency',
			});
		} else if (!check.phpModules.allExtensionsLoaded) {
			if (check.phpModules.missing !== undefined) {
				installStore.setMissingExtensions(check.phpModules.missing);
			}
			installStore.setChecked();
			await router.push({
				name: 'MissingExtension',
			});
		} else if (check.sudo !== undefined && (!check.sudo.exists || !check.sudo.userSudo)) {
			// TODO: sudo error
		} else if (!check.allMigrationsExecuted) {
			installStore.setChecked();
			await router.push({ name: 'MissingMigration' });
		} else if (!check.hasUsers && !isInstallUrl) {
			installStore.setChecked();
			await router.push({ name: 'InstallDisambiguation' });
		} else if (check.hasUsers && (isInstallUrl || errorUrl)) {
			installStore.setChecked();
			await router.push('/sign/in');
		}
		if (isLoggedIn.value) {
			await repositoryStore.fetch();
			await gatewayStore.fetchInfo();
		}
		installStore.setChecked();
	} catch (error) {
		// SPINNER HIDE
		console.error(error);
	}
});

watch(isConnected, (newVal) => {
	if (newVal) {
		daemonStore.sendVersionRequest();
	}
});

watch(locale, setHeadOptions);
</script>
