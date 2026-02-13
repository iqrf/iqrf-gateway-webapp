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
		{{ text }}
	</v-chip>
</template>

<script setup lang='ts'>
import {
	NetworkInterfaceState,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import { computed, type ComputedRef } from 'vue';
import { useI18n } from 'vue-i18n';

/// Component props
const componentProps = defineProps<{
	state: NetworkInterfaceState;
}>();
const i18n = useI18n();

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
			return 'secondary';
	}
});
/// Badge text
const text: ComputedRef<string> = computed((): string => {
	const data: Record<NetworkInterfaceState, string> = {
		[NetworkInterfaceState.CheckingIpConnectivity]: i18n.t('components.ipNetwork.interfaces.columns.states.connecting (checking IP connectivity)'),
		[NetworkInterfaceState.Configuring]: i18n.t('components.ipNetwork.interfaces.columns.states.connecting (configuring)'),
		[NetworkInterfaceState.Connected]: i18n.t('components.ipNetwork.interfaces.columns.states.connected'),
		[NetworkInterfaceState.Deactivating]: i18n.t('components.ipNetwork.interfaces.columns.states.deactivating'),
		[NetworkInterfaceState.Disconnected]: i18n.t('components.ipNetwork.interfaces.columns.states.disconnected'),
		[NetworkInterfaceState.Failed]: i18n.t('components.ipNetwork.interfaces.columns.states.failed'),
		[NetworkInterfaceState.GettingIpConfiguration]: i18n.t('components.ipNetwork.interfaces.columns.states.connecting (getting IP configuration)'),
		[NetworkInterfaceState.NeedAuthentication]: i18n.t('components.ipNetwork.interfaces.columns.states.connecting (need authentication)'),
		[NetworkInterfaceState.Prepare]: i18n.t('components.ipNetwork.interfaces.columns.states.connecting (prepare)'),
		[NetworkInterfaceState.StartingSecondaries]: i18n.t('components.ipNetwork.interfaces.columns.states.connecting (starting secondary connections)'),
		[NetworkInterfaceState.Unavailable]: i18n.t('components.ipNetwork.interfaces.columns.states.unavailable'),
		[NetworkInterfaceState.Unknown]: i18n.t('components.ipNetwork.interfaces.columns.states.unknown'),
		[NetworkInterfaceState.Unmanaged]: i18n.t('components.ipNetwork.interfaces.columns.states.unmanaged'),
	};
	return data[componentProps.state] ?? '';
});
</script>
