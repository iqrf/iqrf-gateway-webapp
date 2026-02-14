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
	<v-theme-provider :theme='theme'>
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
import { Language } from '@iqrf/iqrf-ui-common-types';
import { storeToRefs } from 'pinia';
import { useHead } from 'unhead';
import { onBeforeMount, watch } from 'vue';
import { useI18n } from 'vue-i18n';

import InstallationChecker from '@/components/layout/InstallationChecker.vue';
import SessionExpirationDialog from '@/components/SessionExpirationDialog.vue';
import head from '@/plugins/head';
import { useDaemonStore } from '@/store/daemonSocket';
import { useInstallStore } from '@/store/install';
import { useLocaleStore } from '@/store/locale';
import { useThemeStore } from '@/store/theme';
import { useUserStore } from '@/store/user';

const i18n = useI18n();

const daemonStore = useDaemonStore();
const { isConnected } = storeToRefs(daemonStore);

const installStore = useInstallStore();
const { isChecked } = storeToRefs(installStore);

const userStore = useUserStore();
const { isLoggedIn } = storeToRefs(userStore);

const localeStore = useLocaleStore();
const { getLocale: locale } = storeToRefs(localeStore);

const themeStore = useThemeStore();
const { getTheme: theme } = storeToRefs(themeStore);

/**
 * Set HTML head options
 * @param {Language} newLocale New locale to set
 */
function setHeadOptions(newLocale: Language): void {
	useHead(head, {
		htmlAttrs: {
			lang: newLocale.toString(),
		},
		titleTemplate: '%s %separator %siteName',
		templateParams: {
			siteName: i18n.t('title'),
			separator: '|',
		},
	});
}

onBeforeMount(async () => {
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
