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
			{{ $t('pages.config.daemon.connections.mqtt.title') }}
		</template>
		<template #titleActions>
			<MqttConnectionForm
				ref='addForm'
				:action='Action.Add'
				:disabled='componentState === ComponentState.Reloading'
				@saved='getConfig()'
			/>
			<CloudConnectionSelector
				:disabled='componentState === ComponentState.Reloading'
				@saved='getConfig()'
			/>
			<MqttConnectionImportDialog
				:disabled='componentState === ComponentState.Reloading'
				@import='(c: IqrfGatewayDaemonMqttMessaging) => importFromConfig(c)'
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
		>
			<template #item.actions='{ item, internalItem, toggleExpand, isExpanded }'>
				<IDataTableAction
					color='primary'
					:icon='mdiInformation'
					:disabled='componentState === ComponentState.Reloading'
					:tooltip='isExpanded(internalItem) ? $t("components.config.daemon.connections.mqtt.actions.hideInfo") : $t("components.config.daemon.connections.mqtt.actions.showInfo")'
					@click='toggleExpand(internalItem)'
				/>
				<IDataTableAction
					:action='Action.Export'
					:tooltip='$t("components.config.daemon.connections.actions.export")'
					:disabled='componentState === ComponentState.Reloading'
					@click='exportConfig(item)'
				/>
				<MqttConnectionForm
					:action='Action.Edit'
					:connection-profile='toRaw(item)'
					:disabled='componentState === ComponentState.Reloading'
					@saved='getConfig()'
				/>
				<MqttConnectionDeleteDialog
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
									<th>{{ $t('components.config.daemon.connections.mqtt.requestTopic') }}</th>
									<td>{{ item.TopicRequest }}</td>
								</tr>
								<tr>
									<th>{{ $t('components.config.daemon.connections.mqtt.responseTopic') }}</th>
									<td>{{ item.TopicResponse }}</td>
								</tr>
							</tbody>
						</v-table>
					</v-sheet>
				</td>
			</template>
		</IDataTable>
	</ICard>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	IqrfGatewayDaemonComponentName,
	type IqrfGatewayDaemonMqttMessaging,
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
import { mdiInformation } from '@mdi/js';
import { computed, ref, type Ref, toRaw } from 'vue';
import { onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import CloudConnectionSelector from '@/components/config/daemon/connections/mqtt/cloud/CloudConnectionSelector.vue';
import MqttConnectionDeleteDialog from '@/components/config/daemon/connections/mqtt/MqttConnectionDeleteDialog.vue';
import MqttConnectionForm from '@/components/config/daemon/connections/mqtt/MqttConnectionForm.vue';
import MqttConnectionImportDialog from '@/components/config/daemon/connections/mqtt/MqttConnectionImportDialog.vue';
import { useApiClient } from '@/services/ApiClient';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const headers = [
	{ key: 'instance', title: i18n.t('components.config.daemon.connections.profile') },
	{ key: 'BrokerAddr', title: i18n.t('components.config.daemon.connections.mqtt.broker') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false },
];
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const instances: Ref<IqrfGatewayDaemonMqttMessaging[]> = ref([]);
const addForm: Ref<typeof MqttConnectionForm | null> = ref(null);

const noDataText = computed(() => {
	if (componentState.value === ComponentState.FetchFailed) {
		return 'components.config.daemon.connections.mqtt.noData.fetchError';
	}
	return 'components.config.daemon.connections.mqtt.noData.empty';
});

async function getConfig(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		instances.value = (await service.getComponent(IqrfGatewayDaemonComponentName.IqrfMqttMessaging)).instances;
		componentState.value = ComponentState.Ready;
	} catch {
		toast.error(
			i18n.t('components.config.daemon.connections.mqtt.messages.list.failed'),
		);
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
	}
}

function importFromConfig(config: IqrfGatewayDaemonMqttMessaging): void {
	if (addForm.value === null) {
		return;
	}
	addForm.value.importFromConfig(config);
}

function exportConfig(config: IqrfGatewayDaemonMqttMessaging): void {
	FileDownloader.downloadFromData(
		config,
		'application/json',
		`${config.component.replace('::','__')}__${config.instance}.json`,
	);
}

onMounted(() => {
	getConfig();
});
</script>
