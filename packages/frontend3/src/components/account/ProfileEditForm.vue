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
	<Card :bottom-margin='true'>
		<template #title>
			{{ $t('account.profile.details.title') }}
		</template>
		<v-form ref='form' @submit.prevent='onSubmit'>
			<TextInput
				v-model='user.username'
				:label='$t("components.common.fields.username")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("components.common.validations.username.required")),
				]'
				required
				:prepend-inner-icon='mdiAccount'
			/>
			<TextInput
				v-model='user.email'
				:label='$t("components.common.fields.email")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("components.common.validations.email.required")),
					(v: string) => ValidationRules.email(v, $t("components.common.validations.email.email")),
				]'
				required
				:prepend-inner-icon='mdiEmail'
			/>
			<LanguageInput v-model='user.language' />
			<v-btn
				color='primary'
				type='submit'
			>
				{{ $t('common.buttons.edit') }}
			</v-btn>
		</v-form>
	</Card>
</template>

<script lang='ts' setup>
import { type AccountService } from '@iqrf/iqrf-gateway-webapp-client/services';
import { type UserEdit, type UserInfo, UserLanguage, UserRole } from '@iqrf/iqrf-gateway-webapp-client/types';
import { mdiAccount, mdiEmail } from '@mdi/js';
import { type AxiosError } from 'axios';
import { onMounted, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import LanguageInput from '@/components/account/LanguageInput.vue';
import Card from '@/components/Card.vue';
import TextInput from '@/components/TextInput.vue';
import { basicErrorToast } from '@/helpers/errorToast';
import UrlBuilder from '@/helpers/urlBuilder';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { useUserStore } from '@/store/user';

const i18n = useI18n();
const userStore = useUserStore();
const user: Ref<UserEdit> = ref({
	username: '',
	email: '',
	language: UserLanguage.English,
	role: UserRole.Basic,
});

const accountService: AccountService = useApiClient().getAccountService();

onMounted(() => {
	accountService.fetchInfo()
		.then((data: UserInfo) => {
			user.value = {
				username: data.username,
				email: data.email,
				language: data.language,
				role: data.role,
				baseUrl: new UrlBuilder().getBaseUrl(),
			};
		});
});

async function onSubmit(): Promise<void> {
	accountService.edit(user.value)
		.then(() => {
			toast.success(
				i18n.t('account.profile.details.messages.success').toString(),
			);
			userStore.refreshUserInfo();
		})
		.catch((error: AxiosError) => basicErrorToast(error, 'account.profile.details.messages.failure'));
}
</script>
