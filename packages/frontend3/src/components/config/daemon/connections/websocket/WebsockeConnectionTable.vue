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
	<Card>
		<template #title>
			{{ $t('pages.config.daemon.connections.ws.title') }}
		</template>
		<template #titleActions>
			<CardTitleActionBtn
				:action='Action.Reload'
				:tooltip='$t("components.config.daemon.connections.actions.reload")'
				@click='getConfigs'
			/>
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
				<DataTableAction
					:action='Action.Export'
					:tooltip='$t("components.config.daemon.connections.actions.export")'
					@click='exportConfig(item)'
				/>
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
import { FileDownloader } from '@iqrf/iqrf-gateway-webapp-client/utils';
import { type Ref, ref } from 'vue';
import { onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import BooleanCheckMarker from '@/components/BooleanCheckMarker.vue';
import Card from '@/components/layout/card/Card.vue';
import CardTitleActionBtn from '@/components/layout/card/CardTitleActionBtn.vue';
import DataTable from '@/components/layout/data-table/DataTable.vue';
import DataTableAction from '@/components/layout/data-table/DataTableAction.vue';
import { useApiClient } from '@/services/ApiClient';
import { Action } from '@/types/Action';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const headers = [
	{ key: 'instance', title: i18n.t('components.config.daemon.connections.profile') },
	{ key: 'port', title: i18n.t('common.labels.port') },
	{ key: 'acceptAsyncMsg', title: i18n.t('components.config.daemon.connections.acceptAsyncMessages') },
	{ key: 'acceptOnlyLocalhost', title: i18n.t('components.config.daemon.connections.acceptOnlyLocalhost') },
	{ key: 'tlsEnabled', title: i18n.t('components.config.daemon.connections.tls') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false },
];
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const ifaces: Ref<IqrfGatewayDaemonWebsocketInterface[]> = ref([]);

async function getConfigs(): Promise<void> {
	const instances: IqrfGatewayDaemonWebsocketInterface[] = [];
	const services = await service.getComponent(IqrfGatewayDaemonComponentName.ShapeWebsocketService)
		.then((data: IqrfGatewayDaemonComponent<IqrfGatewayDaemonComponentName.ShapeWebsocketService>) => {
			return data.instances;
		})
		.catch(() => undefined);
	const messagings = await service.getComponent(IqrfGatewayDaemonComponentName.IqrfWsMessaging)
		.then((data: IqrfGatewayDaemonComponent<IqrfGatewayDaemonComponentName.IqrfWsMessaging>) => {
			return data.instances;
		})
		.catch(() => undefined);
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
