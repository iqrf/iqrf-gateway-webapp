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
		v-if='state !== null'
		:color
		label
		size='small'
	>
		{{ text }}
	</v-chip>
</template>

<script setup lang='ts'>
import {
	NetworkConnectionState,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import { computed, type ComputedRef } from 'vue';
import { useI18n } from 'vue-i18n';

/// Component props
const componentProps = defineProps<{
	state: NetworkConnectionState | null;
}>();
const i18n = useI18n();

/// Badge color
const color: ComputedRef<string> = computed((): string => {
	const data: Record<NetworkConnectionState, string> = {
		[NetworkConnectionState.Activated]: 'success',
		[NetworkConnectionState.Activating]: 'primary',
		[NetworkConnectionState.Deactivating]: 'warning',
		[NetworkConnectionState.Deactivated]: 'error',
		[NetworkConnectionState.Unknown]: 'gray',
	};
	return componentProps.state ? data[componentProps.state] ?? 'gray' : 'gray';
});

// Badge text
const text: ComputedRef<string> = computed((): string => {
	const data: Record<NetworkConnectionState, string> = {
		[NetworkConnectionState.Activated]: i18n.t('components.ipNetwork.connections.columns.states.activated'),
		[NetworkConnectionState.Activating]: i18n.t('components.ipNetwork.connections.columns.states.activating'),
		[NetworkConnectionState.Deactivating]: i18n.t('components.ipNetwork.connections.columns.states.deactivating'),
		[NetworkConnectionState.Deactivated]: i18n.t('components.ipNetwork.connections.columns.states.deactivated'),
		[NetworkConnectionState.Unknown]: i18n.t('components.ipNetwork.connections.columns.states.unknown'),
	};
	return componentProps.state ? data[componentProps.state] ?? '' : '';
});
</script>
