<!--
Copyright 2023-2025 IQRF Tech s.r.o.
Copyright 2023-2025 MICRORISC s.r.o.

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
	<div>
		<Head>
			<title>{{ $t('components.account.verification.title') }}</title>
		</Head>
		<ICard>
			<template #title>
				{{ $t('components.account.verification.title') }}
			</template>
			<v-skeleton-loader
				class='input-skeleton-loader'
				:loading='componentState === ComponentState.Loading'
				type='heading, text'
			>
				<v-responsive>
					<v-alert
						v-if='componentState === ComponentState.Success'
						type='success'
					>
						{{ $t('components.account.verification.messages.success') }}
						<vue-countdown
							v-slot='{ seconds }'
							:auto-start='true'
							:time='10_000'
							@end='signIn'
						>
							{{ $t('components.account.verification.messages.redirect', { countdown: seconds }) }}
						</vue-countdown>
					</v-alert>
					<v-alert
						v-else-if='componentState === ComponentState.Error'
						:text='$t("components.account.verification.messages.failure")'
						type='error'
					/>
					<v-alert
						v-else-if='componentState === ComponentState.Expired'
						:text='$t("components.account.verification.messages.alreadyVerified")'
						type='error'
					/>
					<v-alert
						v-else-if='componentState === ComponentState.NotFound'
						:text='$t("components.account.verification.messages.notFound")'
						type='error'
					/>
				</v-responsive>
			</v-skeleton-loader>
		</ICard>
	</div>
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
import { UserSignedIn } from '@iqrf/iqrf-gateway-webapp-client/types';
import { ICard } from '@iqrf/iqrf-vue-ui';
import { Head } from '@unhead/vue/components';
import { AxiosError } from 'axios';
import { validate as uuidValidate, version as uuidVersion } from 'uuid';
import { onMounted, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';

import { useApiClient } from '@/services/ApiClient';
import { useUserStore } from '@/store/user';
import { ComponentState } from '@/types/ComponentState';

/// Component props
const componentProps = defineProps({
	uuid: {
		type: String,
		required: true,
		validator(value: string): boolean {
			return uuidValidate(value) && uuidVersion(value) === 4;
		},
	},
});
/// Component state
const componentState: Ref<ComponentState> = ref(ComponentState.Loading);
/// User store
const userStore = useUserStore();
/// Router instance
const router = useRouter();
/// User data
const userData: Ref<UserSignedIn | null> = ref(null);
/// Error message
const errorMsg: Ref<string> = ref('');
/// Internationalization instance
const i18n = useI18n();

onMounted(async () => {
	componentState.value = ComponentState.Loading;
	try {
		userData.value = await useApiClient().getAccountService().verifyEmail(componentProps.uuid);
		userStore.processSignInResponse(userData.value);
		await userStore.refreshUserPreferences();
		componentState.value = ComponentState.Success;
	} catch (error) {
		if (!(error instanceof AxiosError)) {
			componentState.value = ComponentState.Error;
			errorMsg.value = i18n.t('components.account.verification.messages.failure');
			return;
		}
		switch (error?.response?.status) {
			case 400:
				componentState.value = ComponentState.Expired;
				errorMsg.value = i18n.t('components.account.verification.messages.alreadyVerified');
				break;
			case 404:
				componentState.value = ComponentState.NotFound;
				errorMsg.value = i18n.t('components.account.verification.messages.notFound');
				break;
			default:
				componentState.value = ComponentState.Error;
				errorMsg.value = i18n.t('components.account.verification.messages.failure');
				break;
		}
		toast.error(errorMsg.value);
	}
});

/**
 * Redirects the user to the home page
 */
async function signIn(): Promise<void> {
	if (userData.value === null) {
		return;
	}
	await router.push('/');
}
</script>
