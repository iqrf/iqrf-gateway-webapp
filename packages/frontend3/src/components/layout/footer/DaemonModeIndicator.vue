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
		v-tooltip:bottom='`${$t("components.status.monitor.mode") }: ${ $t(`components.status.monitor.modes.${monitorStore.mode}`)}`'
		class='me-2'
		:color='color'
		:icon='icon'
		size='small'
	/>
</template>

<script lang='ts' setup>
import { DaemonMode } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import {
	mdiCheckCircleOutline, mdiForward, mdiHelp, mdiWrench,
} from '@mdi/js';
import { computed, type Ref } from 'vue';

import { useMonitorStore } from '@/store/monitorSocket';

const monitorStore = useMonitorStore();

/// Icon to display
const icon: Ref<string> = computed((): string => {
	const mode: DaemonMode = monitorStore.mode;
	switch (mode) {
		case DaemonMode.Operational:
			return mdiCheckCircleOutline;
		case DaemonMode.Forwarding:
			return mdiForward;
		case DaemonMode.Service:
			return mdiWrench;
		default:
			return mdiHelp;
	}
});

/// Color of the icon
const color: Ref<string> = computed((): string => {
	const mode: DaemonMode = monitorStore.mode;
	switch (mode) {
		case DaemonMode.Operational:
		case DaemonMode.Forwarding:
			return 'light-green-accent-3';
		case DaemonMode.Service:
			return 'warning';
		default:
			return 'red-accent-3';
	}
});
</script>
