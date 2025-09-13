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
	<v-btn
		v-if='hasBrokenModem'
		:icon='mdiWifiRefresh'
		color='warning'
		variant='elevated'
		@click='restartModemManager()'
	/>
</template>

<script setup lang='ts'>
import { mdiWifiRefresh } from '@mdi/js';
import { computed } from 'vue';

import { useApiClient } from '@/services/ApiClient';
import { useGatewayStore } from '@/store/gateway';

const gatewayStore = useGatewayStore();
const serviceService = useApiClient().getServiceService();

/// Checks if the used modem is broken to prevent hanging on
const hasBrokenModem = computed(() => {
	return gatewayStore.board === 'MICRORISC s.r.o. IQD-GW04';
});
/// Define emits
const emit = defineEmits(['restart', 'reload']);
/// Define props
defineProps({
	disabled: {
		type: Boolean,
		default: false,
		required: false,
	},
});

/**
 * Restarts the ModemManager service
 */
async function restartModemManager(): Promise<void> {
	emit('restart');
	try {
		await serviceService.restart('ModemManager');
		await new Promise(resolve => setTimeout(resolve, 15_000));
		emit('reload');
	} catch {
		emit('reload');
	}
}

</script>
