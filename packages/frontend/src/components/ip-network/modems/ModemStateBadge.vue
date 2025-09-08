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
	<v-chip
		:color
		label
		size='small'
	>
		{{ $t(`components.ipNetwork.modems.columns.states.${state}`) }}
	</v-chip>
</template>

<script setup lang='ts'>
import { ModemState } from '@iqrf/iqrf-gateway-webapp-client/types/Network';
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
