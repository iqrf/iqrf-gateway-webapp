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
const { isConnected: connected, getMode: mode } = storeToRefs(monitorStore);

</script>
