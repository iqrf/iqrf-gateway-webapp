<template>
	<v-chip
		v-if='state !== null'
		:color
		label
		size='small'
	>
		{{ $t(`components.ipNetwork.connections.columns.states.${state}`) }}
	</v-chip>
</template>

<script setup lang='ts'>
import {
	NetworkConnectionState,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network/NetworkConnection';
import { computed, type PropType } from 'vue';

/// Component props
const componentProps = defineProps({
	state: {
		type: [String, null] as PropType<NetworkConnectionState | null>,
		required: true,
	},
});

/// Badge color
const color = computed(() => {
	switch (componentProps.state) {
		case NetworkConnectionState.Activated:
			return 'success';
		case NetworkConnectionState.Activating:
			return 'primary';
		case NetworkConnectionState.Deactivating:
			return 'warning';
		case NetworkConnectionState.Deactivated:
			return 'error';
		default:
			return 'gray';
	}
});
</script>
