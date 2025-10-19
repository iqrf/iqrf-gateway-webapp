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
		@submit.prevent='onSubmit()'
	>
		<ICard>
			<template #title>
				{{ $t('components.account.password.title') }}
			</template>
			<v-alert
				class='mb-4'
				type='info'
				variant='tonal'
				:text='$t("components.account.password.note")'
			/>
			<IPasswordInput
				v-model='passwordChange.old'
				:label='$t("components.account.password.current")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("components.account.password.validation.current.required")),
				]'
				required
				:prepend-inner-icon='mdiKey'
			/>
			<IPasswordInput
				v-model='passwordChange.new'
				:label='$t("components.account.password.new")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("components.common.validations.password.required")),
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
					(v: string) => v.length > 0 && v === passwordChange.new || $t("common.validation.passwordConfirm.match"),
				]'
				required
				:prepend-inner-icon='mdiKey'
			/>
			<template #actions>
				<IActionBtn
					:action='Action.Save'
					:disabled='!isValid.value'
					:loading='componentState === ComponentState.Action'
					:text='$t("components.account.password.title")'
					type='submit'
				/>
			</template>
		</ICard>
	</v-form>
</template>

<script lang='ts' setup>
import { type UserPasswordChange } from '@iqrf/iqrf-gateway-webapp-client/types';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IPasswordInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiHelpCircleOutline, mdiKey } from '@mdi/js';
import { ref, type Ref, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { type VForm } from 'vuetify/components';

import UrlBuilder from '@/helpers/urlBuilder';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';
import { useUserStore } from '@/store/user';

/// Component state
const componentState: Ref<ComponentState> = ref(ComponentState.Ready);
/// Form reference
const form: Ref<VForm | null> = useTemplateRef('form');
/// User store
const userStore = useUserStore();
/// Internationalization instance
const i18n = useI18n();
/// Password change request
const passwordChange: Ref<UserPasswordChange> = ref<UserPasswordChange>({
	old: '',
	new: '',
	baseUrl: new UrlBuilder().getBaseUrl(),
});
/// Password confirmation
const passwordConfirmation: Ref<string> = ref('');

/**
 * Handles the password change form submission
 */
async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Action;
	try {
		await useApiClient().getAccountService().updatePassword(passwordChange.value);
		await userStore.signOut();
		toast.success(
			i18n.t('components.account.password.messages.save.success'),
		);
	} catch {
		toast.error(
			i18n.t('components.account.password.messages.save.failed'),
		);
	} finally {
		componentState.value = ComponentState.Ready;
	}
}
</script>
