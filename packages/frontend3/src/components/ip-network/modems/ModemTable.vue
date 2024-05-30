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
			{{ $t('components.ipNetwork.modems.title') }}
		</template>
		<template #titleActions>
			<ModemMonitButton ref='monit' />
			<ModemRestartButton @restart='componentState = ComponentState.Loading' @reload='fetchData()' />
			<v-btn
				color='grey'
				:icon='mdiMagnify'
				variant='elevated'
				@click='scan'
			/>
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
					:items='modems'
					:headers='headers'
					hide-pagination
					dense
				>
					<template #item.signal='{item}'>
						<SignalIndicator :signal='item.signal' />
					</template>
					<template #item.state='{item}'>
						<ModemStateBadge :state='item.state' />
					</template>
					<template #item.rssi='{item}'>
						{{ item.rssi !== null ? item.rssi + ' dBm' : '-' }}
					</template>
				</DataTable>
			</v-responsive>
		</v-skeleton-loader>
	</Card>
</template>

<script setup lang='ts'>
import { type Modem } from '@iqrf/iqrf-gateway-webapp-client/types/Network/Modem';
import { mdiMagnify, mdiRefresh } from '@mdi/js';
import { onBeforeMount, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';

import ModemMonitButton
	from '@/components/ip-network/modems/ModemMonitButton.vue';
import ModemRestartButton
	from '@/components/ip-network/modems/ModemRestartButton.vue';
import ModemStateBadge
	from '@/components/ip-network/modems/ModemStateBadge.vue';
import SignalIndicator from '@/components/ip-network/SignalIndicator.vue';
import Card from '@/components/layout/card/Card.vue';
import DataTable from '@/components/layout/data-table/DataTable.vue';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const i18n = useI18n();
const service = useApiClient().getNetworkServices().getModemService();

/// Component state
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
/// Data table headers
const headers = [
	{
		key: 'interface',
		title: i18n.t('components.ipNetwork.modems.columns.interface').toString(),
	},
	{
		key: 'imei',
		title: i18n.t('components.ipNetwork.modems.columns.imei').toString(),
	},
	{
		key: 'manufacturer',
		title: i18n.t('components.ipNetwork.modems.columns.manufacturer').toString(),
	},
	{
		key: 'model',
		title: i18n.t('components.ipNetwork.modems.columns.model').toString(),
	},
	{
		key: 'state',
		title: i18n.t('components.ipNetwork.modems.columns.state').toString(),
	},
	{
		key: 'signal',
		title: i18n.t('components.ipNetwork.modems.columns.signal').toString(),
	},
	{
		key: 'rssi',
		title: i18n.t('components.ipNetwork.modems.columns.rssi').toString(),
	},
];
/// Modems
const modems: Ref<Modem[]> = ref([]);
/// Monit button reference
const monitButton = ref<typeof ModemMonitButton | null>(null);

/**
 * Fetches modems
 */
async function fetchData(): Promise<void> {
	componentState.value = ComponentState.Loading;
	await service.list()
		.then((response: Modem[]): Modem[] => {
			modems.value = response;
			componentState.value = ComponentState.Ready;
			return response;
		})
		.catch(() => {
			componentState.value = ComponentState.FetchFailed;
		});
}

/**
 * Scans for modems
 */
function scan(): void {
	componentState.value = ComponentState.Loading;
	service.scan()
		.then(async () => {
			await new Promise((resolve) => setTimeout(resolve, 5_000));
			await fetchData();
		})
		.catch(() => {
			componentState.value = ComponentState.Error;
		});
}

onBeforeMount(() => {
	fetchData();
	monitButton.value?.fetchData();
});
</script>
