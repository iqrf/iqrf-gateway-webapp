<template>
	<v-chip
		:color
		label
		size='small'
	>
		{{ $t(`components.ipNetwork.modems.columns.states.${state}`) }}
	</v-chip>
</template>

<script setup lang='ts'>
import { ModemState } from '@iqrf/iqrf-gateway-webapp-client/types/Network/Modem';
import { computed, type PropType } from 'vue';

/// Component props
const componentProps = defineProps({
	state: {
		type: String as PropType<ModemState>,
		required: true,
	},
});

/// Badge color
const color = computed(() => {
	switch (componentProps.state) {
		case ModemState.failed:
			return 'error';
		case ModemState.locked:
		case ModemState.unknown:
			return 'warning';
		case ModemState.connected:
			return 'success';
		case ModemState.registered:
			return 'info';
		default:
			return 'secondary';
	}
});
</script>
