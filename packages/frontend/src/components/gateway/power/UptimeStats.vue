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
	<ICard>
		<template #title>
			{{ $t('components.gateway.power.stats.title') }}
		</template>
		<template #titleActions>
			<IActionBtn
				:action='Action.Reload'
				type='card-title'
				:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
				:tooltip='$t("components.gateway.power.stats.actions.refresh")'
				@click='getStats()'
			/>
		</template>
		<IDataTable
			:headers='headers'
			:items='stats'
			:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
			:no-data-text='noDataText'
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
		</IDataTable>
	</ICard>
</template>

<script lang='ts' setup>
import { type PowerService } from '@iqrf/iqrf-gateway-webapp-client/services/Gateway';
import { type GatewayUptime } from '@iqrf/iqrf-gateway-webapp-client/types/Gateway';
import { Action, IActionBtn, ICard, IDataTable } from '@iqrf/iqrf-vue-ui';
import humanizeDuration from 'humanize-duration';
import { computed, onBeforeMount, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import BooleanCheckMarker from '@/components/BooleanCheckMarker.vue';
import { useApiClient } from '@/services/ApiClient';
import { useLocaleStore } from '@/store/locale';
import { ComponentState } from '@/types/ComponentState';

const i18n = useI18n();
const localeStore = useLocaleStore();
const service: PowerService = useApiClient().getGatewayServices().getPowerService();

const headers = [
	{ key: 'id', title: i18n.t('common.columns.id') },
	{ key: 'start', title: i18n.t('components.gateway.power.stats.table.start') },
	{ key: 'shutdown', title: i18n.t('components.gateway.power.stats.table.shutdown') },
	{ key: 'graceful', title: i18n.t('components.gateway.power.stats.table.graceful') },
	{ key: 'running', title: i18n.t('components.gateway.power.stats.table.uptime') },
	{ key: 'downtime', title: i18n.t('components.gateway.power.stats.table.downtime') },
];
/// Component state
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
/// Gateway uptime statistics
const stats: Ref<GatewayUptime[]> = ref([]);

const noDataText = computed(() => {
	if (componentState.value === ComponentState.FetchFailed) {
		return i18n.t('components.gateway.power.stats.table.fetchError');
	}
	return i18n.t('components.gateway.power.stats.table.noData');
});

/**
 * Fetches gateway uptime statistics
 */
async function getStats() {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		stats.value = await service.getStats();
		componentState.value = ComponentState.Ready;
	} catch {
		toast.error(
			i18n.t('components.gateway.power.stats.messages.list.failed').toString(),
		);
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
	}
}

onBeforeMount(async (): Promise<void> => {
	await getStats();
});

</script>
