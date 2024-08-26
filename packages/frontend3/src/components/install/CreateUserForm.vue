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
			{{ $t('install.createUser.title') }}
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
			<PasswordInput
				v-model='user.password'
				:label='$t("components.common.fields.password")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("components.common.validations.password.required")),
				]'
				required
				:prepend-inner-icon='mdiKey'
			/>
			<SelectInput
				v-model='user.language'
				:items='languageOptions'
				:label='$t("user.language")'
				:prepend-inner-icon='mdiTranslate'
			/>
			<SessionExpirationInput v-model='expiration' />
			<v-btn
				color='primary'
				variant='elevated'
				type='submit'
			>
				{{ $t('install.createUser.createButton') }}
			</v-btn>
		</v-form>
	</Card>
</template>

<script lang='ts' setup>
import {
	type EmailSentResponse,
	type UserCreate,
	type UserCredentials,
	UserLanguage,
	UserRole,
	UserSessionExpiration,
} from '@iqrf/iqrf-gateway-webapp-client/types';
import { mdiAccount, mdiEmail, mdiKey, mdiTranslate } from '@mdi/js';
import { AxiosError } from 'axios';
import { ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import SessionExpirationInput
	from '@/components/auth/SessionExpirationInput.vue';
import Card from '@/components/layout/card/Card.vue';
import PasswordInput from '@/components/layout/form/PasswordInput.vue';
import SelectInput from '@/components/layout/form/SelectInput.vue';
import TextInput from '@/components/layout/form/TextInput.vue';
import { basicErrorToast } from '@/helpers/errorToast';
import UrlBuilder from '@/helpers/urlBuilder';
import { getLanguageOptions } from '@/helpers/userData';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { useInstallStore } from '@/store/install';
import { useUserStore } from '@/store/user';

const i18n = useI18n();

const urlBuilder = new UrlBuilder();
const router = useRouter();
const installStore = useInstallStore();
const userStore = useUserStore();
const user: Ref<UserCreate> = ref({
	username: '',
	password: '',
	email: '',
	language: UserLanguage.English,
	role: UserRole.Admin,
});
const languageOptions = getLanguageOptions();
const expiration = ref(UserSessionExpiration.Default);
const form: Ref<VForm | null> = ref(null);

/**
 * Creates a new user
 */
async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	user.value.baseUrl = urlBuilder.getBaseUrl();
	try {
		const response: EmailSentResponse =
			await useApiClient().getSecurityServices().getUserService().create(user.value);
		if (response.emailSent) {
			toast.success(
				i18n.t('core.user.messages.verificationSent').toString(),
			);
		}
		const credentials: UserCredentials = {
			username: user.value.username,
			password: user.value.password,
			expiration: expiration.value,
		};
		await userStore.signIn(credentials);
		const nextStep = installStore.getNextStep;
		if (nextStep === null) {
			router.push('/');
			return;
		}
		await router.push({ name: nextStep.route });
	} catch (error) {
		if (error instanceof AxiosError) {
			basicErrorToast(error, 'core.user.messages.createFailure');
		}
	}
}

</script>
