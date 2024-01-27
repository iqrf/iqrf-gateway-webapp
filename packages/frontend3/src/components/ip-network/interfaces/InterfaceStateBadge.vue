<template>
	<v-chip
		:color
		label
		size='small'
	>
		{{ $t(`components.ipNetwork.interfaces.columns.states.${state}`) }}
	</v-chip>
</template>

<script setup lang='ts'>
import {
	type NetworkInterfaceState,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network/NetworkInterface';
import { computed, type PropType } from 'vue';

/// Component props
const componentProps = defineProps({
	state: {
		type: String as PropType<NetworkInterfaceState>,
		required: true,
	},
});

/// Badge color
const color = computed(() => {
	const match = componentProps.state.match(/^(?<state>\w+)( (.*))?$/);
	switch (match?.groups?.state) {
		case 'connected':
			return 'success';
		case 'connecting':
			return 'primary';
		case 'deactivating':
			return 'warning';
		case 'disconnected':
			return 'error';
		default:
			return 'gray';
	}
});
</script>
