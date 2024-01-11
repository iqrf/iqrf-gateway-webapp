<template>
	<Card>
		<template #title>
			{{ $t('pages.configuration.daemon.connections.mqtt.title') }}
		</template>
		<DataTable
			:headers='headers'
			:items='ifaces'
			:hover='true'
			:dense='true'
			:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
		>
			<template #item.instance='{ item }'>
				{{ item.messaging.instance }}
			</template>
			<template #item.port='{ item }'>
				{{ item.service.WebsocketPort }}
			</template>
			<template #item.acceptAsyncMsg='{ item }'>
				<BooleanCheckMarker :value='item.messaging.acceptAsyncMsg' />
			</template>
			<template #item.acceptOnlyLocalhost='{ item }'>
				<BooleanCheckMarker :value='item.service.acceptOnlyLocalhost' />
			</template>
			<template #item.tlsEnabled='{ item }'>
				<BooleanCheckMarker :value='item.service.tlsEnabled' />
			</template>
			<template #item.actions='{ item }'>
				<v-tooltip location='bottom'>
					<template #activator='{ props }'>
						<v-icon
							v-bind='props'
							color='info'
							size='large'
							:icon='mdiExport'
							class='me-2'
							@click='exportConfig(item)'
						/>
					</template>
					{{ $t('components.configuration.daemon.connections.actions.export') }}
				</v-tooltip>
			</template>
		</DataTable>
	</Card>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	type IqrfGatewayDaemonComponent,
	IqrfGatewayDaemonComponentName,
	type IqrfGatewayDaemonWebsocketInterface,
	type ShapeWebsocketService,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { FileDownloader } from '@iqrf/iqrf-gateway-webapp-client/utils/FileDownloader';
import { mdiExport } from '@mdi/js';
import { type Ref, ref } from 'vue';
import { onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import BooleanCheckMarker from '@/components/BooleanCheckMarker.vue';
import Card from '@/components/Card.vue';
import DataTable from '@/components/DataTable.vue';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const headers = [
	{key: 'instance', title: i18n.t('components.configuration.daemon.connections.profile')},
	{key: 'port', title: i18n.t('common.labels.port')},
	{key: 'acceptAsyncMsg', title: i18n.t('components.configuration.daemon.connections.acceptAsyncMessages')},
	{key: 'acceptOnlyLocalhost', title: i18n.t('components.configuration.daemon.connections.acceptOnlyLocalhost')},
	{key: 'tlsEnabled', title: i18n.t('components.configuration.daemon.connections.tls')},
	{key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false},
];
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const ifaces: Ref<IqrfGatewayDaemonWebsocketInterface[]> = ref([]);

async function getConfigs(): Promise<void> {
	const instances: IqrfGatewayDaemonWebsocketInterface[] = [];
	const services = await service.getComponent(IqrfGatewayDaemonComponentName.ShapeWebsocketService)
		.then((data: IqrfGatewayDaemonComponent<IqrfGatewayDaemonComponentName.ShapeWebsocketService>) => {
			return Promise.resolve(data.instances);
		})
		.catch(() => Promise.resolve(undefined));
	const messagings = await service.getComponent(IqrfGatewayDaemonComponentName.IqrfWsMessaging)
		.then((data: IqrfGatewayDaemonComponent<IqrfGatewayDaemonComponentName.IqrfWsMessaging>) => {
			return Promise.resolve(data.instances);
		})
		.catch(() => Promise.resolve(undefined));
	if (services === undefined || messagings === undefined) {
		toast.error('TODO ERROR FETCH CONFIG');
		return;
	}
	for (const m of messagings) {
		if (m.RequiredInterfaces === undefined || m.RequiredInterfaces.length === 0) {
			continue;
		}
		const requiredService = m.RequiredInterfaces[0].target.instance;
		const idx = services.findIndex((item: ShapeWebsocketService) => item.instance === requiredService);
		if (idx === -1) {
			continue;
		}
		instances.push({
			messaging: m,
			service: services[idx],
		});
	}
	ifaces.value = instances;
}

function exportConfig(config: IqrfGatewayDaemonWebsocketInterface): void {
	FileDownloader.downloadFromData(
		config,
		'application/json',
		`${config.messaging.component.replace('::','__')}__${config.messaging.instance}.json`,
	);
}

onMounted(() => {
	getConfigs();
});

</script>
