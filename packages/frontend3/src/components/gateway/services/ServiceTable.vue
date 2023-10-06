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
			:expanded='expanded'
			:loading='loading'
			:hover='true'
			:dense='true'
		>
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
				<td :colspan='columns.length'>
					<pre>{{ item.status }}</pre>
				</td>
			</template>
		</DataTable>
	</Card>
</template>

<script lang='ts' setup>
import { onMounted, ref, Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useApiClient } from '@/services/ApiClient';

import Card from '@/components/Card.vue';
import DataTable from '@/components/DataTable.vue';

import { ServiceService } from '@iqrf/iqrf-gateway-webapp-client/services';
import { ServiceStatus } from '@iqrf/iqrf-gateway-webapp-client/types';
import { mdiCheckCircle, mdiCloseCircle, mdiInformation, mdiPlay, mdiPlayCircleOutline, mdiReload, mdiStop, mdiStopCircleOutline } from '@mdi/js';

const i18n = useI18n();
const service: ServiceService = useApiClient().getServiceService();
const loading: Ref<boolean> = ref(false);
const headers = [
	{key: 'name', title: i18n.t('components.gateway.services.table.service')},
	{key: 'description', title: i18n.t('common.columns.description')},
	{key: 'enabled', title: i18n.t('components.gateway.services.table.enabled')},
	{key: 'active', title: i18n.t('components.gateway.services.table.active')},
	{key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false},
];
const services: Ref<ServiceStatus[]> = ref([]);
const expanded: Ref<ServiceStatus[]> = ref([]);

onMounted(() => {
	getServices();
});

function getServices(): void {
	service.getStatus('iqrf-gateway-daemon')
		.then((item: ServiceStatus) => services.value.push(item));
}

function enableService(name: string, index: number): void {
	service.enable(name)
		.then(() => refreshService(name, index));
}

function disableService(name: string, index: number): void {
	service.disable(name)
		.then(() => refreshService(name, index));
}

function startService(name: string, index: number): void {
	service.start(name)
		.then(() => refreshService(name, index));
}

function stopService(name: string, index: number): void {
	service.stop(name)
		.then(() => refreshService(name, index));
}

function refreshService(name: string, index: number): void {
	service.getStatus(name)
		.then((item: ServiceStatus) => services.value[index] = item);
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

</script>
