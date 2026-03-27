<!--
Copyright 2017-2026 IQRF Tech s.r.o.
Copyright 2019-2026 MICRORISC s.r.o.

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
			<IActionBtn
				:action='Action.Add'
				container-type='card-title'
				:tooltip='$t("components.config.daemon.connections.actions.add")'
				:disabled='componentState === ComponentState.Reloading'
				@click='openForm(null, Action.Add)'
			/>
			<WebSocketConnectionImportDialog
				:disabled='componentState === ComponentState.Reloading'
				@import='(c: IqrfGatewayDaemonWsMessaging) => openForm(c, Action.Add)'
			/>
			<IActionBtn
				:action='Action.Reload'
				container-type='card-title'
				:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
				:disabled='componentState === ComponentState.Action'
				:tooltip='$t("components.config.daemon.connections.actions.reload")'
				@click='getConfig()'
			/>
		</template>
		<IDataTable
			:headers='headers'
			:items='instances'
			:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
			:no-data-text='noDataText'
			:hover='true'
			:dense='true'
			item-value='instance'
		>
			<template #item.transportMode='{ item }'>
				<WebSocketConnectionTableTransportMode
					:mode='item.transportMode'
				/>
			</template>
			<template #item.acceptOnlyLocalhost='{ item }'>
				<IBooleanIcon
					:value='item.acceptOnlyLocalhost'
				/>
			</template>
			<template #item.acceptAsyncMsg='{ item }'>
				<IBooleanIcon
					:value='item.acceptAsyncMsg'
				/>
			</template>
			<template #item.actions='{ item, internalItem, toggleExpand, isExpanded }'>
				<IDataTableAction
					color='primary'
					:icon='mdiInformation'
					:disabled='componentState === ComponentState.Reloading'
					:tooltip='isExpanded(internalItem) ?
						$t("components.config.daemon.connections.actions.hideInfo") :
						$t("components.config.daemon.connections.actions.showInfo")'
					@click='toggleExpand(internalItem)'
				/>
				<IDataTableAction
					:action='Action.Export'
					:tooltip='$t("components.config.daemon.connections.actions.export")'
					:disabled='componentState === ComponentState.Reloading'
					@click='exportConfig(item)'
				/>
				<IDataTableAction
					:action='Action.Edit'
					:tooltip='$t("components.config.daemon.connections.actions.edit")'
					:disabled='componentState === ComponentState.Reloading'
					@click='openForm(toRaw(item), Action.Edit)'
				/>
				<WebSocketConnectionDeleteDialog
					:connection-profile='toRaw(item)'
					:disabled='componentState === ComponentState.Reloading'
					@deleted='getConfig()'
				/>
			</template>
			<template #expanded-row='{ columns, item }'>
				<td :colspan='columns.length'>
					<v-sheet border>
						<v-table density='compact'>
							<tbody>
								<tr>
									<th>{{ $t('components.config.daemon.connections.ws.plainPort') }}</th>
									<td>{{ item.port }}</td>
								</tr>
								<tr v-if='item.transportMode !== IqrfGatewayDaemonWsTransportModes.Plain'>
									<th>{{ $t('components.config.daemon.connections.ws.tlsPort') }}</th>
									<td>{{ item.tlsPort }}</td>
								</tr>
								<tr v-if='item.transportMode !== IqrfGatewayDaemonWsTransportModes.Plain'>
									<th>{{ $t('components.config.daemon.connections.ws.tlsMode') }}</th>
									<td>
										{{ getTlsModeText(item.tlsMode) }}
										<v-tooltip location='bottom'>
											<template #activator='{ props }'>
												<v-icon
													v-bind='props'
													:icon='mdiHelpCircleOutline'
												/>
											</template>
											{{ getTlsModeNote(item.tlsMode) }}
										</v-tooltip>
									</td>
								</tr>
								<tr>
									<th>{{ $t('components.config.daemon.connections.ws.maxClients') }}</th>
									<td>{{ item.maxClients }}</td>
								</tr>
							</tbody>
						</v-table>
					</v-sheet>
				</td>
			</template>
		</IDataTable>
	</ICard>
	<WebSocketConnectionForm
		ref='form'
		@saved='getConfig()'
	/>
</template>

<script setup lang='ts'>
import { IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	IqrfGatewayDaemonComponentName,
	type IqrfGatewayDaemonWsMessaging,
	IqrfGatewayDaemonWsTlsModes,
	IqrfGatewayDaemonWsTransportModes,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { FileDownloader } from '@iqrf/iqrf-gateway-webapp-client/utils';
import {
	Action,
	ComponentState,
	IActionBtn,
	IBooleanIcon,
	ICard,
	IDataTable,
	IDataTableAction,
} from '@iqrf/iqrf-vue-ui';
import { mdiHelpCircleOutline, mdiInformation } from '@mdi/js';
import {
	computed,
	onMounted,
	ref,
	toRaw,
	useTemplateRef,
} from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import WebSocketConnectionDeleteDialog from '@/components/config/daemon/connections/websocket/WebSocketConnectionDeleteDialog.vue';
import WebSocketConnectionForm from '@/components/config/daemon/connections/websocket/WebSocketConnectionForm.vue';
import WebSocketConnectionImportDialog from '@/components/config/daemon/connections/websocket/WebSocketConnectionImportDialog.vue';
import WebSocketConnectionTableTransportMode from '@/components/config/daemon/connections/websocket/WebSocketConnectionTableTransportMode.vue';
import { useApiClient } from '@/services/ApiClient';

const componentState = ref<ComponentState>(ComponentState.Created);
const i18n = useI18n();
const service: IqrfGatewayDaemonService = useApiClient()
	.getConfigServices()
	.getIqrfGatewayDaemonService();
const headers = computed(() => [
	{ key: 'instance', title: i18n.t('components.config.daemon.connections.profile') },
	{ key: 'transportMode', title: i18n.t('components.config.daemon.connections.ws.transportMode') },
	{ key: 'acceptOnlyLocalhost', title: i18n.t('components.config.daemon.connections.ws.localhostOnly') },
	{ key: 'acceptAsyncMsg', title: i18n.t('components.config.daemon.connections.ws.asyncMessages') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false },
]);
const instances = ref<IqrfGatewayDaemonWsMessaging[]>([]);
const form = useTemplateRef<InstanceType<typeof WebSocketConnectionForm>>('form');

const noDataText = computed(() => {
	if (componentState.value === ComponentState.FetchFailed) {
		return 'components.config.daemon.connections.ws.noData.fetchError';
	}
	return 'components.config.daemon.connections.ws.noData.empty';
});

function getTlsModeText(mode: IqrfGatewayDaemonWsTlsModes): string {
	if (mode === IqrfGatewayDaemonWsTlsModes.Modern) {
		return i18n.t('components.config.daemon.connections.ws.tlsModes.modern');
	}
	if (mode === IqrfGatewayDaemonWsTlsModes.Intermediate) {
		return i18n.t('components.config.daemon.connections.ws.tlsModes.intermediate');
	}
	return i18n.t('components.config.daemon.connections.ws.tlsModes.old');
}

function getTlsModeNote(mode: IqrfGatewayDaemonWsTlsModes): string {
	if (mode === IqrfGatewayDaemonWsTlsModes.Modern) {
		return i18n.t('components.config.daemon.connections.ws.notes.tlsModes.modern');
	}
	if (mode === IqrfGatewayDaemonWsTlsModes.Intermediate) {
		return i18n.t('components.config.daemon.connections.ws.notes.tlsModes.intermediate');
	}
	return i18n.t('components.config.daemon.connections.ws.notes.tlsModes.old');
}

async function getConfig(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		const data = await service.getComponent(IqrfGatewayDaemonComponentName.IqrfWsMessaging);
		instances.value = data.instances;
		componentState.value = ComponentState.Ready;
	} catch {
		toast.error(
			i18n.t('components.config.daemon.connections.ws.messages.list.failed'),
		);
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
	}
}

function openForm(config: IqrfGatewayDaemonWsMessaging | null = null, action: Action): void {
	if (form.value === null) {
		return;
	}
	form.value.open(config, action);
}

function exportConfig(config: IqrfGatewayDaemonWsMessaging): void {
	FileDownloader.downloadFromData(
		config,
		'application/json',
		`${config.component.replace('::', '__')}__${config.instance}.json`,
	);
}

onMounted(() => {
	getConfig();
});
</script>
