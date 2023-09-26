<template>
	<v-dialog
		v-model='show'
		persistent
		width='60%'
		no-click-animation
	>
		<Card>
			<template #title>
				{{ $t('status.sessionExpiration.title') }}
			</template>
			{{ $t('status.sessionExpiration.prompt') }}
			<template #actions>
				<v-spacer />
				<v-btn
					color='primary'
					variant='elevated'
					@click='renewSession'
				>
					{{ `${$t('status.sessionExpiration.renew')} (${countdown})` }}
				</v-btn>
			</template>
		</Card>
	</v-dialog>
</template>

<script lang='ts' setup>
import { UserSignedIn } from '@iqrf/iqrf-gateway-webapp-client/types';
import { onBeforeUnmount, onMounted, ref, Ref  } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';

import { useApiClient } from '@/services/ApiClient';
import { useUserStore } from '@/store/user';

import Card from '@/components/Card.vue';

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
				await router.push({path: '/sign/in', query: {redirect: router.currentRoute.value.path}});
				toast.warning(
					i18n.t('core.sign.out.expired').toString()
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
				.catch(() => toast.error(i18n.t('core.sessionExpiration.failed').toString()));
		})
		.catch(() => (toast.error(i18n.t('core.sessionExpiration.failed').toString())));
}

function clear(): void {
	window.clearTimeout(logoutTimeout.value);
	window.clearTimeout(expirationWarningTimeout.value);
	window.clearInterval(logoutTimerInterval.value);
}

</script>