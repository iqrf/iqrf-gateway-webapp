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
				@saved='getConfig()'
			/>
			<CloudConnectionSelector
				@saved='getConfig()'
			/>
			<MqttConnectionImportDialog @import='(c: IqrfGatewayDaemonMqttMessaging) => importFromConfig(c)' />
			<ICardTitleActionBtn
				:action='Action.Reload'
				:tooltip='$t("components.config.daemon.connections.actions.reload")'
				@click='getConfig()'
			/>
		</template>
		<DataTable
			:headers='headers'
			:items='instances'
			:hover='true'
			:dense='true'
			:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
		>
			<template #item.actions='{ item }'>
				<DataTableAction
					:action='Action.Export'
					:tooltip='$t("components.config.daemon.connections.actions.export")'
					@click='exportConfig(item)'
				/>
				<MqttConnectionForm
					:action='Action.Edit'
					:connection-profile='item'
					@saved='getConfig()'
				/>
				<MqttConnectionDeleteDialog
					:connection-profile='item'
					@deleted='getConfig()'
				/>
			</template>
		</DataTable>
	</ICard>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	IqrfGatewayDaemonComponentName,
	type IqrfGatewayDaemonMqttMessaging,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { FileDownloader } from '@iqrf/iqrf-gateway-webapp-client/utils';
import { Action, ICard, ICardTitleActionBtn } from '@iqrf/iqrf-vue-ui';
import { ref, type Ref } from 'vue';
import { onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import CloudConnectionSelector from '@/components/config/daemon/connections/mqtt/cloud/CloudConnectionSelector.vue';
import MqttConnectionDeleteDialog from '@/components/config/daemon/connections/mqtt/MqttConnectionDeleteDialog.vue';
import MqttConnectionForm from '@/components/config/daemon/connections/mqtt/MqttConnectionForm.vue';
import MqttConnectionImportDialog from '@/components/config/daemon/connections/mqtt/MqttConnectionImportDialog.vue';
import DataTable from '@/components/layout/data-table/DataTable.vue';
import DataTableAction from '@/components/layout/data-table/DataTableAction.vue';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const headers = [
	{ key: 'instance', title: i18n.t('components.config.daemon.connections.profile') },
	{ key: 'BrokerAddr', title: i18n.t('components.config.daemon.connections.mqtt.broker') },
	{ key: 'TopicRequest', title: i18n.t('components.config.daemon.connections.mqtt.requestTopic') },
	{ key: 'TopicResponse', title: i18n.t('components.config.daemon.connections.mqtt.responseTopic') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false },
];
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const instances: Ref<IqrfGatewayDaemonMqttMessaging[]> = ref([]);
const addForm: Ref<typeof MqttConnectionForm | null> = ref(null);

async function getConfig(): Promise<void> {
	if (componentState.value === ComponentState.Created) {
		componentState.value = ComponentState.Loading;
	} else {
		componentState.value = ComponentState.Reloading;
	}
	try {
		instances.value = (await service.getComponent(IqrfGatewayDaemonComponentName.IqrfMqttMessaging)).instances;
	} catch {
		toast.error('TODO FETCH ERROR');
	}
	componentState.value = ComponentState.Ready;
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
