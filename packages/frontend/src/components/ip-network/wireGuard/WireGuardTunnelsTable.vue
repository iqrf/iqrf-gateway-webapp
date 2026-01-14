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
	<ICard header-color='primary'>
		<template #title>
			{{ $t('components.ipNetwork.wireGuard.tunnels.title') }}
		</template>
		<template #titleActions>
			<WireGuardTunnelForm :action='Action.Add' @update-tunnel='updateTunnel' />
			<IActionBtn
				:action='Action.Reload'
				container-type='card-title'
				@click='fetchData'
			/>
		</template>
		<v-skeleton-loader
			class='input-skeleton-loader'
			:loading='componentState === ComponentState.Loading'
			type='table-heading, table-row-divider@2, table-row'
		>
			<v-responsive>
				<IDataTable
					:items='tunnels'
					:headers='headers'
					hide-pagination
					dense
				>
					<template #item.active='{ item }'>
						<BooleanCheckMarker :value='item.active' />
					</template>
					<template #item.enabled='{ item }'>
						<BooleanCheckMarker :value='item.enabled' />
					</template>
					<template #item.actions='{ item }'>
						<WireGuardDeactivateDialog :wg-list-entry='item' @update-active-flag='updateActiveFlag' />
						<WireGuardDisableDialog :wg-list-entry='item' @update-enable-flag='updateEnableFlag' />
						<WireGuardTunnelForm :action='Action.Edit' :tunnel-id='item.id' @update-tunnel='updateTunnel' />
						<WireGuardDeleteTunnelDialog :tunnel='item' @deleted='deleteTunnel' />
					</template>
				</IDataTable>
			</v-responsive>
		</v-skeleton-loader>
	</ICard>
</template>

<script setup lang='ts'>
import {
	WireGuardTunnelConfig,
	type WireGuardTunnelListEntry,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IDataTable,
} from '@iqrf/iqrf-vue-ui';
import { computed, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';

import BooleanCheckMarker from '@/components/BooleanCheckMarker.vue';
import WireGuardDeleteTunnelDialog
	from '@/components/ip-network/wireGuard/WireGuardDeleteTunnelDialog.vue';
import WireGuardTunnelForm from '@/components/ip-network/wireGuard/WireGuardTunnelForm.vue';

import WireGuardDeactivateDialog from './WireGuardDeactivateDialog.vue';
import WireGuardDisableDialog from './WireGuardDisableDialog.vue';

defineProps<{
	/// List of WireGuard tunnels
	tunnels: WireGuardTunnelListEntry[];
}>();

const emit = defineEmits<{
	updateTunnel: [tunnel: WireGuardTunnelConfig, enabled: boolean, active: boolean];
	updateActiveFlag: [tunnelId: number];
	updateEnableFlag: [tunnelId: number];
	deleteTunnel: [tunnelId: number];
	fetchData: [];
}>();

const i18n = useI18n();

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const headers = computed(() => [
	{
		key: 'name',
		title: i18n.t('components.ipNetwork.wireGuard.tunnels.columns.name'),
	},
	{
		key: 'active',
		title: i18n.t('components.ipNetwork.wireGuard.tunnels.columns.active'),
	},
	{
		key: 'enabled',
		title: i18n.t('components.ipNetwork.wireGuard.tunnels.columns.enabled'),
	},
	{
		align: 'end',
		key: 'actions',
		title: i18n.t('common.columns.actions'),
		sortable: false,
	},
]);

/**
 * Passes fetch data event to parent component
 */
async function fetchData(): Promise<void> {
	emit('fetchData');
}

/**
 * Passes update tunnel event to parent component
 * @param {WireGuardTunnelConfig} tunnel tunnel to add to the array
 * @param {boolean} enabled enabled flag - tells if tunnel is enabled
 * @param {boolean} active active flag - tells if tunnel is active
 */
function updateTunnel(tunnel: WireGuardTunnelConfig, enabled: boolean, active: boolean): void {
	emit('updateTunnel', tunnel, enabled, active);
}

/**
 * Passes delete tunnel event to parent component
 * @param {number} tunnelId ID of tunnel to remove
 */
function deleteTunnel(tunnelId: number): void {
	emit('deleteTunnel', tunnelId);
}

/**
 * Passes update enable flag event to parent component
 * @param {number} tunnelId ID of tunnel to update.
 */
function updateEnableFlag(tunnelId: number): void {
	emit('updateEnableFlag', tunnelId);
}

/**
 * Passes update active flag event to parent component
 * @param {number} tunnelId ID of tunnel to update
 */
function updateActiveFlag(tunnelId: number): void {
	emit('updateActiveFlag', tunnelId);
}
</script>
