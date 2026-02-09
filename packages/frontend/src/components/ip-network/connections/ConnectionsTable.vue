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
			{{ $t(`components.ipNetwork.connections.titles.${type}`) }}
		</template>
		<template #titleActions>
			<ConnectionForm :action='Action.Add' :type='componentProps.type' @change='fetchData' />
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
					:items='connections'
					:headers='headers'
					dense
				>
					<template #item.state='{ item }'>
						<ConnectionStateBadge :state='item.state' />
					</template>
					<template #item.actions='{ item }'>
						<ConnectionActionButton :connection='item' @change='fetchData' />
						<ConnectionForm :action='Action.Edit' :type='componentProps.type' :uuid='item.uuid' />
						<ConnectionDeleteDialog :connection='item' @deleted='fetchData' />
					</template>
				</IDataTable>
			</v-responsive>
		</v-skeleton-loader>
	</ICard>
</template>

<script setup lang='ts'>
import {
	type NetworkConnectionListEntry,
	type NetworkConnectionType,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IDataTable,
} from '@iqrf/iqrf-vue-ui';
import { computed, onBeforeMount, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';

import ConnectionActionButton
	from '@/components/ip-network/connections/ConnectionActionButton.vue';
import ConnectionDeleteDialog
	from '@/components/ip-network/connections/ConnectionDeleteDialog.vue';
import ConnectionForm
	from '@/components/ip-network/connections/ConnectionForm.vue';
import ConnectionStateBadge
	from '@/components/ip-network/connections/ConnectionStateBadge.vue';
import { useApiClient } from '@/services/ApiClient';

/// Component props
const componentProps = withDefaults(
	defineProps<{
		/// Network connection type filter
		type?: NetworkConnectionType | null;
	}>(),
	{
		type: null,
	},
);
/// Internationalization instance
const i18n = useI18n();
/// Network connection service
const service = useApiClient().getNetworkServices().getNetworkConnectionService();

/// Component state
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
/// Network connections
const connections: Ref<NetworkConnectionListEntry[]> = ref([]);
/// Data table headers
const headers = computed(() => [
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
]);

/**
 * Fetches network interfaces
 */
async function fetchData(): Promise<void> {
	componentState.value = ComponentState.Loading;
	try {
		connections.value = await service.list(componentProps.type);
		componentState.value = ComponentState.Ready;
	} catch {
		componentState.value = ComponentState.FetchFailed;
	}
}

onBeforeMount(async (): Promise<void> => await fetchData());
</script>
