<template>
	<Head>
		<title>{{ $t('auth.sign.in.title') }}</title>
	</Head>
	<v-card>
		<v-card-title class='text-center'>
			{{ $t('auth.sign.in.title') }}
		</v-card-title>
		<v-card-text>
			<v-form ref='form' @submit.prevent='onSubmit'>
				<TextInput
					v-model='credentials.username'
					:label='$t("user.username")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("user.validation.username")),
					]'
					required
					prepend-inner-icon='mdi-account'
				/>
				<PasswordInput
					v-model='credentials.password'
					:label='$t("user.password")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("user.validation.password")),
					]'
					required
					prepend-inner-icon='mdi-key'
				/>
				<div style='display: flex; justify-content: space-between;'>
					<v-btn
						color='primary'
						type='submit'
						prepend-icon='mdi-login'
					>
						{{ $t('auth.sign.in.title') }}
					</v-btn>
					<v-btn
						variant='text'
						to='/account/recovery'
					>
						{{ $t('auth.sign.in.recoverPassword') }}
					</v-btn>
				</div>
			</v-form>
		</v-card-text>
	</v-card>
</template>

<route lang='yaml'>
name: SignIn
meta:
  requiresAuth: false
</route>

<script lang='ts' setup>
import { ref, Ref } from 'vue';
import { Head } from '@vueuse/head';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { VForm } from 'vuetify/components';
import { toast } from 'vue3-toastify';

import { UserCredentials } from '@iqrf/iqrf-gateway-webapp-client';
import { useUserStore } from '@/store/user';

import PasswordInput from '@/components/PasswordInput.vue';
import TextInput from '@/components/TextInput.vue';
import ValidationRules from '@/helpers/ValidationRules';
import { basicErrorToast } from '@/helpers/errorToast';
import { AxiosError } from 'axios';
import { validateForm } from '@/helpers/validateForm';

const i18n = useI18n();
const route = useRoute();
const router = useRouter();
const userStore = useUserStore();
const credentials: Ref<UserCredentials> = ref({username: '', password: ''});
const form: Ref<typeof VForm | null> = ref(null);

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	await userStore.signIn(credentials.value)
		.then(() => {
			let destination = (route?.query?.redirect as string | undefined) ?? '/';
			if (destination.startsWith('/sign/in')) {
				destination = '/';
			}
			router.push(destination);
			toast.success(
				i18n.t('core.sign.in.messages.success').toString()
			);
		})
		.catch((error: AxiosError) => {
			basicErrorToast(error, 'core.sign.in.messages.incorrectUsernameOrPassword');
		});
}
</script>
