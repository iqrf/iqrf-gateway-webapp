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
	<Card>
		<template #title>
			{{ $t('account.recovery.title') }}
		</template>
		<p>
			{{ $t('account.recovery.confirmation.prompt') }}
		</p>
		<v-form ref='form' class='mt-4' @submit.prevent='onSubmit'>
			<PasswordInput
				v-model='password'
				:label='$t("user.password")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("user.validation.password")),
				]'
				required
				:prepend-inner-icon='mdiKey'
			/>
			<v-btn
				color='primary'
				type='submit'
				:prepend-icon='mdiAccountKey'
			>
				{{ $t('account.recovery.confirmation.button') }}
			</v-btn>
		</v-form>
	</Card>
</template>

<script lang='ts' setup>
import { type AccountService } from '@iqrf/iqrf-gateway-webapp-client/services';
import { UserRole, type UserSignedIn } from '@iqrf/iqrf-gateway-webapp-client/types';
import { mdiAccountKey, mdiKey } from '@mdi/js';
import { type AxiosError } from 'axios';
import { validate as uuidValidate, version as uuidVersion } from 'uuid';
import { ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import UrlBuilder from '@/helpers/urlBuilder';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { useUserStore } from '@/store/user';
import { ComponentState } from '@/types/ComponentState';

const componentProps = defineProps({
	uuid: {
		type: String,
		required: true,
		validator(value: string): boolean {
			return uuidValidate(value) && uuidVersion(value) === 4;
		},
	},
});
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const form: Ref<typeof VForm | null> = ref(null);
const i18n = useI18n();
const password: Ref<string> = ref('');
const router = useRouter();
const service: AccountService = useApiClient().getAccountService();
const store = useUserStore();

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Loading;
	service.confirmPasswordRecovery(componentProps.uuid, {
		password: password.value,
		baseUrl: new UrlBuilder().getBaseUrl(),
	})
		.then((user: UserSignedIn): UserSignedIn => {
			componentState.value = ComponentState.Success;
			if (user.role === UserRole.Basic) {
				location.pathname = '/';
			}
			store.setUserInfo(user);
			router.push('/');
			toast.success(
				i18n.t('account.recovery.confirmation.messages.success').toString(),
			);
			return user;
		})
		.catch((error: AxiosError): AxiosError => {
			switch (error?.response?.status) {
				case 404:
					componentState.value = ComponentState.NotFound;
					toast.error(
						i18n.t('account.recovery.confirmation.messages.notFound').toString(),
					);
					break;
				case 410:
					componentState.value = ComponentState.Expired;
					toast.error(
						i18n.t('account.recovery.confirmation.messages.expired').toString(),
					);
					break;
				default:
					componentState.value = ComponentState.Error;
					toast.error(
						i18n.t('account.recovery.confirmation.messages.failure').toString(),
					);
					break;
			}
			return error;
		});
}
</script>
