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
	<v-form
		ref='form'
		:disabled='[ComponentState.Loading, ComponentState.Reloading].includes(componentState) || !configuration.enabled'
		@submit.prevent='onSubmit'
	>
		<Card>
			<template #title>
				{{ $t('components.configuration.smtp.form.title') }}
			</template>
			<template #titleActions>
				<v-btn
					color='white'
					variant='tonal'
					@click='configuration.enabled = !configuration.enabled'
				>
					{{ stateButtonLabel }}
				</v-btn>
				<CardTitleActionBtn
					:action='Action.Reload'
					:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
					@click='getConfig'
				/>
			</template>
			<v-alert
				v-if='componentState === ComponentState.FetchFailed'
				type='error'
				variant='tonal'
				:text='$t("common.messages.fetchFailed")'
			/>
			<v-skeleton-loader
				class='input-skeleton-loader'
				:loading='componentState === ComponentState.Loading'
				type='heading@6, button@2'
			>
				<v-responsive>
					<v-alert
						v-if='defaultConfig'
						class='mb-4'
						type='warning'
						variant='tonal'
						:text='$t("components.configuration.smtp.defaultConfig")'
					/>
					<v-text-field
						v-model='configuration.host'
						:label='$t("components.configuration.smtp.form.host")'
						:rules='[
							(v: string|null) => ValidationRules.required(v, $t("components.configuration.smtp.errors.host")),
							(v: string) => ValidationRules.server(v, $t("components.configuration.smtp.errors.hostInvalid")),
						]'
						:disabled='[ComponentState.Loading, ComponentState.Reloading].includes(componentState) || !configuration.enabled'
						:prepend-inner-icon='mdiServer'
						required
					/>
					<v-text-field
						v-model.number='configuration.port'
						:label='$t("components.configuration.smtp.form.port")'
						:rules='[
							(v: number|null) => ValidationRules.required(v, $t("components.configuration.smtp.errors.port")),
							(v: number) => ValidationRules.integer(v, $t("components.configuration.smtp.errors.port")),
							(v: number) => ValidationRules.between(v, 1, 65535, $t("components.configuration.smtp.errors.portInvalid")),
						]'
						:disabled='[ComponentState.Loading, ComponentState.Reloading].includes(componentState) || !configuration.enabled'
						:prepend-inner-icon='mdiNumeric'
						required
					/>
					<v-text-field
						v-model='configuration.username'
						:label='$t("components.configuration.smtp.form.username")'
						:rules='[
							(v: string|null) => ValidationRules.required(v, $t("components.configuration.smtp.errors.username")),
						]'
						:disabled='[ComponentState.Loading, ComponentState.Reloading].includes(componentState) || !configuration.enabled'
						:prepend-inner-icon='mdiAccount'
					/>
					<PasswordInput
						v-model='configuration.password'
						:label='$t("components.configuration.smtp.form.password")'
						:rules='[
							(v: string|null) => ValidationRules.required(v, $t("components.configuration.smtp.errors.password")),
						]'
						:disabled='[ComponentState.Loading, ComponentState.Reloading].includes(componentState) || !configuration.enabled'
						:prepend-inner-icon='mdiKey'
					/>
					<SmtpSecurityInput
						v-model='configuration.secure'
						:disabled='[ComponentState.Loading, ComponentState.Reloading].includes(componentState) || !configuration.enabled'
					/>
					<v-text-field
						v-model='configuration.from'
						:label='$t("components.configuration.smtp.form.sender")'
						:rules='[
							(v: string|null) => ValidationRules.required(v, $t("components.configuration.smtp.errors.sender")),
						]'
						:disabled='[ComponentState.Loading, ComponentState.Reloading].includes(componentState) || !configuration.enabled'
						:prepend-inner-icon='mdiEmail'
					/>
					<CardActionBtn
						:action='Action.Save'
						class='mr-1'
						:disabled='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
						type='submit'
					/>
					<CardActionBtn
						color='info'
						:icon='mdiEmailFast'
						:text='$t("components.configuration.smtp.form.test")'
						:disabled='!configuration.enabled || [ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
						@click='testConfiguration'
					/>
				</v-responsive>
			</v-skeleton-loader>
		</Card>
	</v-form>
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
import { computed, onMounted, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { type VForm } from 'vuetify/components';

import SmtpSecurityInput
	from '@/components/config/smtp/SmtpSecurityInput.vue';
import Card from '@/components/layout/card/Card.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import CardTitleActionBtn from '@/components/layout/card/CardTitleActionBtn.vue';
import PasswordInput from '@/components/layout/form/PasswordInput.vue';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { Action } from '@/types/Action';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const form: Ref<VForm | null> = ref(null);
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

const stateButtonLabel = computed(() => {
	if (configuration.value.enabled) {
		return i18n.t('$iqrf.common.actions.disable');
	}
	return i18n.t('$iqrf.common.actions.enable');
});

const service: MailerService = useApiClient().getConfigServices().getMailerService();

onMounted(async () => await getConfig());

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
		}
		componentState.value = ComponentState.Ready;
	} catch (error) {
		componentState.value = ComponentState.FetchFailed;
		if (error instanceof AxiosError) {
			const message = (error.response?.data as ErrorResponse | undefined)?.message ?? error.message;
			toast.error(i18n.t('components.configuration.smtp.messages.fetchFailed', { error: message }));
		}
	}
}

/**
 * Saves the new SMTP configuration
 */
async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Reloading;
	try {
		await service.updateConfig(configuration.value);
		componentState.value = ComponentState.Ready;
		toast.success(i18n.t('components.configuration.smtp.messages.saveSuccess'));
	} catch (error) {
		if (error instanceof AxiosError) {
			componentState.value = ComponentState.Ready;
			const message = (error.response?.data as ErrorResponse | undefined)?.message ?? error.message;
			toast.error(i18n.t('components.configuration.smtp.messages.saveFailed', { error: message }));
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
		toast.success(i18n.t('components.configuration.smtp.messages.testSuccess'));
	} catch (error) {
		if (error instanceof AxiosError) {
			componentState.value = ComponentState.Ready;
			const message = (error.response?.data as ErrorResponse | undefined)?.message ?? error.message;
			toast.error(i18n.t('components.configuration.smtp.messages.testFailed', { error: message }));
		}
	}
}
</script>
