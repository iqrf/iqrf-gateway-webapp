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
			<IActionBtn
				:action='Action.Reload'
				container-type='card-title'
				:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
				:disabled='componentState === ComponentState.Action'
				:tooltip='$t("components.gateway.services.actions.refreshAll")'
				@click='getServices()'
			/>
		</template>
		<IDataTable
			:headers='headers'
			:items='services'
			:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
			:no-data-text='noDataText'
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
				<IDataTableAction
					v-if='item.enabled'
					:action='Action.Disable'
					:disabled='componentState === ComponentState.Action'
					:tooltip='$t("components.gateway.services.actions.disable")'
					@click='disableService(item.name, index)'
				/>
				<IDataTableAction
					v-else
					:action='Action.Enable'
					:disabled='componentState === ComponentState.Action'
					:tooltip='$t("components.gateway.services.actions.enable")'
					@click='enableService(item.name, index)'
				/>
				<IDataTableAction
					v-if='item.active'
					:action='Action.Stop'
					:disabled='componentState === ComponentState.Action'
					:tooltip='$t("components.gateway.services.actions.stop")'
					@click='stopService(item.name, index)'
				/>
				<IDataTableAction
					v-else
					:action='Action.Start'
					:disabled='componentState === ComponentState.Action'
					:tooltip='$t("components.gateway.services.actions.start")'
					@click='startService(item.name, index)'
				/>
				<IDataTableAction
					:action='Action.Restart'
					:disabled='componentState === ComponentState.Action'
					:tooltip='$t("components.gateway.services.actions.restart")'
					@click='restartService(item.name, index)'
				/>
				<IDataTableAction
					:action='Action.Reload'
					:disabled='componentState === ComponentState.Action'
					:tooltip='$t("components.gateway.services.actions.refresh")'
					@click='refreshService(item.name, index)'
				/>
				<IDataTableAction
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
		</IDataTable>
	</ICard>
</template>

<script lang='ts' setup>
import { type ServiceService } from '@iqrf/iqrf-gateway-webapp-client/services';
import { type ServiceState, type ServiceStatus } from '@iqrf/iqrf-gateway-webapp-client/types';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IDataTable,
	IDataTableAction,
} from '@iqrf/iqrf-vue-ui';
import { onBeforeMount, ref, type Ref } from 'vue';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import BooleanCheckMarker from '@/components/BooleanCheckMarker.vue';
import { useApiClient } from '@/services/ApiClient';

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
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
/// Gateway services
const services: Ref<ServiceState[]> = ref([]);

const noDataText = computed(() => {
	if (componentState.value === ComponentState.FetchFailed) {
		return i18n.t('components.gateway.services.table.fetchError');
	}
	return i18n.t('components.gateway.services.table.noData');
});

async function getServices(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		services.value = await service.list(true);
		componentState.value = ComponentState.Ready;
	} catch {
		toast.error(
			i18n.t('components.gateway.services.messages.list.failed'),
		);
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
	}
}

async function enableService(name: string, index: number): Promise<void> {
	componentState.value = ComponentState.Action;
	try {
		await service.enable(name);
	} catch {
		toast.error(
			i18n.t('components.gateway.services.messages.enable.failed'),
		);
	}
	await refreshService(name, index);
}

async function disableService(name: string, index: number): Promise<void> {
	componentState.value = ComponentState.Action;
	try {
		await service.disable(name);
	} catch {
		toast.error(
			i18n.t('components.gateway.services.messages.disable.failed'),
		);
	}
	await refreshService(name, index);
}

async function startService(name: string, index: number): Promise<void> {
	componentState.value = ComponentState.Action;
	try {
		await service.start(name);
	} catch {
		toast.error(
			i18n.t('components.gateway.services.messages.start.failed'),
		);
	}
	await refreshService(name, index);
}

async function stopService(name: string, index: number): Promise<void> {
	componentState.value = ComponentState.Action;
	try {
		await service.stop(name);
	} catch {
		toast.error(
			i18n.t('components.gateway.services.messages.stop.failed'),
		);
	}
	await refreshService(name, index);
}

async function restartService(name: string, index: number): Promise<void> {
	componentState.value = ComponentState.Action;
	try {
		await service.restart(name);
	} catch {
		toast.error(
			i18n.t('components.gateway.services.messages.restart.failed'),
		);
	}
	await refreshService(name, index);
}

async function refreshService(name: string, index: number): Promise<void> {
	componentState.value = ComponentState.Action;
	const status: ServiceStatus = await service.getStatus(name);
	services.value[index] = {
		...services.value[index],
		active: status.active ?? null,
		enabled: status.enabled ?? null,
		status: status.status,
	};
	componentState.value = ComponentState.Ready;
}

onBeforeMount(async () => await getServices());

</script>
