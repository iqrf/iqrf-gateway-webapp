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
			{{ $t('pages.gateway.services.title') }}
		</template>
		<template #titleActions>
			<ICardTitleActionBtn
				:action='Action.Reload'
				:tooltip='$t("components.gateway.services.actions.refreshAll")'
				@click='getServices'
			/>
		</template>
		<DataTable
			:headers='headers'
			:items='services'
			:loading='state === ComponentState.Loading'
			:hover='true'
			:dense='true'
			item-value='name'
		>
			<template #item.name='{ item }'>
				{{ $t(`components.gateway.services.service.${item.name}.name`) }}
			</template>
			<template #item.description='{ item }'>
				{{ $t(`components.gateway.services.service.${item.name}.description`) }}
			</template>
			<template #item.enabled='{ item }'>
				<BooleanCheckMarker :value='item.enabled' />
			</template>
			<template #item.active='{ item }'>
				<BooleanCheckMarker :value='item.active' />
			</template>
			<template #item.actions='{ item, index, internalItem, toggleExpand }'>
				<DataTableAction
					v-if='item.enabled'
					:action='Action.Disable'
					:tooltip='$t("components.gateway.services.actions.disable")'
					@click='disableService(item.name, index)'
				/>
				<DataTableAction
					v-else
					:action='Action.Enable'
					:tooltip='$t("components.gateway.services.actions.enable")'
					@click='enableService(item.name, index)'
				/>
				<DataTableAction
					v-if='item.active'
					:action='Action.Stop'
					:tooltip='$t("components.gateway.services.actions.stop")'
					@click='stopService(item.name, index)'
				/>
				<DataTableAction
					v-else
					:action='Action.Start'
					:tooltip='$t("components.gateway.services.actions.start")'
					@click='startService(item.name, index)'
				/>
				<DataTableAction
					:action='Action.Restart'
					:tooltip='$t("components.gateway.services.actions.restart")'
					@click='restartService(item.name, index)'
				/>
				<DataTableAction
					:action='Action.Reload'
					:tooltip='$t("components.gateway.services.actions.refresh")'
					@click='refreshService(item.name, index)'
				/>
				<DataTableAction
					:action='Action.ShowDetails'
					:tooltip='$t("components.gateway.services.actions.status")'
					@click='toggleExpand(internalItem)'
				/>
			</template>
			<template #expanded-row='{ columns, item }'>
				<tr>
					<td :colspan='columns.length'>
						<pre>{{ item.status }}</pre>
					</td>
				</tr>
			</template>
		</DataTable>
	</ICard>
</template>

<script lang='ts' setup>
import { type ServiceService } from '@iqrf/iqrf-gateway-webapp-client/services';
import { type ServiceState, type ServiceStatus } from '@iqrf/iqrf-gateway-webapp-client/types';
import { Action, ICard, ICardTitleActionBtn } from '@iqrf/iqrf-vue-ui';
import { onMounted, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';

import BooleanCheckMarker from '@/components/BooleanCheckMarker.vue';
import DataTable from '@/components/layout/data-table/DataTable.vue';
import DataTableAction from '@/components/layout/data-table/DataTableAction.vue';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const i18n = useI18n();
const service: ServiceService = useApiClient().getServiceService();
const headers = [
	{ key: 'name', title: i18n.t('components.gateway.services.table.service') },
	{ key: 'description', title: i18n.t('common.columns.description') },
	{ key: 'enabled', title: i18n.t('common.states.enabled') },
	{ key: 'active', title: i18n.t('common.states.active') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false },
];
/// Component state
const state: Ref<ComponentState> = ref(ComponentState.Created);
/// Gateway services
const services: Ref<ServiceState[]> = ref([]);

async function getServices(): Promise<void> {
	state.value = ComponentState.Loading;
	services.value = await service.list(true);
	state.value = ComponentState.Ready;
}

async function enableService(name: string, index: number): Promise<void> {
	state.value = ComponentState.Loading;
	await service.enable(name);
	await refreshService(name, index);
}

async function disableService(name: string, index: number): Promise<void> {
	state.value = ComponentState.Loading;
	await service.disable(name);
	await refreshService(name, index);
}

async function startService(name: string, index: number): Promise<void> {
	state.value = ComponentState.Loading;
	await service.start(name);
	await refreshService(name, index);
}

async function stopService(name: string, index: number): Promise<void> {
	state.value = ComponentState.Loading;
	await service.stop(name);
	await refreshService(name, index);
}

async function restartService(name: string, index: number): Promise<void> {
	state.value = ComponentState.Loading;
	await service.restart(name);
	await refreshService(name, index);
}

async function refreshService(name: string, index: number): Promise<void> {
	state.value = ComponentState.Loading;
	const status: ServiceStatus = await service.getStatus(name);
	services.value[index] = {
		...services.value[index],
		active: status.active ?? null,
		enabled: status.enabled ?? null,
		status: status.status,
	};
	state.value = ComponentState.Ready;
}

onMounted(async () => await getServices());

</script>
