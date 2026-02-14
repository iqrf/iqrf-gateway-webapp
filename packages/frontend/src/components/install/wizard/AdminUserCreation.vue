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
	<v-stepper-vertical-item
		:value='componentProps.index'
		:title='$t("components.install.wizard.adminUserCreation.title")'
	>
		<p class='mb-4'>
			{{ $t('components.install.wizard.adminUserCreation.text') }}
		</p>
		<v-form
			ref='form'
			v-model='formValidity'
			:disabled='componentState === ComponentState.Action'
		>
			<ITextInput
				v-model='user.username'
				:label='$t("components.common.fields.username")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("components.common.validations.username.required")),
				]'
				required
				:prepend-inner-icon='mdiAccount'
			/>
			<ITextInput
				v-model='user.email'
				:label='$t("components.common.fields.email")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("components.common.validations.email.required")),
					(v: string) => ValidationRules.email(v, $t("components.common.validations.email.email")),
				]'
				required
				:prepend-inner-icon='mdiEmail'
			/>
			<IPasswordInput
				v-model='user.password'
				:label='$t("components.common.fields.password")'
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
				:label='$t("components.common.fields.passwordConfirm")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("components.common.validations.passwordConfirm.required")),
					(v: string) => v.length > 0 && v === user.password || $t("components.common.validations.passwordConfirm.match"),
				]'
				required
				:prepend-inner-icon='mdiKey'
			/>
			<ILanguageSelect v-model='user.language' />
		</v-form>
		<template #actions='{ next }'>
			<IActionBtn
				:disabled='!formValidity'
				container-type='card'
				:icon='mdiAccountPlus'
				:loading='componentState === ComponentState.Action'
				:text='$t("components.install.wizard.adminUserCreation.button")'
				@click='onSubmit(next)'
			/>
		</template>
	</v-stepper-vertical-item>
</template>

<script lang='ts' setup>
import {
	type EmailSentResponse,
	type UserCreate,
	type UserCredentials,
	UserRole,
} from '@iqrf/iqrf-gateway-webapp-client/types';
import { Language } from '@iqrf/iqrf-ui-common-types';
import {
	ComponentState,
	IActionBtn,
	ILanguageSelect,
	IPasswordInput,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiAccount, mdiAccountPlus, mdiEmail, mdiHelpCircleOutline, mdiKey } from '@mdi/js';
import { ref, type Ref, type TemplateRef, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import UrlBuilder from '@/helpers/urlBuilder';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';
import { useInstallStore } from '@/store/install';
import { useUserStore } from '@/store/user';

const componentProps = defineProps<{
	/// Step index
	index: number;
}>();
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const urlBuilder = new UrlBuilder();
const installStore = useInstallStore();
const userStore = useUserStore();
const user: Ref<UserCreate> = ref({
	username: '',
	password: '',
	email: '',
	language: Language.English,
	role: UserRole.Admin,
});
const passwordConfirmation: Ref<string> = ref('');
const form: TemplateRef<VForm> = useTemplateRef('form');
const formValidity: Ref<boolean | null> = ref(null);
const i18n = useI18n();

/**
 * Creates a new user
 * @param {Function} onClickNext Next button click handler
 */
async function onSubmit(onClickNext: Function): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Action;
	const data: UserCreate = {
		...user.value,
		baseUrl: urlBuilder.getBaseUrl(),
	};
	try {
		const response: EmailSentResponse = await useApiClient()
			.getSecurityServices()
			.getUserService()
			.create(user.value);
		if (response.emailSent) {
			toast.success(i18n.t('user.messages.verificationSent'));
		}
		const credentials: UserCredentials = {
			username: user.value.username,
			password: user.value.password,
		};
		await userStore.signIn(credentials);
		await userStore.refreshUserPreferences();
		installStore.setHasUsers(true);
		componentState.value = ComponentState.Idle;
		onClickNext();
	} catch {
		toast.error(i18n.t(
			'components.accessControl.users.messages.add.failed',
			{ user: data.username },
		));
		componentState.value = ComponentState.Idle;
	}
}

</script>
