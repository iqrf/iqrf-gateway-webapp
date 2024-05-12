<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
	<v-icon
		v-tooltip:bottom='$t("components.status.monitor.queue") + ": " + (daemonStore.isConnected ? monitorStore.queueLength : "N/A")'
		class='me-2'
		:color='color'
		:icon='icon'
		size='small'
	/>
</template>

<script lang='ts' setup>
import { mdiTray, mdiTrayAlert, mdiTrayFull, mdiTrayRemove } from '@mdi/js';
import { storeToRefs } from 'pinia';
import { computed } from 'vue';

import { useDaemonStore } from '@/store/daemonSocket';
import { useMonitorStore } from '@/store/monitorSocket';

const daemonStore = useDaemonStore();
const monitorStore = useMonitorStore();

const { isConnected } = storeToRefs(daemonStore);
const { queueLength } = storeToRefs(monitorStore);

/// Icon to display
const icon = computed(() => {
	if (!isConnected.value) {
		return mdiTrayRemove;
	}
	if (queueLength.value <= 16) {
		return mdiTray;
	} else if (queueLength.value <= 24) {
		return mdiTrayFull;
	} else {
		return mdiTrayAlert;
	}
});

/// Color of the icon
const color = computed(() => {
	if (!isConnected.value) {
		return 'red-accent-3';
	}
	if (queueLength.value <= 16) {
		return 'light-green-accent-3';
	} else if (queueLength.value <= 24) {
		return 'warning';
	} else {
		return 'red-accent-3';
	}
});
</script>
