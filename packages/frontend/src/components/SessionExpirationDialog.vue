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
	<ModalWindow
		v-model='show'
	>
		<Card header-color='warning'>
			<template #title>
				{{ $t('components.status.sessionExpiration.title') }}
			</template>
			{{ $t('components.status.sessionExpiration.prompt') }}
			<template #actions>
				<CardActionBtn
					color='warning'
					:icon='mdiRefresh'
					:text='`${$t("components.status.sessionExpiration.renew") } (${ countdown })`'
					@click='renewSession'
				/>
				<v-spacer />
				<CardActionBtn
					:action='Action.Cancel'
					@click='close'
				/>
			</template>
		</Card>
	</ModalWindow>
</template>

<script lang='ts' setup>
import { type UserSignedIn } from '@iqrf/iqrf-gateway-webapp-client/types';
import { mdiRefresh } from '@mdi/js';
import { storeToRefs } from 'pinia';
import { onBeforeUnmount, onMounted, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';

import Card from '@/components/layout/card/Card.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import { useApiClient } from '@/services/ApiClient';
import { useUserStore } from '@/store/user';
import { Action } from '@/types/Action';

const i18n = useI18n();
const userStore = useUserStore();
const show: Ref<boolean> = ref(false);
const expirationWarningTimeout: Ref<number> = ref(0);
const logoutTimeout: Ref<number> = ref(0);
const logoutTimerInterval: Ref<number> = ref(0);
const countdown: Ref<number> = ref(0);
const { getExpiration: expiration } = storeToRefs(userStore);
const router = useRouter();

onMounted(async () => await setup());

onBeforeUnmount(() => clear());

/**
 * Close the dialog
 */
function close(): void {
	show.value = false;
}

/**
 * Set-up the session expiration dialog
 */
async function setup(): Promise<void> {
	const now = Date.now();
	let timeout = expiration.value - now;
	if (timeout > 0 && timeout < 300_000) {
		await renewSession();
		timeout = expiration.value - now;
	}
	const warning = timeout - 300_000;
	expirationWarningTimeout.value = window.setTimeout(() => {
		logoutTimerInterval.value = window.setInterval(() => {
			countdown.value = Math.floor((expiration.value - Date.now()) / 1_000);
		}, 300, expiration);
		show.value = true;
	}, warning);
	logoutTimeout.value = window.setTimeout(async () => {
		close();
		await userStore.signOut();
		await router.push({ path: '/sign/in', query: { redirect: router.currentRoute.value.path } });
		toast.warning(i18n.t('components.auth.sign.out.expired'));
	}, timeout);
}

/**
 * Renew the session
 */
async function renewSession(): Promise<void> {
	try {
		const rsp: UserSignedIn = await useApiClient().getAccountService().refreshToken();
		await userStore.processJwt(rsp.token);
		close();
		clear();
		await setup();
	} catch (error) {
		console.error(error);
		toast.error(i18n.t('components.status.sessionExpiration.failed').toString());
	}
}

/**
 * Clear all timeouts and intervals
 */
function clear(): void {
	window.clearTimeout(logoutTimeout.value);
	window.clearTimeout(expirationWarningTimeout.value);
	window.clearInterval(logoutTimerInterval.value);
}

</script>
