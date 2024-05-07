<template>
	<Card header-color='primary'>
		<template #title>
			{{ $t('components.ipNetwork.wireGuard.tunnels.title') }}
		</template>
		<template #titleActions>
			<v-btn
				color='primary'
				:icon='mdiRefresh'
				variant='elevated'
				@click='fetchData'
			/>
		</template>
		<v-skeleton-loader
			class='input-skeleton-loader'
			:loading='componentState === ComponentState.Loading'
			type='table-heading, table-row-divider@2, table-row'
		>
			<v-responsive>
				<DataTable
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
						<WireGuardDeleteTunnelDialog :tunnel='item' @deleted='fetchData' />
					</template>
				</DataTable>
			</v-responsive>
		</v-skeleton-loader>
	</Card>
</template>

<script setup lang='ts'>
import {
	type WireGuardTunnelListEntry,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network/WireGuard';
import { mdiRefresh } from '@mdi/js';
import { onBeforeMount, type Ref, ref } from 'vue';
import { useI18n } from 'vue-i18n';

import BooleanCheckMarker from '@/components/BooleanCheckMarker.vue';
import Card from '@/components/Card.vue';
import DataTable from '@/components/DataTable.vue';
import WireGuardDeleteTunnelDialog
	from '@/components/ip-network/wireGuard/WireGuardDeleteTunnelDialog.vue';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const i18n = useI18n();
const service = useApiClient().getNetworkServices().getWireGuardService();

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const tunnels: Ref<WireGuardTunnelListEntry[]> = ref([]);
const headers = [
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
];

/**
 * Fetches WireGuard tunnels
 */
function fetchData() {
	componentState.value = ComponentState.Loading;
	service.listTunnels()
		.then((response: WireGuardTunnelListEntry[]): WireGuardTunnelListEntry[] => {
			tunnels.value = response;
			componentState.value = ComponentState.Ready;
			return response;
		});
}

onBeforeMount(() => {
	fetchData();
});
</script>
