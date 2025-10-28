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
		:title='$t("components.install.wizard.mailServerConfiguration.title")'
	>
		<p class='mb-4'>
			{{ $t('components.install.wizard.mailServerConfiguration.text') }}
		</p>
		<v-form
			ref='form'
			v-model='formValidity'
		>
			<v-alert
				v-if='defaultConfig'
				class='mb-4'
				type='info'
				variant='tonal'
				:text='$t("components.config.smtp.defaultConfig")'
			/>
			<v-switch
				v-if='defaultConfig'
				v-model='customConfig'
				:label='$t("components.config.smtp.form.customConfig")'
				color='primary'
			/>
			<v-switch
				v-if='customConfig'
				v-model='configuration.enabled'
				:label='$t("common.states.enabled")'
				color='primary'
			/>
			<ITextInput
				v-if='customConfig'
				v-model='configuration.host'
				:label='$t("components.config.smtp.form.host")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("components.config.smtp.validation.host.required")),
					(v: string) => ValidationRules.host(v, $t("components.config.smtp.validation.host.invalid")),
				]'
				:disabled='[ComponentState.Loading, ComponentState.Reloading, ComponentState.Action].includes(componentState) || !configuration.enabled'
				:prepend-inner-icon='mdiServer'
				required
			/>
			<INumberInput
				v-if='customConfig'
				v-model='configuration.port'
				:label='$t("common.labels.port")'
				:rules='[
					(v: number|null) => ValidationRules.required(v, $t("common.validation.port.required")),
					(v: number) => ValidationRules.integer(v, $t("common.validation.port.integer")),
					(v: number) => ValidationRules.between(v, 1, 65535, $t("common.validation.port.between")),
				]'
				:min='1'
				:max='65535'
				:disabled='[ComponentState.Loading, ComponentState.Reloading, ComponentState.Action].includes(componentState) || !configuration.enabled'
				:prepend-inner-icon='mdiNumeric'
				required
			/>
			<ITextInput
				v-if='customConfig'
				v-model='configuration.username'
				:label='$t("common.labels.username")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("common.validation.username.required")),
				]'
				:disabled='[ComponentState.Loading, ComponentState.Reloading, ComponentState.Action].includes(componentState) || !configuration.enabled'
				:prepend-inner-icon='mdiAccount'
				required
			/>
			<IPasswordInput
				v-if='customConfig'
				v-model='configuration.password'
				:label='$t("common.labels.password")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("common.validation.password.required")),
				]'
				:disabled='[ComponentState.Loading, ComponentState.Reloading, ComponentState.Action].includes(componentState) || !configuration.enabled'
				:prepend-inner-icon='mdiKey'
				required
			/>
			<SmtpSecurityInput
				v-if='customConfig'
				v-model='configuration.secure'
				:disabled='[ComponentState.Loading, ComponentState.Reloading, ComponentState.Action].includes(componentState) || !configuration.enabled'
			/>
			<ITextInput
				v-if='customConfig'
				v-model='configuration.from'
				:label='$t("components.config.smtp.form.sender")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("components.config.smtp.validation.sender.required")),
				]'
				:disabled='[ComponentState.Loading, ComponentState.Reloading, ComponentState.Action].includes(componentState) || !configuration.enabled'
				:prepend-inner-icon='mdiEmail'
			/>
		</v-form>
		<template #actions='{ next }'>
			<IActionBtn
				:action='Action.Next'
				:loading='componentState === ComponentState.Action && smtpAction === SmtpAction.Save'
				:disabled='!formValidity || [ComponentState.Loading, ComponentState.Reloading].includes(componentState) || (componentState === ComponentState.Action && smtpAction !== SmtpAction.Save)'
				@click='onSubmit(next)'
			/>
			<IActionBtn
				:action='Action.Skip'
				class='ml-2'
				:disabled='[ComponentState.Loading, ComponentState.Reloading, ComponentState.Action].includes(componentState)'
				@click='next()'
			/>
			<IActionBtn
				color='info'
				class='ml-2'
				:icon='mdiEmailFast'
				:text='$t("components.config.smtp.actions.test")'
				:loading='componentState === ComponentState.Action && smtpAction === SmtpAction.Test'
				:disabled='!formValidity || defaultConfig || !configuration.enabled || [ComponentState.Loading, ComponentState.Reloading].includes(componentState) || (componentState === ComponentState.Action && smtpAction !== SmtpAction.Test)'
				@click='testConfiguration()'
			/>
		</template>
	</v-stepper-vertical-item>
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
	INumberInput,
	IPasswordInput,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import {
	mdiAccount,
	mdiEmail,
	mdiEmailFast,
	mdiKey,
	mdiNumeric,
	mdiServer,
} from '@mdi/js';
import { storeToRefs } from 'pinia';
import {
	onBeforeMount,
	ref,
	type Ref,
	type TemplateRef,
	useTemplateRef,
	watch,
} from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { type VForm } from 'vuetify/components';

import SmtpSecurityInput
	from '@/components/config/smtp/SmtpSecurityInput.vue';
import UrlBuilder from '@/helpers/urlBuilder';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';
import { useUserStore } from '@/store/user';

enum SmtpAction {
	Save = 0,
	Test = 1,
}

const componentProps = defineProps({
	index: {
		type: Number,
		required: true,
	},
});
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const userStore = useUserStore();
const { isLoggedIn, isVerified } = storeToRefs(userStore);
const defaultConfig: Ref<boolean> = ref(false);
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
const customConfig: Ref<boolean> = ref(true);
const form: TemplateRef<VForm> = useTemplateRef('form');
const formValidity: Ref<boolean | null> = ref(null);
const i18n = useI18n();
let service: MailerService = useApiClient().getConfigServices().getMailerService();
const smtpAction: Ref<SmtpAction | null> = ref(null);

onBeforeMount(async () => {
	if (isLoggedIn.value) {
		await getConfig();
	}
});

watch(isLoggedIn, async (newVal: boolean, oldVal: boolean) => {
	if (newVal && !oldVal) {
		service = useApiClient().getConfigServices().getMailerService();
		await getConfig();
	}
});

/**
 * Retrieves the SMTP configuration
 */
async function getConfig(): Promise<void> {
	componentState.value = componentState.value === ComponentState.Created ? ComponentState.Loading : ComponentState.Reloading;
	try {
		const response: MailerGetConfigResponse = await service.getConfig();
		configuration.value = response.config;
		if (response.headers) {
			defaultConfig.value = response.headers.defaultConfig;
			customConfig.value = !response.headers.defaultConfig;
		}
		componentState.value = ComponentState.Ready;
	} catch {
		componentState.value = ComponentState.FetchFailed;
		toast.error(
			i18n.t('components.config.smtp.messages.test.failed'),
		);
	}
}

/**
 * Creates a new user
 * @param {Function} onClickNext Next button click handler
 */
async function onSubmit(onClickNext: Function): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	smtpAction.value = SmtpAction.Save;
	componentState.value = ComponentState.Action;
	try {
		if (customConfig.value) {
			await service.updateConfig(configuration.value);
			if (!isVerified.value) {
				await useApiClient().getAccountService().resendVerificationEmail({
					baseUrl: new UrlBuilder().getBaseUrl(),
				});
			}
			toast.success(
				i18n.t('components.config.smtp.messages.save.success'),
			);
		}
		onClickNext();
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
