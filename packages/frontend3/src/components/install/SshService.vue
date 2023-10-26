<template>
	<Card>
		<template #title>{{ $t('pages.install.sshService.title') }}</template>
		{{ $t('components.sshService.note') }}
		<v-form @submit.prevent='setServiceStatus'>
			<v-radio-group
				v-model='status'
			>
				<v-radio
					v-for='(option, i) of options'
					:key='i'
					:value='option.value'
					:label='option.text'
				/>
			</v-radio-group>
			<v-btn
				color='primary'
				type='submit'
			>
				{{ $t('common.buttons.save') }}
			</v-btn>
			<v-btn
				class='ml-1'
				color='grey'
				variant='elevated'
				@click='toNextStep'
			>
				{{ $t('common.buttons.skip') }}
			</v-btn>
		</v-form>
	</Card>
</template>

<script lang='ts' setup>
import { ref, Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import Card from '@/components/Card.vue';

import { ServiceAction } from '@/enums/controls';
import { useApiClient } from '@/services/ApiClient';

import { AxiosError } from 'axios';
import { ServiceService } from '@iqrf/iqrf-gateway-webapp-client/services';
import { useInstallStore } from '@/store/install';

const i18n = useI18n();
const installStore = useInstallStore();
const router = useRouter();
const service: ServiceService = useApiClient().getServiceService();
const status:  Ref<ServiceAction> = ref(ServiceAction.Disable);
const options = [
	{
		value: ServiceAction.Enable,
		text: i18n.t('components.sshService.enable').toString(),
	},
	{
		value: ServiceAction.Start,
		text: i18n.t('components.sshService.start').toString(),
	},
	{
		value: ServiceAction.Disable,
		text: i18n.t('components.sshService.disable').toString(),
	}
];

function setServiceStatus(): void {
	if (status.value === ServiceAction.Enable) {
		service.enable('ssh')
			.then(handleSuccess)
			.catch(handleFailure);
	} else if (status.value === ServiceAction.Start) {
		service.start('ssh')
			.then(handleSuccess)
			.catch(handleFailure);
	} else {
		service.disable('ssh')
			.then(handleSuccess)
			.catch(handleFailure);
	}
}


function handleSuccess(): void {
	toNextStep();
}

function handleFailure(error: AxiosError): void {
	console.warn(error);
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
