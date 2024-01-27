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
const componentProps = defineProps({
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
	await serviceService.restart('ModemManager')
		.then(async () => {
			await new Promise(resolve => setTimeout(resolve, 15_000));
			emit('reload');
		})
		.catch(() => {
			emit('reload');
		});
}

</script>
