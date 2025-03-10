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
	<Card header-color='primary'>
		<template #title>
			{{ $t(`components.ipNetwork.interfaces.titles.${type}`) }}
		</template>
		<template #titleActions>
			<CardTitleActionBtn
				:action='Action.Reload'
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
					:items='interfaces'
					:headers='headers'
					hide-pagination
					dense
				>
					<template #item.state='{ item }'>
						<InterfaceStateBadge :state='item.state' />
					</template>
				</DataTable>
			</v-responsive>
		</v-skeleton-loader>
	</Card>
</template>

<script setup lang='ts'>
import {
	type NetworkInterface,
	type NetworkInterfaceType,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import { computed, onBeforeMount, type PropType, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';

import InterfaceStateBadge
	from '@/components/ip-network/interfaces/InterfaceStateBadge.vue';
import Card from '@/components/layout/card/Card.vue';
import CardTitleActionBtn from '@/components/layout/card/CardTitleActionBtn.vue';
import DataTable from '@/components/layout/data-table/DataTable.vue';
import { useApiClient } from '@/services/ApiClient';
import { Action } from '@/types/Action';
import { ComponentState } from '@/types/ComponentState';

/// Component props
const componentProps = defineProps({
	type: {
		type: [String, null] as PropType<NetworkInterfaceType | null>,
		default: null,
		required: false,
	},
});
const i18n = useI18n();
const service = useApiClient().getNetworkServices().getNetworkInterfaceService();

/// Component state
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
/// Network interfaces
const interfaces: Ref<NetworkInterface[]> = ref([]);
/// Data table headers
const headers = computed(() => [
	{
		key: 'name',
		title: i18n.t('components.ipNetwork.interfaces.columns.name'),
	},
	{
		key: 'manufacturer',
		title: i18n.t('components.ipNetwork.interfaces.columns.manufacturer'),
	},
	{
		key: 'model',
		title: i18n.t('components.ipNetwork.interfaces.columns.model'),
	},
	{
		key: 'macAddress',
		title: i18n.t('components.ipNetwork.interfaces.columns.macAddress'),
	},
	{
		align: 'end',
		key: 'state',
		title: i18n.t('components.ipNetwork.interfaces.columns.state'),
	},
]);

/**
 * Fetches network interfaces
 */
async function fetchData(): Promise<void> {
	componentState.value = ComponentState.Loading;
	try {
		interfaces.value = await service.list(componentProps.type);
		componentState.value = ComponentState.Ready;
	} catch {
		componentState.value = ComponentState.FetchFailed;
	}
}

onBeforeMount(async (): Promise<void> => await fetchData());
</script>
