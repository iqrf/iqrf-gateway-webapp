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
	<Card header-color='primary'>
		<template #title>
			{{ $t(`components.ipNetwork.connections.titles.${type}`) }}
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
					:items='connections'
					:headers='headers'
					dense
				>
					<template #item.state='{ item }'>
						<ConnectionStateBadge :state='item.state' />
					</template>
					<template #item.actions='{ item }'>
						<ConnectionEditBtn :connection='item' />
						<ConnectionDeleteDialog :connection='item' @deleted='fetchData' />
					</template>
				</DataTable>
			</v-responsive>
		</v-skeleton-loader>
	</Card>
</template>

<script setup lang='ts'>
import {
	type NetworkConnectionListEntry,
	type NetworkConnectionType,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network/NetworkConnection';
import { mdiRefresh } from '@mdi/js';
import { onBeforeMount, type PropType, type Ref, ref } from 'vue';
import { useI18n } from 'vue-i18n';

import Card from '@/components/Card.vue';
import DataTable from '@/components/DataTable.vue';
import ConnectionDeleteDialog
	from '@/components/ip-network/connections/ConnectionDeleteDialog.vue';
import ConnectionEditBtn
	from '@/components/ip-network/connections/ConnectionEditBtn.vue';
import ConnectionStateBadge
	from '@/components/ip-network/connections/ConnectionStateBadge.vue';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const componentProps = defineProps({
	type: {
		type: [String, null] as PropType<NetworkConnectionType | null>,
		default: null,
		required: false,
	},
});
const i18n = useI18n();
const service = useApiClient().getNetworkServices().getNetworkConnectionService();

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const connections: Ref<NetworkConnectionListEntry[]> = ref([]);
const headers = [
	{
		key: 'name',
		title: i18n.t('components.ipNetwork.connections.columns.name'),
	},
	{
		key: 'interfaceName',
		title: i18n.t('components.ipNetwork.connections.columns.interfaceName'),
	},
	{
		key: 'state',
		title: i18n.t('components.ipNetwork.connections.columns.state'),
	},
	{
		align: 'end',
		key: 'actions',
		title: i18n.t('common.columns.actions'),
		sortable: false,
	},
];

/**
 * Fetches network interfaces
 */
function fetchData() {
	componentState.value = ComponentState.Loading;
	service.list(componentProps.type)
		.then((response: NetworkConnectionListEntry[]): NetworkConnectionListEntry[] => {
			connections.value = response;
			componentState.value = ComponentState.Ready;
			return response;
		});
}

onBeforeMount(() => {
	fetchData();
});
</script>
