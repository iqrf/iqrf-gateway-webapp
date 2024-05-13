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
	<ModalWindow
		v-model='show'
	>
		<Card>
			<template #title>
				{{ $t('components.status.sessionExpiration.title') }}
			</template>
			{{ $t('components.status.sessionExpiration.prompt') }}
			<template #actions>
				<v-spacer />
				<v-btn
					color='primary'
					variant='elevated'
					@click='renewSession'
				>
					{{ `${$t('components.status.sessionExpiration.renew')} (${countdown})` }}
				</v-btn>
			</template>
		</Card>
	</ModalWindow>
</template>

<script lang='ts' setup>
import { type UserSignedIn } from '@iqrf/iqrf-gateway-webapp-client/types';
import { onBeforeUnmount, onMounted, ref, type Ref  } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';

import Card from '@/components/Card.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import { useApiClient } from '@/services/ApiClient';
import { useUserStore } from '@/store/user';

const i18n = useI18n();
const userStore = useUserStore();
const show: Ref<boolean> = ref(false);
const expirationWarningTimeout: Ref<number> = ref(0);
const logoutTimeout: Ref<number> = ref(0);
const logoutTimerInterval: Ref<number> = ref(0);
const countdown: Ref<number> = ref(0);
const router = useRouter();

onMounted(() => {
	setup();
});

onBeforeUnmount(() => {
	clear();
});

async function setup(): Promise<void> {
	let expiration: number = userStore.getExpiration * 1000;
	const now = new Date().getTime();
	if ((expiration - now) < 300000) {
		await renewSession();
		expiration = (userStore.getExpiration * 1000);
	}
	const timeout = expiration - now;
	const warning = timeout - 60000;
	expirationWarningTimeout.value = window.setTimeout(() => {
		logoutTimerInterval.value = window.setInterval((expiration: number) => {
			countdown.value = Math.floor((expiration - Date.now()) / 1000);
		}, 300, expiration);
		show.value = true;
	}, warning);
	logoutTimeout.value = window.setTimeout(async () => {
		show.value = false;
		await userStore.signOut()
			.then(async () => {
				await router.push({ path: '/sign/in', query: { redirect: router.currentRoute.value.path } });
				toast.warning(
					i18n.t('auth.sign.out.expired').toString(),
				);
			});
	}, timeout);
}

async function renewSession(): Promise<void> {
	await useApiClient().getAuthenticationService().refreshToken()
		.then((rsp: UserSignedIn) => {
			userStore.processJwt(rsp.token)
				.then(() => {
					show.value = false;
					clear();
					setup();
				})
				.catch(() => toast.error(i18n.t('components.status.sessionExpiration.failed').toString()));
		})
		.catch(() => (toast.error(i18n.t('components.status.sessionExpiration.failed').toString())));
}

function clear(): void {
	window.clearTimeout(logoutTimeout.value);
	window.clearTimeout(expirationWarningTimeout.value);
	window.clearInterval(logoutTimerInterval.value);
}

</script>
