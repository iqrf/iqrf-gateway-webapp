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
			{{ $t(`components.ipNetwork.interfaces.titles.${type}`) }}
		</template>
		<template #titleActions>
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
					:items='interfaces'
					:headers='headers'
					hide-pagination
					dense
				>
					<template #item.state='{ item }'>
						<InterfaceStateBadge :state='item.state' />
					</template>
				</IDataTable>
			</v-responsive>
		</v-skeleton-loader>
	</ICard>
</template>

<script setup lang='ts'>
import {
	type NetworkInterface,
	type NetworkInterfaceType,
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

import InterfaceStateBadge
	from '@/components/ip-network/interfaces/InterfaceStateBadge.vue';
import { useApiClient } from '@/services/ApiClient';

/// Component props
const componentProps = withDefaults(
	defineProps<{
		type?: NetworkInterfaceType | null;
	}>(),
	{
		type: null,
	},
);
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
