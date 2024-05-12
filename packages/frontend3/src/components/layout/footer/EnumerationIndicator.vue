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
import { computed, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';

import { useDaemonStore } from '@/store/daemonSocket';

const daemonStore = useDaemonStore();
const i18n = useI18n();

const {
	isEnumerationRunning: active,
	isConnected: connected,
} = storeToRefs(daemonStore);

/// Color of the icon
const color: Ref<string> = computed((): string => {
	if (!connected.value) {
		return 'red-accent-3';
	}
	return 'grey-lighten-1';
});

/// Icon to display
const icon: Ref<string> = computed((): string => {
	if (!connected.value) {
		return mdiDatabaseAlert;
	}
	if (active.value) {
		return mdiDatabaseArrowDown;
	}
	return mdiDatabase;
});

/// Message to display in the tooltip
const message: Ref<string> = computed((): string => {
	if (!connected.value) {
		return i18n.t('components.status.monitor.enumeration.unavailable').toString();
	}
	if (active.value) {
		return i18n.t('components.status.monitor.enumeration.running').toString();
	}
	return i18n.t('components.status.monitor.enumeration.notRunning').toString();
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
