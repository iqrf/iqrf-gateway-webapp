<!--
Copyright 2017-2023 IQRF Tech s.r.o.
Copyright 2019-2023 MICRORISC s.r.o.

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
			{{ $t('pages.gateway.power.title') }}
		</template>
		<v-btn
			color='error'
			@click='powerOff'
		>
			<v-icon :icon='mdiPower' />
			{{ $t('components.gateway.power.powerOff') }}
		</v-btn> <v-btn
			color='primary'
			@click='reboot'
		>
			<v-icon :icon='mdiReload' />
			{{ $t('components.gateway.power.reboot') }}
		</v-btn>
	</Card>
</template>

<script lang='ts' setup>
import { type PowerService } from '@iqrf/iqrf-gateway-webapp-client/services/Gateway';
import { type PowerActionResponse } from '@iqrf/iqrf-gateway-webapp-client/types/Gateway';
import { mdiPower, mdiReload } from '@mdi/js';
import { type AxiosError } from 'axios';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import Card from '@/components/Card.vue';
import { basicErrorToast } from '@/helpers/errorToast';
import { useApiClient } from '@/services/ApiClient';


const i18n = useI18n();
const service: PowerService = useApiClient().getGatewayServices().getPowerService();

function powerOff(): void {
	service.powerOff()
		.then((response: PowerActionResponse) =>
			toast.success(
				i18n.t('components.gateway.power.messages.powerOffSuccess', {time: parseActionTime(response.timestamp)}).toString(),
			),
		).catch((error: AxiosError) =>
			basicErrorToast(error, 'components.gateway.power.messages.powerOffFailed'),
		);
}

function reboot(): void {
	service.reboot()
		.then((response: PowerActionResponse) =>
			toast.success(
				i18n.t('components.gateway.power.messages.rebootSuccess', {time: parseActionTime(response.timestamp)}).toString(),
			),
		).catch((error: AxiosError) =>
			basicErrorToast(error, 'components.gateway.power.messages.rebootFailed'),
		);
}

/**
 * Converts timestamp to time string
 */
function parseActionTime(timestamp: number): string {
	return new Date(timestamp * 1000).toLocaleTimeString();
}
</script>
