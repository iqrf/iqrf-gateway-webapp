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
	<v-form ref='form' v-slot='{ isValid }' @submit.prevent='onSubmit'>
		<Card>
			<template #title>
				{{ $t('components.auth.sign.in.title') }}
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
			<SessionExpirationInput
				v-if='credentials.expiration !== undefined'
				v-model='credentials.expiration'
			/>
			<template #actions>
				<CardActionBtn
					color='primary'
					:disabled='!isValid.value'
					:text='$t("components.auth.sign.in.actions.signIn")'
					type='submit'
					:icon='mdiLogin'
				/>
				<v-spacer />
				<CardActionBtn
					color='grey'
					:icon='mdiAccountKey'
					:text='$t("components.auth.sign.in.actions.recoverPassword")'
					to='/account/recovery'
				/>
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
import { AxiosError } from 'axios';
import { onMounted, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import SessionExpirationInput
	from '@/components/auth/SessionExpirationInput.vue';
import Card from '@/components/layout/card/Card.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import PasswordInput from '@/components/layout/form/PasswordInput.vue';
import TextInput from '@/components/layout/form/TextInput.vue';
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
const form: Ref<VForm | null> = ref(null);

onMounted(() => {
	credentials.value.expiration = userStore.getLastRequestedExpiration;
});

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	try {
		await userStore.signIn(credentials.value);
		let destination = (route?.query?.redirect as string | undefined) ?? '/';
		if (destination.startsWith('/sign/in')) {
			destination = '/';
		}
		await router.push(destination);
		toast.success(i18n.t('auth.sign.in.messages.success'));
		await gatewayStore.fetchInfo();
		await repositoryStore.fetch();
	} catch (error) {
		if (error instanceof AxiosError) {
			basicErrorToast(error, 'auth.sign.in.messages.incorrectUsernameOrPassword');
		}
	}
}
</script>
