<template>
	<Card>
		<template #title>
			{{ $t('account.profile.password.title') }}
		</template>
		<v-form ref='form' @submit.prevent='onSubmit'>
			<PasswordInput
				v-model='passwordChange.old'
				:label='$t("account.profile.password.current")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("account.profile.password.validation.current")),
				]'
				required
				prepend-inner-icon='mdi-key'
			/>
			<PasswordInput
				v-model='passwordChange.new'
				:label='$t("account.profile.password.new")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("account.profile.password.validation.new")),
				]'
				required
				prepend-inner-icon='mdi-key'
			/>
			<v-btn
				color='primary'
				type='submit'
			>
				{{ $t('generic.button.change') }}
			</v-btn>
		</v-form>
	</Card>
</template>

<script lang='ts' setup>
import { UserPasswordChange } from '@iqrf/iqrf-gateway-webapp-client';
import type { AxiosError } from 'axios';
import { ref, Ref } from 'vue';
import { toast } from 'vue3-toastify';
import {VForm} from 'vuetify/components';

import ValidationRules from '@/helpers/ValidationRules';

import Card from '@/components/Card.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import UrlBuilder from '@/helpers/urlBuilder';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

const form: Ref<typeof VForm | null> = ref(null);
const passwordChange: Ref<UserPasswordChange> = ref<UserPasswordChange>({
	old: '',
	new: '',
	baseUrl: new UrlBuilder().getBaseUrl(),
});

/// @todo add messages
async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	useApiClient().getAccountService().changePassword(passwordChange.value)
		.then(() => {
			toast.success('Success');
		})
		.catch((error: AxiosError) => {
			toast.error('Error: ' + error.message);
		});
}
</script>
