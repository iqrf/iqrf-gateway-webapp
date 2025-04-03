<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

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
	<v-progress-linear
		v-if='connected && mode === DaemonMode.Unknown'	
		color='info'
		indeterminate
		rounded
	/>		
	<v-chip
		v-else
		:color='chipColor'
		label
	>
		{{ chipText }}
	</v-chip>
</template>

<script lang='ts' setup>
import { DaemonMode } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { storeToRefs } from 'pinia';

import { useMonitorStore } from '@/store/monitorSocket';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const i18n = useI18n();
const monitorStore = useMonitorStore();
const { isConnected: connected, getMode: mode } = storeToRefs(monitorStore);

const chipColor = computed(() => {
	if (!connected.value) {
		return 'error';
	}
	if ([DaemonMode.Forwarding, DaemonMode.Operational].includes(mode.value)) {
		return 'success';
	}
	return 'warning';
})

const chipText = computed(() => {
	if (!connected.value) {
		return i18n.t('components.gateway.mode.modes.unknown');
	}
	return i18n.t(`components.gateway.mode.modes.${mode.value}`);
});

</script>
