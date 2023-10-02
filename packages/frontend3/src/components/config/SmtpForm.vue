<template>
	<v-form
		ref='form'
		@submit.prevent='onSubmit'
		:disabled='loading || !configuration.enabled'
	>
		<Card v-if='install'>
			<template #title>
				{{ $t('pages.install.smtp.title') }}
				<v-btn
					class='float-right'
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
				{{ $t('generic.button.save') }}
			</v-btn>
			<v-btn
				class='mr-1'
				color='info'
				variant='elevated'
				:disabled='!configuration.enabled || loading'
				@click='testConfiguration'
			>
				{{ $t('configuration.smtp.form.test') }}
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
				{{ $t('configuration.smtp.title') }}
				<v-btn
					class='float-right'
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
					{{ $t('configuration.smtp.form.test') }}
				</v-btn>
			</template>
		</Card>
	</v-form>
</template>

<script lang='ts' setup>
import { MailerConfig } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { MailerService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { AxiosError } from 'axios';
import { computed, onMounted, ref, Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';

import { basicErrorToast } from '@/helpers/errorToast';
import { useApiClient } from '@/services/ApiClient';
import { validateForm } from '@/helpers/validateForm';
import SmtpFormElements from './SmtpFormElements.vue';
import router from '@/router';
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

const form: Ref<typeof VForm | null> = ref(null);
const stateButtonLabel = computed(() => {
	if (configuration.value.enabled) {
		return i18n.t('generic.properties.enabled');
	}
	return i18n.t('generic.properties.disabled');
});

const service: MailerService = useApiClient().getConfigServices().getMailerService();

onMounted(() => {
	getConfig();
});

function getConfig(): void {
	loading.value = true;
	service.getConfig()
		.then((config: MailerConfig) => {
			configuration.value = config;
			loading.value = false;
		})
		.catch((error: AxiosError) => {
			loading.value = false;
			basicErrorToast(error, 'configuration.smtp.messages.fetchFailed');
		});
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	loading.value = true;
	service.editConfig(configuration.value)
		.then(() => {
			loading.value = false;
			toast.success(
				i18n.t('configuration.smtp.messages.saveSuccess')
			);
			if (props.install) {
				toNextStep();
			}
		})
		.catch((error: AxiosError) => {
			loading.value = false;
			basicErrorToast(error, 'configuration.smtp.messages.saveFailed');
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
				i18n.t('configuration.smtp.messages.testSuccess')
			);
		})
		.catch((error: AxiosError) => {
			loading.value = false;
			basicErrorToast(error, 'configuration.smtp.messages.testFailed');
		});
}

function toNextStep(): void {
	const nextStep = installStore.getNextStep;
	if (nextStep === null) {
		router.push('/');
		return;
	}
	router.push({name: nextStep.route});
}

</script>
