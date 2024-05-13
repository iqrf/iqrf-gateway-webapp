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
			{{ $t('pages.configuration.daemon.connections.udp.title') }}
		</template>
		<template #titleActions>
			<UdpConnectionForm
				ref='addForm'
				:action='FormAction.Add'
				@saved='getConfigs'
			/>
			<UdpConnectionImportDialog @import='importFromConfig' />
			<v-btn
				id='reload-activator'
				color='white'
				:icon='mdiReload'
				@click='getConfigs'
			/>
			<v-tooltip
				activator='#reload-activator'
				location='bottom'
			>
				{{ $t('components.configuration.daemon.connections.actions.reload') }}
			</v-tooltip>
		</template>
		<DataTable
			:headers='headers'
			:items='instances'
			:hover='true'
			:dense='true'
			:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
		>
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
				<UdpConnectionForm
					:action='FormAction.Edit'
					:connection-profile='item'
					@saved='getConfigs'
				/>
				<UdpConnectionDeleteDialog
					:connection-profile='item'
					@deleted='getConfigs'
				/>
			</template>
		</DataTable>
	</Card>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { type IqrfGatewayDaemonComponent, IqrfGatewayDaemonComponentName, type IqrfGatewayDaemonUdpMessaging } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { FileDownloader } from '@iqrf/iqrf-gateway-webapp-client/utils/FileDownloader';
import { mdiExport, mdiReload } from '@mdi/js';
import { type Ref, ref } from 'vue';
import { onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import Card from '@/components/Card.vue';
import UdpConnectionDeleteDialog from '@/components/config/daemon/connections/udp/UdpConnectionDeleteDialog.vue';
import UdpConnectionForm from '@/components/config/daemon/connections/udp/UdpConnectionForm.vue';
import UdpConnectionImportDialog from '@/components/config/daemon/connections/udp/UdpConnectionImportDialog.vue';
import DataTable from '@/components/DataTable.vue';
import { FormAction } from '@/enums/controls';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const headers = [
	{ key: 'instance', title: i18n.t('components.configuration.daemon.connections.profile') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false },
];
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const instances: Ref<IqrfGatewayDaemonUdpMessaging[]> = ref([]);
const addForm: Ref<typeof UdpConnectionForm | null> = ref(null);

async function getConfigs(): Promise<void> {
	if (componentState.value === ComponentState.Created) {
		componentState.value = ComponentState.Loading;
	} else {
		componentState.value = ComponentState.Reloading;
	}
	service.getComponent(IqrfGatewayDaemonComponentName.IqrfUdpMessaging)
		.then((response: IqrfGatewayDaemonComponent<IqrfGatewayDaemonComponentName.IqrfUdpMessaging>): void => {
			instances.value = response.instances;
			componentState.value = ComponentState.Ready;
		})
		.catch(() => toast.error('TODO FETCH ERROR'));
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
	getConfigs();
});
</script>
