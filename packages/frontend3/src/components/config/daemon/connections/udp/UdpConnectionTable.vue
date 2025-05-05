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
	<Card>
		<template #title>
			{{ $t('pages.config.daemon.connections.udp.title') }}
		</template>
		<template #titleActions>
			<UdpConnectionForm
				ref='addForm'
				:action='Action.Add'
				@saved='getConfig()'
			/>
			<UdpConnectionImportDialog @import='(c: IqrfGatewayDaemonUdpMessaging) => importFromConfig(c)' />
			<CardTitleActionBtn
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
				<UdpConnectionForm
					:action='Action.Edit'
					:connection-profile='item'
					@saved='getConfig()'
				/>
				<UdpConnectionDeleteDialog
					:connection-profile='item'
					@deleted='getConfig()'
				/>
			</template>
		</DataTable>
	</Card>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	IqrfGatewayDaemonComponentName,
	type IqrfGatewayDaemonUdpMessaging,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { FileDownloader } from '@iqrf/iqrf-gateway-webapp-client/utils';
import { ref, type Ref } from 'vue';
import { onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import UdpConnectionDeleteDialog from '@/components/config/daemon/connections/udp/UdpConnectionDeleteDialog.vue';
import UdpConnectionForm from '@/components/config/daemon/connections/udp/UdpConnectionForm.vue';
import UdpConnectionImportDialog from '@/components/config/daemon/connections/udp/UdpConnectionImportDialog.vue';
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
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false },
];
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const instances: Ref<IqrfGatewayDaemonUdpMessaging[]> = ref([]);
const addForm: Ref<typeof UdpConnectionForm | null> = ref(null);

async function getConfig(): Promise<void> {
	if (componentState.value === ComponentState.Created) {
		componentState.value = ComponentState.Loading;
	} else {
		componentState.value = ComponentState.Reloading;
	}
	try {
		instances.value = (await service.getComponent(IqrfGatewayDaemonComponentName.IqrfUdpMessaging)).instances;
	} catch {
		toast.error('TODO FETCH ERROR');
	}
	componentState.value = ComponentState.Ready;
}

function importFromConfig(config: IqrfGatewayDaemonUdpMessaging): void {
	if (addForm.value === null) {
		return;
	}
	addForm.value.importFromConfig(config);
}

function exportConfig(config: IqrfGatewayDaemonUdpMessaging): void {
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
