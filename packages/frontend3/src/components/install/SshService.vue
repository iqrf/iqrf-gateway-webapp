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
	<Card>
		<template #title>
			{{ $t('pages.install.sshService.title') }}
		</template>
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
import { type ServiceService } from '@iqrf/iqrf-gateway-webapp-client/services';
import { type AxiosError } from 'axios';
import { ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';

import Card from '@/components/layout/card/Card.vue';
import { ServiceAction } from '@/enums/controls';
import { useApiClient } from '@/services/ApiClient';
import { useInstallStore } from '@/store/install';

const i18n = useI18n();
const installStore = useInstallStore();
const router = useRouter();
const service: ServiceService = useApiClient().getServiceService();
const status: Ref<ServiceAction> = ref(ServiceAction.Disable);
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
	},
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
	router.push({ name: nextStep.route });
}

</script>
