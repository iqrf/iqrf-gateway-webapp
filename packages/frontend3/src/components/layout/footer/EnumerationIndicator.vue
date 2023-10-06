<template>
	<v-tooltip location='bottom'>
		<template #activator='{ props }'>
			<v-icon
				v-bind='props'
				:class='active ? "blink-text me-2" : "me-2"'
				:color='active ? undefined : color'
				size='small'
			>
				{{ icon }}
			</v-icon>
		</template>
		{{ $t(message) }}
	</v-tooltip>
</template>

<script lang='ts' setup>
import { useDaemonStore } from '@/store/daemonSocket';
import { mdiDatabase, mdiDatabaseAlert, mdiDatabaseArrowDown } from '@mdi/js';
import { storeToRefs } from 'pinia';
import { computed } from 'vue';

const daemonStore = useDaemonStore();
const { isEnumerationRunning: active, isConnected: connected } = storeToRefs(daemonStore);
const color = computed(() => {
	if (!connected.value) {
		return 'red-accent-3';
	}
	return 'grey-lighten-1';
});
const icon = computed(() => {
	if (!connected.value) {
		return mdiDatabaseAlert;
	}
	if (active.value) {
		return mdiDatabaseArrowDown;
	}
	return mdiDatabase;
});
const message = computed(() => {
	if (!connected.value) {
		return 'components.status.monitor.enumeration.unavailable';
	}
	if (active.value) {
		return 'components.status.monitor.enumeration.running';
	}
	return 'components.status.monitor.enumeration.notRunning';
});

</script>

<style scoped>
.blink-text {
	color: black;
	animation: blink 1s ease infinite;
}

@keyframes blink{
	0% {color: #009933;}
	17% {color: #00cc00;}
	34% {color: #00ff00;}
	50% {color: #66ff66;}
	67% {color: #00ff00;}
	84% {color: #00cc00;}
	100% {color: #009933;}
}
</style>
