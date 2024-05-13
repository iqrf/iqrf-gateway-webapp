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
	<v-chip
		:color
		label
		size='small'
	>
		{{ $t(`components.ipNetwork.interfaces.columns.states.${state}`) }}
	</v-chip>
</template>

<script setup lang='ts'>
import {
	type NetworkInterfaceState,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network/NetworkInterface';
import { computed, type PropType } from 'vue';

/// Component props
const componentProps = defineProps({
	state: {
		type: String as PropType<NetworkInterfaceState>,
		required: true,
	},
});

/// Badge color
const color = computed(() => {
	const match = componentProps.state.match(/^(?<state>\w+)( (.*))?$/);
	switch (match?.groups?.state) {
		case 'connected':
			return 'success';
		case 'connecting':
			return 'primary';
		case 'deactivating':
			return 'warning';
		case 'disconnected':
			return 'error';
		default:
			return 'gray';
	}
});
</script>
