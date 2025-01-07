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
	<Card>
		<template #title>
			{{ $t('components.gateway.power.stats.title') }}
		</template>
		<v-alert v-if='state === ComponentState.NotFound'>
			{{ $t('components.gateway.power.stats.messages.tuptimeMissing') }}
		</v-alert>
		<DataTable
			:headers='headers'
			:items='stats'
			:loading='state === ComponentState.Loading'
			:hover='true'
			:dense='true'
		>
			<template #item.start='{ item }'>
				{{ $d(item.start.toJSDate(), 'long') }}
			</template>
			<template #item.shutdown='{ item }'>
				<span v-if='item.shutdown !== null'>
					{{ $d(item.shutdown.toJSDate(), 'long') }}
				</span>
			</template>
			<template #item.running='{ item }'>
				{{ humanizeDuration(item.running.valueOf(), { round: true, language: localeStore.getLocale, units: ["d", "h", "m"] }) }}
			</template>
			<template #item.downtime='{ item }'>
				<span v-if='item.shutdown !== null'>
					{{ humanizeDuration(item.downtime.valueOf(), { round: true, language: localeStore.getLocale, units: ["d", "h", "m"] }) }}
				</span>
			</template>
			<template #item.graceful='{ item }'>
				<BooleanCheckMarker
					v-if='item.graceful'
					:value='true'
				/>
				<BooleanCheckMarker
					v-else-if='item.shutdown !== null'
					:value='false'
				/>
			</template>
		</DataTable>
	</Card>
</template>

<script lang='ts' setup>
import { type PowerService } from '@iqrf/iqrf-gateway-webapp-client/services/Gateway';
import { type GatewayUptime } from '@iqrf/iqrf-gateway-webapp-client/types/Gateway';
import { type AxiosError } from 'axios';
import humanizeDuration from 'humanize-duration';
import { onMounted, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import BooleanCheckMarker from '@/components/BooleanCheckMarker.vue';
import Card from '@/components/layout/card/Card.vue';
import DataTable from '@/components/layout/data-table/DataTable.vue';
import { useApiClient } from '@/services/ApiClient';
import { useLocaleStore } from '@/store/locale';
import { ComponentState } from '@/types/ComponentState';

const i18n = useI18n();
const localeStore = useLocaleStore();
const service: PowerService = useApiClient().getGatewayServices().getPowerService();

const headers = [
	{ key: 'id', title: i18n.t('common.columns.id') },
	{ key: 'start', title: i18n.t('components.gateway.power.stats.columns.start') },
	{ key: 'shutdown', title: i18n.t('components.gateway.power.stats.columns.shutdown') },
	{ key: 'graceful', title: i18n.t('components.gateway.power.stats.columns.graceful') },
	{ key: 'running', title: i18n.t('components.gateway.power.stats.columns.uptime') },
	{ key: 'downtime', title: i18n.t('components.gateway.power.stats.columns.downtime') },
];
/// Component state
const state: Ref<ComponentState> = ref(ComponentState.Created);
/// Gateway uptime statistics
const stats: Ref<GatewayUptime[]> = ref([]);

/**
 * Fetches gateway uptime statistics
 */
async function fetchData() {
	state.value = ComponentState.Loading;
	await service.getStats()
		.then((response: GatewayUptime[]) => {
			stats.value = response;
			state.value = ComponentState.Ready;
		})
		.catch((error: AxiosError) => {
			stats.value = [];
			if (error.response?.status === 404) {
				state.value = ComponentState.NotFound;
				toast.warn(i18n.t('components.gateway.power.stats.messages.tuptimeMissing').toString());
			} else {
				state.value = ComponentState.FetchFailed;
				toast.error(i18n.t('components.gateway.power.stats.messages.fetchFailed').toString());
			}
		});
}

onMounted(async (): Promise<void> => {
	await fetchData();
});

</script>
