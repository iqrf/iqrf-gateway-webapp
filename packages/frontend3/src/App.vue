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
			<div v-if='isChecked'>
				<router-view />
				<SessionExpirationDialog v-if='isLoggedIn' />
			</div>
			<InstallationChecker v-else />
		</v-app>
	</v-theme-provider>
</template>

<script lang='ts' setup>
import { type UserLanguage } from '@iqrf/iqrf-gateway-webapp-client/types';
import { storeToRefs } from 'pinia';
import { useHead } from 'unhead';
import { onBeforeMount, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useTheme } from 'vuetify';

import InstallationChecker from '@/components/layout/InstallationChecker.vue';
import SessionExpirationDialog from '@/components/SessionExpirationDialog.vue';
import { useDaemonStore } from '@/store/daemonSocket';
import { useInstallStore } from '@/store/install';
import { useLocaleStore } from '@/store/locale';
import { useUserStore } from '@/store/user';

const i18n = useI18n();
const theme = useTheme();

const daemonStore = useDaemonStore();
const installStore = useInstallStore();
const localeStore = useLocaleStore();
const userStore = useUserStore();

const { isConnected } = storeToRefs(daemonStore);
const { isChecked } = storeToRefs(installStore);
const { getLocale: locale } = storeToRefs(localeStore);
const { isLoggedIn } = storeToRefs(userStore);

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
	if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
		theme.global.name.value = 'dark';
	} else {
		theme.global.name.value = 'light';
	}
	localeStore.setLocale(locale.value);
	setHeadOptions(locale.value);
});

watch(isConnected, (newVal) => {
	if (newVal) {
		daemonStore.sendVersionRequest();
	}
});

watch(locale, setHeadOptions);
</script>
