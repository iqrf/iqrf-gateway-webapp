<template>
	<v-icon
		v-tooltip:bottom='message'
		:class='active ? "blink-text me-2" : "me-2"'
		:color='active ? undefined : color'
		:icon='icon'
		size='small'
	/>
</template>

<script lang='ts' setup>
import { mdiDatabase, mdiDatabaseAlert, mdiDatabaseArrowDown } from '@mdi/js';
import { storeToRefs } from 'pinia';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

import { useDaemonStore } from '@/store/daemonSocket';
import { useMonitorStore } from '@/store/monitorSocket';

const daemonStore = useDaemonStore();
const i18n = useI18n();
const monitorStore = useMonitorStore();
const { isConnected: connected } = storeToRefs(daemonStore);
const { getDataReadingInProgress: active } = storeToRefs(monitorStore);
const color = computed(() => {
	if (!connected.value) {
		return 'red-accent-3';
	}
	if (active.value) {
		return;
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
		return i18n.t('components.status.monitor.data-collecting.unavailable');
	}
	if (active.value) {
		return i18n.t('components.status.monitor.data-collecting.running');
	}
	return i18n.t('components.status.monitor.data-collecting.notRunning');
});
</script>

<style scoped>
.blink-text {
	color: black;
	animation: blink 1s ease infinite;
}

@keyframes blink{
	0% { color: #009933; }
	17% {	color: #00cc00; }
	34% { color: #00ff00; }
	50% { color: #66ff66; }
	67% { color: #00ff00; }
	84% { color: #00cc00; }
	100% { color: #009933; }
}
</style>
