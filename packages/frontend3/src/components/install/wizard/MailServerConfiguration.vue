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
				:label='$t("components.config.smtp.form.enabled")'
				color='primary'
			/>
			<v-text-field
				v-if='customConfig'
				v-model='configuration.host'
				:disabled='[ComponentState.Loading, ComponentState.Reloading, ComponentState.Saving].includes(componentState) || !configuration.enabled'
				:label='$t("components.config.smtp.form.host")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("components.config.smtp.errors.host")),
					(v: string) => ValidationRules.server(v, $t("components.config.smtp.errors.hostInvalid")),
				]'
				:prepend-inner-icon='mdiServer'
				required
			/>
			<v-text-field
				v-if='customConfig'
				v-model.number='configuration.port'
				:disabled='[ComponentState.Loading, ComponentState.Reloading, ComponentState.Saving].includes(componentState) || !configuration.enabled'
				:label='$t("components.config.smtp.form.port")'
				:rules='[
					(v: number|null) => ValidationRules.required(v, $t("components.config.smtp.errors.port")),
					(v: number) => ValidationRules.integer(v, $t("components.config.smtp.errors.port")),
					(v: number) => ValidationRules.between(v, 1, 65535, $t("components.config.smtp.errors.portInvalid")),
				]'
				:prepend-inner-icon='mdiNumeric'
				required
			/>
			<v-text-field
				v-if='customConfig'
				v-model='configuration.username'
				:disabled='[ComponentState.Loading, ComponentState.Reloading, ComponentState.Saving].includes(componentState) || !configuration.enabled'
				:label='$t("components.config.smtp.form.username")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("components.config.smtp.errors.username")),
				]'
				:prepend-inner-icon='mdiAccount'
			/>
			<PasswordInput
				v-if='customConfig'
				v-model='configuration.password'
				:disabled='[ComponentState.Loading, ComponentState.Reloading, ComponentState.Saving].includes(componentState) || !configuration.enabled'
				:label='$t("components.config.smtp.form.password")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("components.config.smtp.errors.password")),
				]'
				:prepend-inner-icon='mdiKey'
			/>
			<SmtpSecurityInput
				v-if='customConfig'
				v-model='configuration.secure'
				:disabled='[ComponentState.Loading, ComponentState.Reloading, ComponentState.Saving].includes(componentState) || !configuration.enabled'
			/>
			<v-text-field
				v-if='customConfig'
				v-model='configuration.from'
				:disabled='[ComponentState.Loading, ComponentState.Reloading, ComponentState.Saving].includes(componentState) || !configuration.enabled'
				:label='$t("components.config.smtp.form.sender")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("components.config.smtp.errors.sender")),
				]'
				:prepend-inner-icon='mdiEmail'
			/>
		</v-form>
		<template #actions='{ next }'>
			<CardActionBtn
				:action='Action.Next'
				:disabled='!formValidity'
				:loading='[ComponentState.Loading, ComponentState.Reloading, ComponentState.Saving].includes(componentState)'
				@click='onSubmit(next)'
			/>
			<CardActionBtn
				:action='Action.Skip'
				class='ml-2'
				:loading='[ComponentState.Loading, ComponentState.Reloading, ComponentState.Saving].includes(componentState)'
				@click='next'
			/>
			<CardActionBtn
				color='info'
				class='ml-2'
				:disabled='!formValidity || defaultConfig'
				:icon='mdiEmailFast'
				:text='$t("components.config.smtp.form.test")'
				:loading='[ComponentState.Loading, ComponentState.Reloading, ComponentState.Saving].includes(componentState)'
				@click='testConfiguration'
			/>
		</template>
	</v-stepper-vertical-item>
</template>

<script lang='ts' setup>
import { MailerService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { ErrorResponse } from '@iqrf/iqrf-gateway-webapp-client/types';
import {
	type MailerConfig,
	type MailerGetConfigResponse, MailerTheme,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {
	mdiAccount,
	mdiEmail,
	mdiEmailFast,
	mdiKey,
	mdiNumeric,
	mdiServer,
} from '@mdi/js';
import { AxiosError } from 'axios';
import { storeToRefs } from 'pinia';
import { onBeforeMount, ref, type Ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { type VForm } from 'vuetify/components';

import SmtpSecurityInput
	from '@/components/config/smtp/SmtpSecurityInput.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import PasswordInput from '@/components/layout/form/PasswordInput.vue';
import UrlBuilder from '@/helpers/urlBuilder';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { useUserStore } from '@/store/user';
import { Action } from '@/types/Action';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const componentProps = defineProps({
	index: {
		type: Number,
		required: true,
	},
});
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
const form: Ref<VForm | null> = ref(null);
const formValidity: Ref<boolean | null> = ref(null);
const i18n = useI18n();
let service: MailerService = useApiClient().getConfigServices().getMailerService();

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
	} catch (error) {
		componentState.value = ComponentState.FetchFailed;
		if (error instanceof AxiosError) {
			const message = (error.response?.data as ErrorResponse | undefined)?.message ?? error.message;
			toast.error(i18n.t('components.config.smtp.messages.fetchFailed', { error: message }));
		}
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
	componentState.value = ComponentState.Saving;
	try {
		if (customConfig.value) {
			await service.updateConfig(configuration.value);
			if (!isVerified.value) {
				await useApiClient().getAccountService().resendVerificationEmail({
					baseUrl: new UrlBuilder().getBaseUrl(),
				});
			}
			toast.success(i18n.t('components.config.smtp.messages.saveSuccess'));
		}
		componentState.value = ComponentState.Ready;
		onClickNext();
	} catch (error) {
		if (error instanceof AxiosError) {
			componentState.value = ComponentState.Ready;
			const message = (error.response?.data as ErrorResponse | undefined)?.message ?? error.message;
			toast.error(i18n.t('components.config.smtp.messages.saveFailed', { error: message }));
		}
	}
}

/**
 * Tests the SMTP configuration
 */
async function testConfiguration(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Reloading;
	try {
		await service.testConfig(configuration.value);
		componentState.value = ComponentState.Ready;
		toast.success(i18n.t('components.config.smtp.messages.testSuccess'));
	} catch (error) {
		if (error instanceof AxiosError) {
			componentState.value = ComponentState.Ready;
			const message = (error.response?.data as ErrorResponse | undefined)?.message ?? error.message;
			toast.error(i18n.t('components.config.smtp.messages.testFailed', { error: message }));
		}
	}
}

</script>
