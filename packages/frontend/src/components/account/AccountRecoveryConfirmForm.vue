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
				{{ $t('pages.account.recovery.title') }}
			</template>
			<p>
				{{ $t('components.account.recovery.confirmation.prompt') }}
			</p>
			<IPasswordInput
				v-model='data.password'
				class='mt-4'
				:label='$t("components.account.password.new")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("components.account.password.validation.new.required")),
					(v: string) => ValidationRules.betweenLen(v, 15, 64, $t("components.common.validations.password.betweenLen")),
					(v: string) => ValidationRules.webappUserPassword(v, $t("components.common.validations.password.invalid")),
				]'
				required
				:prepend-inner-icon='mdiKey'
			>
				<template #append>
					<v-tooltip location='left'>
						<template #activator='{ props }'>
							<v-icon v-bind='props' :icon='mdiHelpCircleOutline' />
						</template>
						<div style='white-space: pre-line;'>
							{{ $t('components.common.hints.password.note') }}
							<ul style='padding-left: 1.2rem; margin: 0;'>
								<li>{{ $t('components.common.hints.password.letters') }}</li>
								<li>{{ $t('components.common.hints.password.numbers') }}</li>
								<li>{{ $t('components.common.hints.password.special', { special: '!"#$%&\'()*+,-./:;<=>?@[\\]^_`{|}~' }) }}</li>
							</ul>
						</div>
					</v-tooltip>
				</template>
			</IPasswordInput>
			<IPasswordInput
				v-model='passwordConfirmation'
				:label='$t("components.account.password.confirmation")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("components.account.password.validation.confirmation.required")),
					(v: string) => v.length > 0 && v === data.password || $t("common.validation.passwordConfirm.match"),
				]'
				required
				:prepend-inner-icon='mdiKey'
			/>
			<IActionBtn
				color='primary'
				container-type='card'
				:disabled='!isValid.value || componentState === ComponentState.Action'
				:loading='componentState === ComponentState.Action'
				:icon='mdiAccountKey'
				:text='$t("components.account.recovery.confirmation.button")'
				type='submit'
			/>
		</ICard>
	</v-form>
</template>

<script lang='ts' setup>
import { AccountService } from '@iqrf/iqrf-gateway-webapp-client/services';
import {
	UserPasswordReset,
	UserSignedIn,
} from '@iqrf/iqrf-gateway-webapp-client/types';
import {
	ComponentState,
	IActionBtn,
	ICard,
	IPasswordInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiAccountKey, mdiHelpCircleOutline, mdiKey } from '@mdi/js';
import { AxiosError } from 'axios';
import { validate as uuidValidate, version as uuidVersion } from 'uuid';
import { ref, type Ref, type TemplateRef, useTemplateRef, watchEffect } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { type VForm } from 'vuetify/components';

import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';
import { useUserStore } from '@/store/user';

/// Component props
const componentProps = defineProps<{
	uuid: string;
}>();
/// Component state
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
/// Form reference
const form: TemplateRef<VForm> = useTemplateRef('form');
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
	componentState.value = ComponentState.Action;
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

watchEffect((): void => {
	if (!uuidValidate(componentProps.uuid) || uuidVersion(componentProps.uuid) !== 4) {
		throw new Error('Invalid UUID v4 format');
	}
});
</script>
