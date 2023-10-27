<template>
	<span v-if='!connected || mode === DaemonMode.Unknown'>
		<v-progress-linear
			color='info'
			indeterminate
			rounded
		/>
	</span>
	<span v-else>
		{{ $t(`components.gateway.mode.modes.${mode}`) }}
	</span>
</template>

<script lang='ts' setup>
import { DaemonMode } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { storeToRefs } from 'pinia';

import { useMonitorStore } from '@/store/monitorSocket';

const monitorStore = useMonitorStore();
const {isConnected: connected, getMode: mode} = storeToRefs(monitorStore);

</script>
