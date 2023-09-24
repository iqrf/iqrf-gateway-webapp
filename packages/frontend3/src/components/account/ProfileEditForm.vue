<template>
	<Card :bottom-margin='true'>
		<template #title>
			{{ $t('account.profile.details.title') }}
		</template>
		<v-form ref='form' @submit.prevent='onSubmit'>
			<TextInput
				v-model='user.username'
				:label='$t("user.username")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("user.validation.username")),
				]'
				required
				:prepend-inner-icon='mdiAccount'
			/>
			<TextInput
				v-model='user.email'
				:label='$t("user.email")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("user.validation.missingEmail")),
					(v: string) => ValidationRules.email(v, $t("user.validation.invalidEmail")),
				]'
				required
				:prepend-inner-icon='mdiEmail'
			/>
			<SelectInput
				v-model='user.language'
				:items='languageOptions'
				:label='$t("user.language")'
				:prepend-inner-icon='mdiTranslate'
			/>
			<v-btn
				color='primary'
				type='submit'
			>
				{{ $t('generic.button.edit') }}
			</v-btn>
		</v-form>
	</Card>
</template>

<script lang='ts' setup>
import { UserEdit, UserInfo, UserLanguage, UserRole } from '@iqrf/iqrf-gateway-webapp-client/types';
import { AccountService } from '@iqrf/iqrf-gateway-webapp-client/services';
import { onMounted, ref, Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { AxiosError } from 'axios';

import { basicErrorToast } from '@/helpers/errorToast';
import { useUserStore } from '@/store/user';
import Card from '@/components/Card.vue';
import SelectInput from '@/components/SelectInput.vue';
import TextInput from '@/components/TextInput.vue';
import ValidationRules from '@/helpers/ValidationRules';
import { getLanguageOptions } from '@/helpers/userData';
import { useApiClient } from '@/services/ApiClient';
import UrlBuilder from '@/helpers/urlBuilder';
import { mdiAccount, mdiEmail, mdiTranslate } from '@mdi/js';

const i18n = useI18n();
const userStore = useUserStore();
const user: Ref<UserEdit> = ref({
	username: '',
	email: '',
	language: UserLanguage.English,
	role: UserRole.Basic,
});
const languageOptions = getLanguageOptions();

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
				i18n.t('account.profile.details.messages.success').toString()
			);
			userStore.refreshUserInfo();
		})
		.catch((error: AxiosError) => basicErrorToast(error, 'account.profile.details.messages.failure'));
}
</script>
