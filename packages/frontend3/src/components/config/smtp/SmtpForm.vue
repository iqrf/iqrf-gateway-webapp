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
		:disabled='loading || !configuration.enabled'
		@submit.prevent='onSubmit'
	>
		<Card v-if='install'>
			<template #title>
				{{ $t('pages.install.smtp.title') }}
			</template>
			<template #titleActions>
				<v-btn
					size='small'
					@click='configuration.enabled = !configuration.enabled'
				>
					{{ stateButtonLabel }}
				</v-btn>
			</template>
			<SmtpFormElements :config='configuration' :loading='loading' />
			<v-btn
				class='mr-1'
				color='primary'
				variant='elevated'
				:disabled='loading'
				@click='onSubmit'
			>
				{{ $t('common.buttons.save') }}
			</v-btn>
			<v-btn
				class='mr-1'
				color='info'
				variant='elevated'
				:disabled='!configuration.enabled || loading'
				@click='testConfiguration'
			>
				{{ $t('components.configuration.smtp.test') }}
			</v-btn>
			<v-btn
				v-if='install'
				color='grey'
				variant='elevated'
				@click='toNextStep'
			>
				{{ $t('common.buttons.skip') }}
			</v-btn>
		</Card>
		<Card v-else header-color='primary'>
			<template #title>
				{{ $t('pages.configuration.smtp.title') }}
			</template>
			<template #titleActions>
				<v-btn
					size='small'
					@click='configuration.enabled = !configuration.enabled'
				>
					{{ stateButtonLabel }}
				</v-btn>
			</template>
			<SmtpFormElements :config='configuration' :loading='loading' />
			<template #actions>
				<v-btn
					color='primary'
					variant='elevated'
					:disabled='loading'
					@click='onSubmit'
				>
					{{ $t('common.buttons.save') }}
				</v-btn>
				<v-btn
					color='info'
					variant='elevated'
					:disabled='!configuration.enabled || loading'
					@click='testConfiguration'
				>
					{{ $t('components.configuration.smtp.test') }}
				</v-btn>
			</template>
		</Card>
	</v-form>
</template>

<script lang='ts' setup>
import { type MailerService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { type MailerConfig, type MailerGetConfigResponse } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { computed, onMounted, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import SmtpFormElements from '@/components/config/smtp/SmtpFormElements.vue';
import Card from '@/components/layout/card/Card.vue';
import { validateForm } from '@/helpers/validateForm';
import router from '@/router';
import { useApiClient } from '@/services/ApiClient';
import { useInstallStore } from '@/store/install';

const installStore = useInstallStore();
const props = defineProps({
	install: {
		type: Boolean,
		default: false,
		required: false,
	},
});
const i18n = useI18n();
const loading: Ref<boolean> = ref(false);
const configuration: Ref<MailerConfig> = ref({
	enabled: false,
	host: '',
	port: 465,
	username: '',
	password: '',
	secure: null,
	from: '',
	theme: 'generic',
	clientHost: null,
});

const form: Ref<VForm | null> = ref(null);
const stateButtonLabel = computed(() => {
	if (configuration.value.enabled) {
		return i18n.t('common.states.enabled');
	}
	return i18n.t('common.states.disabled');
});

const service: MailerService = useApiClient().getConfigServices().getMailerService();

onMounted(() => {
	getConfig();
});

function getConfig(): void {
	loading.value = true;
	service.getConfig()
		.then((response: MailerGetConfigResponse) => {
			configuration.value = response.config;
			loading.value = false;
		})
		.catch(() => {
			loading.value = false;
			toast.error('TODO FETCH ERROR HANDLING');
		});
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	loading.value = true;
	service.updateConfig(configuration.value)
		.then(() => {
			loading.value = false;
			toast.success(
				i18n.t('components.configuration.smtp.messages.save.success'),
			);
			if (props.install) {
				toNextStep();
			}
		})
		.catch(() => {
			loading.value = false;
			toast.error('TODO SAVE ERROR HANDLING');
		});
}

async function testConfiguration(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	loading.value = true;
	service.testConfig(configuration.value)
		.then(() => {
			loading.value = false;
			toast.success(
				i18n.t('configuration.smtp.messages.test.success'),
			);
		})
		.catch(() => {
			loading.value = false;
			toast.error('TODO TEST ERROR HANDLING');
		});
}

function toNextStep(): void {
	const nextStep = installStore.getNextStep;
	if (nextStep === null) {
		router.push('/');
		return;
	}
	router.push({ name: nextStep.route });
}

</script>
