<template>
	<Card>
		<template #title>
			{{ $t('auth.sign.in.title') }}
		</template>
		<v-form ref='form' @submit.prevent='onSubmit'>
			<TextInput
				v-model='credentials.username'
				:label='$t("user.username")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("user.validation.username")),
				]'
				required
				:prepend-inner-icon='mdiAccount'
			/>
			<PasswordInput
				v-model='credentials.password'
				:label='$t("user.password")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("user.validation.password")),
				]'
				required
				:prepend-inner-icon='mdiKey'
			/>
			<SelectInput
				v-model='credentials.expiration'
				:items='expirationOptions'
				:label='$t("auth.sign.in.expiration")'
				:prepend-inner-icon='mdiAccountClock'
			/>
			<div style='display: flex; justify-content: space-between;'>
				<v-btn
					color='primary'
					type='submit'
					:prepend-icon='mdiLogin'
				>
					{{ $t('auth.sign.in.title') }}
				</v-btn>
				<v-btn
					variant='text'
					color='primary'
					to='/account/recovery'
				>
					{{ $t('auth.sign.in.recoverPassword') }}
				</v-btn>
			</div>
		</v-form>
	</Card>
</template>

<script lang='ts' setup>
import { type UserCredentials, UserSessionExpiration } from '@iqrf/iqrf-gateway-webapp-client/types';
import { mdiAccount, mdiAccountClock, mdiKey, mdiLogin } from '@mdi/js';
import { type AxiosError } from 'axios';
import { onMounted, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import SelectInput from '@/components/SelectInput.vue';
import TextInput from '@/components/TextInput.vue';
import { basicErrorToast } from '@/helpers/errorToast';
import { getExpirationOptions } from '@/helpers/userData';
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
const expirationOptions = getExpirationOptions();
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
