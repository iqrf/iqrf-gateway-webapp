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
	<Card>
		<template #title>
			{{ $t('pages.gateway.power.title') }}
		</template>
		<PowerOffDialog class='mr-2' @confirm='powerOff' />
		<RebootDialog @confirm='reboot' />
	</Card>
</template>

<script lang='ts' setup>
import { type PowerService } from '@iqrf/iqrf-gateway-webapp-client/services/Gateway';
import { type PowerActionResponse } from '@iqrf/iqrf-gateway-webapp-client/types/Gateway';
import { type AxiosError } from 'axios';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import PowerOffDialog from '@/components/gateway/power/PowerOffDialog.vue';
import RebootDialog from '@/components/gateway/power/RebootDialog.vue';
import Card from '@/components/layout/card/Card.vue';
import { basicErrorToast } from '@/helpers/errorToast';
import { useApiClient } from '@/services/ApiClient';

/// Internalization instance
const i18n = useI18n();
/// Power service
const service: PowerService = useApiClient().getGatewayServices().getPowerService();

/**
 * Powers off the gateway
 */
async function powerOff(): Promise<void> {
	try {
		const response: PowerActionResponse = await service.powerOff();
		toast.success(
			i18n.t('components.gateway.power.powerOff.messages.success', { time: i18n.d(response.timestamp.toJSDate(), 'time') }),
		);
	} catch {
		toast.error(i18n.t('components.gateway.power.powerOff.messages.failed'));
	}
}

/**
 * Reboots the gateway
 */
async function reboot(): Promise<void> {
	try {
		const response: PowerActionResponse = await service.reboot();
		toast.success(
			i18n.t('components.gateway.power.reboot.messages.success', { time: i18n.d(response.timestamp.toJSDate(), 'time') }),
		);
	} catch {
		toast.error(i18n.t('components.gateway.power.reboot.messages.failed'));
	}
}
</script>
