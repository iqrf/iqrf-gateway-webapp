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
	<WireGuardTunnelsTable
		class='mb-3'
		:tunnels='tunnels'
		:parent-state='componentState'
		@delete-tunnel='deleteTunnel'
		@fetch-data='fetchData'
		@update-active-flag='updateActiveFlag'
		@update-enable-flag='updateEnableFlag'
		@update-tunnel='updateTunnel'
	/>
	<WireGuardPeersTable :tunnels='tunnels' />
</template>

<script lang='ts' setup>
import { WireGuardTunnelConfig, WireGuardTunnelListEntry } from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import { ComponentState } from '@iqrf/iqrf-vue-ui';
import { onBeforeMount, ref, Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import WireGuardPeersTable from '@/components/ip-network/wireGuard/WireGuardPeersTable.vue';
import WireGuardTunnelsTable
	from '@/components/ip-network/wireGuard/WireGuardTunnelsTable.vue';
import { useApiClient } from '@/services/ApiClient';


const service = useApiClient().getNetworkServices().getWireGuardService();
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const tunnels: Ref<WireGuardTunnelListEntry[]> = ref([]);
const i18n = useI18n();

/**
 * Fetches WireGuard tunnels
 */
async function fetchData(): Promise<void> {
	componentState.value = ComponentState.Loading;
	try {
		tunnels.value = await service.listTunnels();
		componentState.value = ComponentState.Ready;
	} catch {
		componentState.value = ComponentState.FetchFailed;
		toast.error(i18n.t('common.messages.fetchFailed'));
	}
}

/**
 * Generalized search function for updating state of the tunnels.
 * @param {number} tunnelId ID of tunnel to find and modify
 * @param {number} callback Function for modification of the tunnel, prototype: function modify(index: number): void { ... }
 * @return {boolean} true if tunnel with given ID was found, false otherwise
 */
function findAndModifyTunnel(tunnelId: number, callback: Function): boolean {
	for (let i = 0; i < tunnels.value.length; i++) {
		if (tunnels.value[i].id === tunnelId) {
			callback(i);
			return true;
		}
	}
	return false;
}

/**
 * Updates or adds WireGuard tunnel to array of tunnels.
 * @param {WireGuardTunnelConfig} tunnel tunnel to add to the array
 * @param {boolean} enabled enabled flag - tells if tunnel is enabled
 * @param {boolean} active active flag - tells if tunnel is active
 */
function updateTunnel(tunnel: WireGuardTunnelConfig, enabled: boolean, active: boolean): void {
	// Create tunnel list entry from the full configuration
	const tunnelListEntry: WireGuardTunnelListEntry = {
		id: tunnel.id!,
		name: tunnel.name,
		enabled: enabled,
		active: enabled || active,
		stack: tunnel.stack,
	};
	// update the entry
	const found = findAndModifyTunnel(tunnelListEntry.id, (index: number) => {
		tunnels.value[index] = tunnelListEntry;
	});
	// Add tunnel if doesn't exist
	if (!found) {
		tunnels.value.push(tunnelListEntry);
	}
}

/**
 * Removes WireGuard tunnel from the array of tunnels.
 * @param {number} tunnelId ID of tunnel to remove
 */
function deleteTunnel(tunnelId: number): void {
	findAndModifyTunnel(tunnelId, (index: number) => tunnels.value.splice(index, 1));
}

/**
 * Updates enabled and also active flags according to the current status (flips the enabled flag).
 * Enabling and disabling also activates or deactivates the tunnel.
 * @param {number} tunnelId ID of tunnel to update.
 */
function updateEnableFlag(tunnelId: number): void {
	findAndModifyTunnel(tunnelId, (index: number) => {
		const record = tunnels.value[index];
		if (record.enabled) {
			record.enabled = false;
			record.active = false;
		} else {
			record.enabled = true;
			record.active = true;
		}
	});
}

/**
 * Flips the active flag for given tunnel.
 * @param {number} tunnelId ID of tunnel to update
 */
function updateActiveFlag(tunnelId: number): void {
	findAndModifyTunnel(tunnelId, (index: number) => {
		tunnels.value[index].active = !tunnels.value[index].active;
	});
}

onBeforeMount(async () => await fetchData());
</script>
