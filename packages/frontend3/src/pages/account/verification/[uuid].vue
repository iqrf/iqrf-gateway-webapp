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

<route>
{
	"name": "AccountVerification",
	"meta": {
		"requiresAuth": false,
	},
}
</route>

<script lang='ts' setup>
import VueCountdown from '@chenfengyuan/vue-countdown';
import { type ErrorResponse, type UserSignedIn } from '@iqrf/iqrf-gateway-webapp-client/types';
import { Head } from '@unhead/vue/components';
import { AxiosError } from 'axios';
import { validate as uuidValidate, version as uuidVersion } from 'uuid';
import { onMounted, ref, type Ref } from 'vue';
import { useRouter } from 'vue-router';

import Card from '@/components/layout/card/Card.vue';
import { useApiClient } from '@/services/ApiClient';
import { useUserStore } from '@/store/user';

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
const finished: Ref<boolean> = ref(false);
const success: Ref<boolean> = ref(false);
const userData: Ref<UserSignedIn | null> = ref(null);
const errorMsg: Ref<string> = ref('');

onMounted(async (): Promise<void> => {
	try {
		const user: UserSignedIn = await useApiClient().getAuthenticationService().verify(props.uuid);
		userStore.setUserInfo(user);
		await userStore.processJwt(user.token);
		userData.value = user;
		finished.value = true;
		success.value = true;
	} catch (error) {
		console.error(error);
		if (error instanceof AxiosError) {
			errorMsg.value = error.response ? (error.response.data as ErrorResponse).message : error.message;
		}
		finished.value = true;
		success.value = false;
	}
});

function signIn(): void {
	if (userData.value === null) {
		return;
	}
	location.pathname = '/';
	router.push('/');
}
</script>
