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
		:disabled='componentState === ComponentState.Saving'
		@submit.prevent='onSubmit'
	>
		<Card>
			<template #title>
				{{ $t('pages.account.recovery.title') }}
			</template>
			<p>
				{{ $t('components.account.recovery.confirmation.prompt') }}
			</p>
			<PasswordInput
				v-model='data.password'
				class='mt-4'
				:label='$t("components.account.password.new")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("components.account.password.validation.new.required")),
					(v: string) => ValidationRules.minLength(v, 8, $t("common.validation.password.minLength")),
				]'
				required
				:prepend-inner-icon='mdiKey'
			/>
			<PasswordInput
				v-model='passwordConfirmation'
				:label='$t("components.account.password.confirmation")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("components.account.password.validation.confirmation.required")),
					(v: string) => v.length !== 0 && v === data.password || $t("common.validation.passwordConfirm.match"),
				]'
				required
				:prepend-inner-icon='mdiKey'
			/>
			<CardActionBtn
				color='primary'
				:disabled='!isValid.value || componentState === ComponentState.Saving'
				:loading='componentState === ComponentState.Saving'
				:icon='mdiAccountKey'
				:text='$t("components.account.recovery.confirmation.button")'
				type='submit'
			/>
		</Card>
	</v-form>
</template>

<script lang='ts' setup>
import { AccountService } from '@iqrf/iqrf-gateway-webapp-client/services';
import {
	UserPasswordReset,
	UserSignedIn,
} from '@iqrf/iqrf-gateway-webapp-client/types';
import { mdiAccountKey, mdiKey } from '@mdi/js';
import { AxiosError } from 'axios';
import { validate as uuidValidate, version as uuidVersion } from 'uuid';
import { ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { type VForm } from 'vuetify/components';

import Card from '@/components/layout/card/Card.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import PasswordInput from '@/components/layout/form/PasswordInput.vue';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { useUserStore } from '@/store/user';
import { ComponentState } from '@/types/ComponentState';

/// Component props
const componentProps = defineProps({
	uuid: {
		type: String,
		required: true,
		validator(value: string): boolean {
			return uuidValidate(value) && uuidVersion(value) === 4;
		},
	},
});
/// Component state
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
/// Form reference
const form: Ref<VForm | null> = ref(null);
/// Internationalization instance
const i18n = useI18n();
/// Password reset request
const data: Ref<UserPasswordReset> = ref({
	password: '',
});
/// Password confirmation
const passwordConfirmation: Ref<string> = ref('');
/// Router instance
const router = useRouter();
/// Account service
const service: AccountService = useApiClient().getAccountService();
/// User store
const store = useUserStore();

/**
 * Submit password change
 */
async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Saving;
	try {
		const user: UserSignedIn = await service.confirmPasswordRecovery(componentProps.uuid, data.value);
		componentState.value = ComponentState.Success;
		store.processSignInResponse(user);
		await store.refreshUserPreferences();
		await router.push('/');
		toast.success(
			i18n.t('components.account.recovery.confirmation.messages.success'),
		);
	} catch (error) {
		if (!(error instanceof AxiosError)) {
			componentState.value = ComponentState.Error;
			toast.error(
				i18n.t('components.account.recovery.confirmation.messages.failure'),
			);
			return;
		}
		switch (error?.response?.status) {
			case 404:
				componentState.value = ComponentState.NotFound;
				toast.error(
					i18n.t('components.account.recovery.confirmation.messages.notFound'),
				);
				break;
			case 410:
				componentState.value = ComponentState.Expired;
				toast.error(
					i18n.t('components.account.recovery.confirmation.messages.expired'),
				);
				break;
			default:
				componentState.value = ComponentState.Error;
				toast.error(
					i18n.t('components.account.recovery.confirmation.messages.failure'),
				);
				break;
		}
	}
}
</script>
