<template>
	<Head>
		<title>{{ $t('core.account.verification.title') }}</title>
	</Head>
	<Card>
		<template #title>
			{{ $t('core.account.verification.title') }}
		</template>
		<div v-if='finished && success'>
			{{ $t('core.account.verification.messages.success') }}
			<vue-countdown
				v-slot='{ seconds }'
				:auto-start='true'
				:time='10_000'
				@end='signIn'
			>

				{{ $t('core.account.verification.messages.redirect', {countdown: seconds}) }}
			</vue-countdown>
		</div>
		<div v-else-if='finished && !success'>
			{{ $t('core.account.verification.messages.failure', {error: errorMsg}) }}
		</div>
	</Card>
</template>

<route lang='yaml'>
name: AccountVerification
meta:
  requiresAuth: false
</route>

<script lang='ts' setup>
import VueCountdown from '@chenfengyuan/vue-countdown';
import { ErrorResponse, UserSignedIn } from '@iqrf/iqrf-gateway-webapp-client/types';
import { Head } from '@unhead/vue/components';
import { AxiosError } from 'axios';
import { validate as uuidValidate, version as uuidVersion } from 'uuid';
import { onMounted, ref, Ref } from 'vue';
import { useRouter } from 'vue-router';

import Card from '@/components/Card.vue';
import { useUserStore } from '@/store/user';
import { useApiClient } from '@/services/ApiClient';

const props = defineProps({
	uuid: {
		type: String,
		required: true,
		validator(value: string): boolean {
			return uuidValidate(value) && uuidVersion(value) === 4;
		},
	},
});

const userStore = useUserStore();

const router = useRouter();
const finished: Ref<boolean>  = ref(false);
const success: Ref<boolean> = ref(false);
const userData: Ref<UserSignedIn | null> = ref(null);
const errorMsg: Ref<string> = ref('');

onMounted(() => {
	useApiClient().getAuthenticationService().verify(props.uuid)
		.then((user: UserSignedIn) => {
			userStore.setUserInfo(user);
			userStore.processJwt(user.token);
			userData.value = user;
			finished.value = true;
			success.value = true;
		})
		.catch((error: AxiosError) => {
			errorMsg.value = error.response ? (error.response.data as ErrorResponse).message : error.message;
			finished.value = true;
			success.value = false;
		});
});

function signIn(): void {
	if (userData.value === null) {
		return;
	}
	location.pathname = '/';
	router.push('/');
}
</script>
