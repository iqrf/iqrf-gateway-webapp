<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

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
	<v-form
		ref='form'
		v-slot='{ isValid }'
		:disabled='componentState === ComponentState.Action'
		@submit.prevent='onSubmit'
	>
		<ICard>
			<template #title>
				{{ $t('components.auth.sign.in.title') }}
			</template>
			<ITextInput
				v-model='credentials.username'
				:label='$t("components.common.fields.username")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("components.common.validations.username.required")),
				]'
				required
				:prepend-inner-icon='mdiAccount'
			/>
			<IPasswordInput
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
				<IActionBtn
					color='primary'
					container-type='card'
					:disabled='!isValid.value'
					:loading='componentState === ComponentState.Action'
					:icon='mdiLogin'
					:text='$t("components.auth.sign.in.actions.signIn")'
					type='submit'
				/>
				<v-spacer />
				<IActionBtn
					color='grey'
					container-type='card'
					:icon='mdiAccountKey'
					:text='$t("components.auth.sign.in.actions.recoverPassword")'
					to='/account/recovery'
				/>
			</template>
		</ICard>
	</v-form>
</template>

<script lang='ts' setup>
import {
	type UserCredentials,
	UserSessionExpiration,
} from '@iqrf/iqrf-gateway-webapp-client/types';
import {
	ComponentState,
	IActionBtn,
	ICard,
	IPasswordInput,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiAccount, mdiAccountKey, mdiKey, mdiLogin } from '@mdi/js';
import { AxiosError } from 'axios';
import { onMounted, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { type VForm } from 'vuetify/components';

import SessionExpirationInput
	from '@/components/auth/SessionExpirationInput.vue';
import { validateForm } from '@/helpers/validateForm';
import { useGatewayStore } from '@/store/gateway';
import { useRepositoryStore } from '@/store/repository';
import { useUserStore } from '@/store/user';

/// Component state
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
/// Internationalization instance
const i18n = useI18n();
/// The current route
const route = useRoute();
/// Vue router
const router = useRouter();
/// Gateway information store
const gatewayStore = useGatewayStore();
/// IQRF Repository store
const repositoryStore = useRepositoryStore();
/// User store
const userStore = useUserStore();
/// User credentials
const credentials: Ref<UserCredentials> = ref({
	username: '',
	password: '',
	expiration: UserSessionExpiration.Default,
});
/// Form reference
const form: Ref<VForm | null> = ref(null);

onMounted(() => {
	credentials.value.expiration = userStore.getLastRequestedExpiration;
});

/**
 * Handles the sign in form submission
 */
async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Action;
	try {
		await userStore.signIn(credentials.value);
		let destination = (route?.query?.redirect as string | undefined) ?? '/';
		if (destination.startsWith('/sign/in')) {
			destination = '/';
		}
		if (destination !== '/' && userStore.getRole !== null) {
			const resolveRoute = router.resolve(destination);
			if (resolveRoute.name === 'NotFound') {
				destination = '/';
			} else {
				const roles = (resolveRoute.meta.roles as string[]) ?? [];
				if (roles.length > 0 && !roles.includes(userStore.getRole)) {
					destination = '/';
				}
			}
		}
		await router.push(destination);
		componentState.value = ComponentState.Idle;
		toast.success(i18n.t('components.auth.sign.in.messages.success'));
		await userStore.refreshUserPreferences();
		await gatewayStore.fetchInfo();
		await repositoryStore.fetch();
	} catch (error) {
		if (error instanceof AxiosError && error.response?.status === 400) {
			toast.error(i18n.t('components.auth.sign.in.messages.incorrectUsernameOrPassword'));
		} else {
			toast.error(i18n.t('components.auth.sign.in.messages.failure'));
		}
		componentState.value = ComponentState.Idle;
	}
}
</script>
