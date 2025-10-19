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
			{{ $t('pages.config.daemon.connections.ws.title') }}
		</template>
		<template #titleActions>
			<WsConnectionForm
				ref='form'
				:action='Action.Add'
				:disabled='componentState === ComponentState.Reloading'
				@saved='getConfigs()'
			/>
			<WsConnectionImportDialog
				:disabled='componentState === ComponentState.Reloading'
				@import='(m: IqrfGatewayDaemonWsMessaging, s: ShapeWebsocketService) => importFromConfig(m, s)'
			/>
			<IActionBtn
				:action='Action.Reload'
				container-type='card-title'
				:tooltip='$t("components.config.daemon.connections.actions.reload")'
				@click='getConfigs()'
			/>
		</template>
		<IDataTable
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
				<IDataTableAction
					:action='Action.Export'
					:tooltip='$t("components.config.daemon.connections.actions.export")'
					:disabled='[ComponentState.Action, ComponentState.Reloading].includes(componentState)'
					@click='exportConfig(item)'
				/>
				<WsConnectionForm
					:action='Action.Edit'
					:messaging-instance='toRaw(item.messaging)'
					:service-instance='toRaw(item.service)'
					:disabled='componentState === ComponentState.Reloading'
					@saved='getConfigs()'
				/>
				<WsConnectionDeleteDialog
					:connection-profile='toRaw(item)'
					:disabled='componentState === ComponentState.Reloading'
					@deleted='getConfigs()'
				/>
			</template>
		</IDataTable>
	</ICard>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	IqrfGatewayDaemonComponentName,
	type IqrfGatewayDaemonWebsocketInterface,
	type IqrfGatewayDaemonWsMessaging,
	type ShapeWebsocketService,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { FileDownloader } from '@iqrf/iqrf-gateway-webapp-client/utils';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IDataTable,
	IDataTableAction,
} from '@iqrf/iqrf-vue-ui';
import { ref, type Ref, toRaw } from 'vue';
import { onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import BooleanCheckMarker from '@/components/BooleanCheckMarker.vue';
import WsConnectionDeleteDialog from '@/components/config/daemon/connections/websocket/WsConnectionDeleteDialog.vue';
import WsConnectionForm from '@/components/config/daemon/connections/websocket/WsConnectionForm.vue';
import WsConnectionImportDialog from '@/components/config/daemon/connections/websocket/WsConnectionImportDialog.vue';
import { useApiClient } from '@/services/ApiClient';

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
const form: Ref<typeof WsConnectionForm | null> = ref(null);

async function getConfigs(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		const messagings = (await service.getComponent(IqrfGatewayDaemonComponentName.IqrfWsMessaging)).instances;
		const services = (await service.getComponent(IqrfGatewayDaemonComponentName.ShapeWebsocketService)).instances;
		buildInterfaces(messagings, services);
		componentState.value = ComponentState.Ready;
	} catch {
		toast.error(
			i18n.t('components.config.daemon.connections.websocket.messages.fetch.failed'),
		);
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
	}
}

function buildInterfaces(messagings: IqrfGatewayDaemonWsMessaging[], services: ShapeWebsocketService[]): void {
	const serviceMap = new Map<string, number>();
	for (const [idx, service] of services.entries()) {
		serviceMap.set(service.instance, idx);
	}
	const interfaces: IqrfGatewayDaemonWebsocketInterface[] = [];
	for (const messaging of messagings) {
		if (messaging.RequiredInterfaces.length === 0) {
			continue;
		}
		const serviceIdx = serviceMap.get(messaging.RequiredInterfaces[0].target.instance);
		if (serviceIdx === undefined) {
			continue;
		}
		interfaces.push({
			messaging: messaging,
			service: services[serviceIdx],
		});
	}
	ifaces.value = interfaces;
}

function importFromConfig(messaging: IqrfGatewayDaemonWsMessaging, service: ShapeWebsocketService): void {
	if (form.value === null) {
		return;
	}
	form.value.importFromConfig(messaging, service);
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
