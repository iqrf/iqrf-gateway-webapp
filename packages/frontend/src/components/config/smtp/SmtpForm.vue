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
		:disabled='[ComponentState.Loading, ComponentState.Reloading].includes(componentState) || !configuration.enabled'
		@submit.prevent='onSubmit()'
	>
		<ICard>
			<template #title>
				{{ $t('components.config.smtp.title') }}
			</template>
			<template #titleActions>
				<IActionBtn
					:action='configuration.enabled ? Action.Disable : Action.Enable'
					container-type='card-title'
					color='primary'
					:icon='stateButtonIcon'
					:disabled='[ComponentState.Action, ComponentState.Loading, ComponentState.Reloading, ComponentState.FetchFailed].includes(componentState)'
					@click='configuration.enabled = !configuration.enabled'
				/>
				<IActionBtn
					:action='Action.Reload'
					container-type='card-title'
					:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
					:disabled='componentState === ComponentState.Action'
					@click='getConfig()'
				/>
			</template>
			<v-alert
				v-if='componentState === ComponentState.FetchFailed'
				type='error'
				variant='tonal'
				:text='$t("components.config.smtp.messages.fetch.failed")'
			/>
			<v-skeleton-loader
				v-else
				class='input-skeleton-loader'
				:loading='componentState === ComponentState.Loading'
				type='heading@6'
			>
				<v-responsive>
					<v-alert
						v-if='defaultConfig'
						class='mb-4'
						type='warning'
						variant='tonal'
						:text='$t("components.config.smtp.defaultConfig")'
					/>
					<ITextInput
						v-model='configuration.host'
						:label='$t("components.config.smtp.form.host")'
						:rules='[
							(v: string|null) => ValidationRules.required(v, $t("components.config.smtp.validation.host.required")),
							(v: string) => ValidationRules.host(v, $t("components.config.smtp.validation.host.invalid")),
						]'
						:disabled='[ComponentState.Loading, ComponentState.Reloading].includes(componentState) || !configuration.enabled'
						:prepend-inner-icon='mdiServer'
						required
					/>
					<INumberInput
						v-model='configuration.port'
						:label='$t("common.labels.port")'
						:rules='[
							(v: number|null) => ValidationRules.required(v, $t("common.validation.port.required")),
							(v: number) => ValidationRules.integer(v, $t("common.validation.port.integer")),
							(v: number) => ValidationRules.between(v, 1, 65535, $t("common.validation.port.between")),
						]'
						:min='1'
						:max='65535'
						:disabled='[ComponentState.Loading, ComponentState.Reloading].includes(componentState) || !configuration.enabled'
						:prepend-inner-icon='mdiNumeric'
						required
					/>
					<ITextInput
						v-model='configuration.username'
						:label='$t("common.labels.username")'
						:rules='[
							(v: string|null) => ValidationRules.required(v, $t("common.validation.username.required")),
						]'
						:disabled='[ComponentState.Loading, ComponentState.Reloading].includes(componentState) || !configuration.enabled'
						:prepend-inner-icon='mdiAccount'
						required
					/>
					<IPasswordInput
						v-model='configuration.password'
						:label='$t("common.labels.password")'
						:rules='[
							(v: string|null) => ValidationRules.required(v, $t("common.validation.password.required")),
						]'
						:disabled='[ComponentState.Loading, ComponentState.Reloading].includes(componentState) || !configuration.enabled'
						:prepend-inner-icon='mdiKey'
					/>
					<SmtpSecurityInput
						v-model='configuration.secure'
						:disabled='[ComponentState.Loading, ComponentState.Reloading].includes(componentState) || !configuration.enabled'
					/>
					<ITextInput
						v-model='configuration.from'
						:label='$t("components.config.smtp.form.sender")'
						:rules='[
							(v: string|null) => ValidationRules.required(v, $t("components.config.smtp.validation.sender.required")),
						]'
						:disabled='[ComponentState.Loading, ComponentState.Reloading].includes(componentState) || !configuration.enabled'
						:prepend-inner-icon='mdiEmail'
					/>
				</v-responsive>
			</v-skeleton-loader>
			<template #actions>
				<IActionBtn
					:action='Action.Save'
					:loading='componentState === ComponentState.Action && smtpAction === SmtpAction.Save'
					:disabled='!isValid.value || [ComponentState.Loading, ComponentState.Reloading].includes(componentState) || (componentState === ComponentState.Action && smtpAction !== SmtpAction.Save)'
					type='submit'
				/>
				<IActionBtn
					color='info'
					:icon='mdiEmailFast'
					:text='$t("components.config.smtp.actions.test")'
					:loading='componentState === ComponentState.Action && smtpAction === SmtpAction.Test'
					:disabled='!isValid.value || !configuration.enabled || [ComponentState.Loading, ComponentState.Reloading].includes(componentState) || (componentState === ComponentState.Action && smtpAction !== SmtpAction.Test)'
					@click='testConfiguration()'
				/>
			</template>
		</ICard>
	</v-form>
</template>

<script lang='ts' setup>
import { MailerService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	type MailerConfig,
	type MailerGetConfigResponse, MailerTheme,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	INumberInput,
	IPasswordInput,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import {
	mdiAccount,
	mdiEmail,
	mdiEmailFast,
	mdiEmailOff,
	mdiKey,
	mdiNumeric,
	mdiServer,
} from '@mdi/js';
import {
	computed, ComputedRef, onMounted, ref, type Ref,
	type TemplateRef, useTemplateRef,
} from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { type VForm } from 'vuetify/components';

import SmtpSecurityInput from '@/components/config/smtp/SmtpSecurityInput.vue';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

enum SmtpAction {
	Save = 0,
	Test = 1,
}

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const form: TemplateRef<VForm> = useTemplateRef('form');
const configuration: Ref<MailerConfig> = ref({
	enabled: false,
	host: '',
	port: 465,
	username: '',
	password: '',
	secure: null,
	from: '',
	timeout: 20,
	context: [],
	clientHost: null,
	persistent: false,
	theme: MailerTheme.Generic,
});
const defaultConfig: Ref<boolean> = ref(false);
const smtpAction: Ref<SmtpAction | null> = ref(null);

const stateButtonIcon: ComputedRef<string> = computed((): string => {
	if (configuration.value.enabled) {
		return mdiEmailOff;
	}
	return mdiEmail;
});

const service: MailerService = useApiClient().getConfigServices().getMailerService();

onMounted(async () => await getConfig());

/**
 * Retrieves the SMTP configuration
 */
async function getConfig(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		const response: MailerGetConfigResponse = await service.getConfig();
		configuration.value = response.config;
		if (response.headers) {
			defaultConfig.value = response.headers.defaultConfig;
		}
		componentState.value = ComponentState.Ready;
	} catch {
		toast.error(
			i18n.t('components.config.smtp.messages.test.failed'),
		);
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
	}
}

/**
 * Saves the new SMTP configuration
 */
async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	smtpAction.value = SmtpAction.Save;
	componentState.value = ComponentState.Action;
	try {
		await service.updateConfig(configuration.value);
		toast.success(
			i18n.t('components.config.smtp.messages.save.success'),
		);
	} catch {
		toast.error(
			i18n.t('components.config.smtp.messages.save.failed'),
		);
	}
	componentState.value = ComponentState.Ready;
}

/**
 * Tests the SMTP configuration
 */
async function testConfiguration(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	smtpAction.value = SmtpAction.Test;
	componentState.value = ComponentState.Action;
	try {
		await service.testConfig(configuration.value);
		toast.success(
			i18n.t('components.config.smtp.messages.test.success'),
		);
	} catch {
		toast.error(
			i18n.t('components.config.smtp.messages.test.failed'),
		);
	}
	componentState.value = ComponentState.Ready;
}
</script>
