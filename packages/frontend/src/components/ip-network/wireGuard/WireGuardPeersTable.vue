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
			{{ $t('components.ipNetwork.wireGuard.peers.title') }}
		</template>
		<template #titleActions>
			<WireGuardPeerForm :action='Action.Add' :tunnels='tunnels' @update-peer='updatePeer' />
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
					:items='peers'
					:headers='headers'
					hide-pagination
					dense
				>
					<template #item.tunnel='{ item }'>
						<div>{{ tunnels.find((tunnel) => tunnel.id === item.tunnelId)?.name }}</div>
					</template>
					<template #item.actions='{ item }'>
						<WireGuardPeerForm
							:action='Action.Edit'
							:peer-id='item.id'
							:tunnels='tunnels'
							@update-peer='updatePeer'
						/>
						<WireGuardDeletePeerDialog :peer='item' @deleted='removePeer' />
					</template>
				</IDataTable>
			</v-responsive>
		</v-skeleton-loader>
	</ICard>
</template>

<script setup lang='ts'>
import {
	WireGuardPeer,
	type WireGuardTunnelListEntry,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IDataTable,
} from '@iqrf/iqrf-vue-ui';
import { computed, onBeforeMount, ref, type Ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

import { useApiClient } from '@/services/ApiClient';

import WireGuardDeletePeerDialog from './WireGuardDeletePeerDialog.vue';
import WireGuardPeerForm from './WireGuardPeerForm.vue';

const componentProps = defineProps<{
	tunnels: WireGuardTunnelListEntry[];
}>();

const i18n = useI18n();
const service = useApiClient().getNetworkServices().getWireGuardService();

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const peers: Ref<WireGuardPeer[]> = ref([]);
const headers = computed(() => [
	{
		key: 'publicKey',
		title: i18n.t('components.ipNetwork.wireGuard.peers.columns.publicKey'),
	},
	{
		key: 'endpoint',
		title: i18n.t('components.ipNetwork.wireGuard.peers.columns.endpoint'),
	},
	{
		key: 'port',
		title: i18n.t('components.ipNetwork.wireGuard.peers.columns.port'),
	},
	{
		key: 'tunnel',
		title: i18n.t('components.ipNetwork.wireGuard.peers.columns.tunnel'),
	},
	{
		align: 'end',
		key: 'actions',
		title: i18n.t('common.columns.actions'),
		sortable: false,
	},
]);

/**
 * Fetches WireGuard tunnels
 */
async function fetchData(): Promise<void> {
	componentState.value = ComponentState.Loading;
	try {
		peers.value = await service.getAllPeers();
		componentState.value = ComponentState.Ready;
	} catch {
		componentState.value = ComponentState.FetchFailed;
	}
}

/**
 * Updates or adds WireGuard peer to array of peers.
 * @param {WireGuardPeer} peer peer to update
 */
function updatePeer(peer: WireGuardPeer): void {
	// Update the peer
	for (let i = 0; i < peers.value.length; i++) {
		if (peers.value[i].id === peer.id) {
			peers.value[i] = peer;
			return;
		}
	}
	// Add new peer if doesn't exist
	peers.value.push(peer);
}

/**
 * Removes WireGuard peer from array of peers.
 * @param {number} peerId ID of peer to remove
 */
function removePeer(peerId: number): void {
	for (let i = 0; i < peers.value.length; i++) {
		if (peers.value[i].id === peerId) {
			peers.value.splice(i, 1);
			return;
		}
	}
}

watch(
	() => componentProps.tunnels.map((tunnel) => tunnel.id),
	(tunnelIds) => {
		// If tunnels were removed, remove corresponding peers
		let len = peers.value.length;
		for (let i = 0; i < len; i++) {
			if (tunnelIds.includes(peers.value[i].tunnelId)) {
				continue;
			}
			peers.value.splice(i, 1);
			len -= 1;
			i -= 1;
		}
	},
);

onBeforeMount(async () => await fetchData());
</script>
