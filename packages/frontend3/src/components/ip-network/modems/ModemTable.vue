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
			{{ $t('components.ipNetwork.modems.title') }}
		</template>
		<template #titleActions>
			<ModemMonitButton ref='monit' />
			<ModemRestartButton @restart='componentState = ComponentState.Loading' @reload='fetchData()' />
			<CardTitleActionBtn
				:icon='mdiMagnify'
				:tooltip='$t("components.ipNetwork.modems.actions.scan")'
				@click='scan'
			/>
			<CardTitleActionBtn
				:action='Action.Reload'
				@click='fetchData'
			/>
		</template>
		<DataTable
			:items='modems'
			:headers='headers'
			:loading='componentState === ComponentState.Loading'
			disable-column-filtering
			disable-search
			hide-pagination
			dense
		>
			<template #item.signal='{ item }'>
				<SignalIndicator :signal='item.signal' />
			</template>
			<template #item.state='{ item }'>
				<ModemStateBadge :state='item.state' />
			</template>
			<template #item.rssi='{ item }'>
				{{ item.rssi !== null ? `${item.rssi } dBm` : '-' }}
			</template>
		</DataTable>
	</Card>
</template>

<script setup lang='ts'>
import { type Modem } from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import { mdiMagnify } from '@mdi/js';
import { computed, onBeforeMount, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';

import ModemMonitButton
	from '@/components/ip-network/modems/ModemMonitButton.vue';
import ModemRestartButton
	from '@/components/ip-network/modems/ModemRestartButton.vue';
import ModemStateBadge
	from '@/components/ip-network/modems/ModemStateBadge.vue';
import SignalIndicator from '@/components/ip-network/SignalIndicator.vue';
import Card from '@/components/layout/card/Card.vue';
import CardTitleActionBtn
	from '@/components/layout/card/CardTitleActionBtn.vue';
import DataTable from '@/components/layout/data-table/DataTable.vue';
import { useApiClient } from '@/services/ApiClient';
import { Action } from '@/types/Action';
import { ComponentState } from '@/types/ComponentState';

/// Internationalization instance
const i18n = useI18n();
/// Modem service
const service = useApiClient().getNetworkServices().getModemService();

/// Component state
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
/// Data table headers
const headers = computed(() => [
	{
		key: 'interface',
		title: i18n.t('components.ipNetwork.modems.columns.interface'),
	},
	{
		key: 'imei',
		title: i18n.t('components.ipNetwork.modems.columns.imei'),
	},
	{
		key: 'manufacturer',
		title: i18n.t('components.ipNetwork.modems.columns.manufacturer'),
	},
	{
		key: 'model',
		title: i18n.t('components.ipNetwork.modems.columns.model'),
	},
	{
		key: 'state',
		title: i18n.t('components.ipNetwork.modems.columns.state'),
	},
	{
		key: 'signal',
		title: i18n.t('components.ipNetwork.modems.columns.signal'),
	},
	{
		align: 'end',
		key: 'rssi',
		title: i18n.t('components.ipNetwork.modems.columns.rssi'),
	},
]);
/// Modems
const modems: Ref<Modem[]> = ref([]);
/// Monit button reference
const monitButton = ref<typeof ModemMonitButton | null>(null);

/**
 * Fetches modems
 */
async function fetchData(): Promise<void> {
	componentState.value = ComponentState.Loading;
	try {
		modems.value = await service.list();
		componentState.value = ComponentState.Ready;
	} catch {
		componentState.value = ComponentState.FetchFailed;
	}
}

/**
 * Scans for modems
 */
async function scan(): Promise<void> {
	componentState.value = ComponentState.Loading;
	try {
		await service.scan();
		await new Promise((resolve) => setTimeout(resolve, 5_000));
		await fetchData();
	} catch {
		componentState.value = ComponentState.Error;
	}
}

onBeforeMount(async () => {
	await fetchData();
	await monitButton.value?.fetchData();
});
</script>
