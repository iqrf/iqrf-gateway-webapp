<template>
	<Card>
		<template #title>
			{{ $t('pages.gateway.services.title') }}
		</template>
		<template #titleActions>
			<v-tooltip
				location='bottom'
			>
				<template #activator='{ props }'>
					<v-btn
						v-bind='props'
						color='white'
						:icon='mdiReload'
						@click='getServices'
					/>
				</template>
				{{ $t('components.gateway.services.actions.refreshAll') }}
			</v-tooltip>
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
				<v-icon
					:icon='statusIcon(item.enabled)'
					:color='iconColor(item.enabled)'
					size='large'
				/>
			</template>
			<template #item.active='{ item }'>
				<v-icon
					:icon='statusIcon(item.active)'
					:color='iconColor(item.active)'
					size='large'
				/>
			</template>
			<template #item.actions='{ item, index, internalItem, toggleExpand }'>
				<span>
					<v-icon
						color='primary'
						size='large'
						class='me-1'
						:icon='enabledActionIcon(item.enabled)'
						@click='item.enabled ? disableService(item.name, index) : enableService(item.name, index)'
					/>
					<v-tooltip
						activator='parent'
						location='bottom'
					>
						{{ enabledTooltip(item.enabled) }}
					</v-tooltip>
				</span>
				<span>
					<v-icon
						color='primary'
						size='large'
						class='me-1'
						:icon='activeActionIcon(item.active)'
						@click='item.active ? stopService(item.name, index) : startService(item.name, index)'
					/>
					<v-tooltip
						activator='parent'
						location='bottom'
					>
						{{ activeTooltip(item.active) }}
					</v-tooltip>
				</span>
				<span>
					<v-icon
						color='primary'
						size='large'
						class='me-1'
						:icon='mdiRestart'
						@click='restartService(item.name, index)'
					/>
					<v-tooltip
						activator='parent'
						location='bottom'
					>
						{{ $t('components.gateway.services.actions.restart') }}
					</v-tooltip>
				</span>
				<span>
					<v-icon
						color='primary'
						size='large'
						class='me-1'
						:icon='mdiReload'
						@click='refreshService(item.name, index)'
					/>
					<v-tooltip
						activator='parent'
						location='bottom'
					>
						{{ $t('components.gateway.services.actions.refresh') }}
					</v-tooltip>
				</span>
				<span>
					<v-icon
						color='primary'
						size='large'
						:icon='mdiInformation'
						@click='toggleExpand(internalItem)'
					/>
					<v-tooltip
						activator='parent'
						location='bottom'
					>
						{{ $t('components.gateway.services.actions.status') }}
					</v-tooltip>
				</span>
			</template>
			<template #expanded-row='{ columns, item }'>
				<tr>
					<td :colspan='columns.length'>
						<pre>{{ item.status }}</pre>
					</td>
				</tr>
			</template>
		</DataTable>
	</Card>
</template>

<script lang='ts' setup>
import { type ServiceService } from '@iqrf/iqrf-gateway-webapp-client/services';
import { type ServiceState, type ServiceStatus } from '@iqrf/iqrf-gateway-webapp-client/types';
import { mdiCheckCircle, mdiCloseCircle, mdiInformation, mdiPlay, mdiPlayCircleOutline, mdiReload, mdiRestart, mdiStop, mdiStopCircleOutline } from '@mdi/js';
import { onMounted, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';


import Card from '@/components/Card.vue';
import DataTable from '@/components/DataTable.vue';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const i18n = useI18n();
const service: ServiceService = useApiClient().getServiceService();
const headers = [
	{key: 'name', title: i18n.t('components.gateway.services.table.service')},
	{key: 'description', title: i18n.t('common.columns.description')},
	{key: 'enabled', title: i18n.t('components.gateway.services.table.enabled')},
	{key: 'active', title: i18n.t('components.gateway.services.table.active')},
	{key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false},
];
/// Component state
const state: Ref<ComponentState> = ref(ComponentState.Created);
/// Gateway services
const services: Ref<ServiceState[]> = ref([]);

function getServices(): void {
	state.value = ComponentState.Loading;
	service.list(true)
		.then((states: ServiceState[]) => {
			services.value = states;
			state.value = ComponentState.Ready;
		});
}

function enableService(name: string, index: number): void {
	state.value = ComponentState.Loading;
	service.enable(name)
		.then(() => refreshService(name, index));
}

function disableService(name: string, index: number): void {
	state.value = ComponentState.Loading;
	service.disable(name)
		.then(() => refreshService(name, index));
}

function startService(name: string, index: number): void {
	state.value = ComponentState.Loading;
	service.start(name)
		.then(() => refreshService(name, index));
}

function stopService(name: string, index: number): void {
	state.value = ComponentState.Loading;
	service.stop(name)
		.then(() => refreshService(name, index));
}

function restartService(name: string, index: number): void {
	state.value = ComponentState.Loading;
	service.restart(name)
		.then(() => refreshService(name, index));
}

function refreshService(name: string, index: number): void {
	state.value = ComponentState.Loading;
	service.getStatus(name)
		.then((status: ServiceStatus) => {
			services.value[index] = {
				...services.value[index],
				active: status.active ?? null,
				enabled: status.enabled ?? null,
				status: status.status,
			};
			state.value = ComponentState.Ready;
		});
}

function iconColor(state: boolean): string {
	if (state) {
		return 'success';
	}
	return 'error';
}

function statusIcon(state: boolean): string {
	if (state) {
		return mdiCheckCircle;
	}
	return mdiCloseCircle;
}

function enabledActionIcon(state: boolean): string {
	if (state) {
		return mdiStopCircleOutline;
	}
	return mdiPlayCircleOutline;
}

function enabledTooltip(state: boolean): string {
	if (state) {
		return i18n.t('components.gateway.services.actions.disable');
	}
	return i18n.t('components.gateway.services.actions.enable');
}

function activeActionIcon(state: boolean): string {
	if (state) {
		return mdiStop;
	}
	return mdiPlay;
}

function activeTooltip(state: boolean): string {
	if (state) {
		return i18n.t('components.gateway.services.actions.stop');
	}
	return i18n.t('components.gateway.services.actions.start');
}

onMounted(() => {
	getServices();
});

</script>
