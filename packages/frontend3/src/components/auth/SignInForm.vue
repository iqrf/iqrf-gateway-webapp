<template>
	<v-form ref='form' @submit.prevent='onSubmit'>
		<Card actions-color='white'>
			<template #title>
				{{ $t('components.auth.signIn.title') }}
			</template>
			<TextInput
				v-model='credentials.username'
				:label='$t("components.common.fields.username")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("components.common.validations.username.required")),
				]'
				required
				:prepend-inner-icon='mdiAccount'
			/>
			<PasswordInput
				v-model='credentials.password'
				:label='$t("components.common.fields.password")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("components.common.validations.password.required")),
				]'
				required
				:prepend-inner-icon='mdiKey'
			/>
			<SessionExpirationInput v-model='credentials.expiration' />
			<template #actions>
				<v-btn
					variant='elevated'
					color='primary'
					type='submit'
					:prepend-icon='mdiLogin'
				>
					{{ $t('components.auth.signIn.actions.signIn') }}
				</v-btn>
				<v-spacer />
				<v-btn
					variant='elevated'
					color='grey'
					to='/account/recovery'
					:prepend-icon='mdiAccountKey'
				>
					{{ $t('components.auth.signIn.actions.recoverPassword') }}
				</v-btn>
			</template>
		</Card>
	</v-form>
</template>

<script lang='ts' setup>
import {
	type UserCredentials,
	UserSessionExpiration,
} from '@iqrf/iqrf-gateway-webapp-client/types';
import { mdiAccount, mdiAccountKey, mdiKey, mdiLogin } from '@mdi/js';
import { type AxiosError } from 'axios';
import { onMounted, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import SessionExpirationInput
	from '@/components/auth/SessionExpirationInput.vue';
import Card from '@/components/Card.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextInput from '@/components/TextInput.vue';
import { basicErrorToast } from '@/helpers/errorToast';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useGatewayStore } from '@/store/gateway';
import { useRepositoryStore } from '@/store/repository';
import { useUserStore } from '@/store/user';

const i18n = useI18n();
const route = useRoute();
const router = useRouter();
const gatewayStore = useGatewayStore();
const repositoryStore = useRepositoryStore();
const userStore = useUserStore();
const credentials: Ref<UserCredentials> = ref({
	username: '',
	password: '',
	expiration: UserSessionExpiration.Default,
});
const form: Ref<typeof VForm | null> = ref(null);

onMounted(() => {
	credentials.value.expiration = userStore.getLastRequestedExpiration;
});

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	await userStore.signIn(credentials.value)
		.then(async () => {
			let destination = (route?.query?.redirect as string | undefined) ?? '/';
			if (destination.startsWith('/sign/in')) {
				destination = '/';
			}
			await router.push(destination);
			toast.success(
				i18n.t('auth.sign.in.messages.success').toString(),
			);
			await gatewayStore.fetchInfo();
			await repositoryStore.fetch();
		})
		.catch((error: AxiosError) => {
			basicErrorToast(error, 'auth.sign.in.messages.incorrectUsernameOrPassword');
		});
}
</script>
