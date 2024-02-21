<template>
	<Card>
		<template #title>
			{{ $t('install.createUser.title') }}
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
			<PasswordInput
				v-model='user.password'
				:label='$t("user.password")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("user.validation.password")),
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
			<SelectInput
				v-model='expiration'
				:items='expirationOptions'
				:label='$t("auth.sign.in.expiration")'
				:prepend-inner-icon='mdiAccountClock'
			/>
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
import { type EmailSentResponse, type UserCreate, type UserCredentials, UserLanguage, UserRole, UserSessionExpiration } from '@iqrf/iqrf-gateway-webapp-client/types';
import { mdiAccount, mdiAccountClock, mdiEmail, mdiKey, mdiTranslate } from '@mdi/js';
import { type AxiosError } from 'axios';
import { ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import SelectInput from '@/components/SelectInput.vue';
import TextInput from '@/components/TextInput.vue';
import { basicErrorToast } from '@/helpers/errorToast';
import UrlBuilder from '@/helpers/urlBuilder';
import { getExpirationOptions, getLanguageOptions } from '@/helpers/userData';
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
const expirationOptions = getExpirationOptions();
const form: Ref<typeof VForm | null> = ref(null);

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	user.value.baseUrl = urlBuilder.getBaseUrl();
	useApiClient().getUserService().create(user.value)
		.then(async (response: EmailSentResponse) => {
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
			await userStore.signIn(credentials)
				.then(() => {
					const nextStep = installStore.getNextStep;
					if (nextStep === null) {
						router.push('/');
						return;
					}
					router.push({ name: nextStep.route });
				});
		})
		.catch((error: AxiosError) => {
			basicErrorToast(error, 'core.user.messages.createFailure');
		});
}

</script>
