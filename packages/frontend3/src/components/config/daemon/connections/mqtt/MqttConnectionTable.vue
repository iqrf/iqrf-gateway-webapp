<template>
	<Card>
		<template #title>
			{{ $t('pages.configuration.daemon.connections.mqtt.title') }}
		</template>
		<template #titleActions>
			<MqttConnectionForm
				:action='FormAction.Add'
				@saved='getConfigs'
			/>
			<CloudConnectionSelector
				@saved='getConfigs'
			/>
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
				{{ $t('components.configuration.daemon.connections.mqtt.actions.reload') }}
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
				<MqttConnectionForm
					:action='FormAction.Edit'
					:connection-profile='item'
					@saved='getConfigs'
				/>
				<MqttConnectionDeleteDialog
					:connection-profile='item'
					@deleted='getConfigs'
				/>
			</template>
		</DataTable>
	</Card>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { type IqrfGatewayDaemonComponent, IqrfGatewayDaemonComponentName, type IqrfGatewayDaemonMqttMessaging } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { mdiReload } from '@mdi/js';
import { type Ref, ref } from 'vue';
import { onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import Card from '@/components/Card.vue';
import CloudConnectionSelector from '@/components/config/daemon/connections/mqtt/cloud/CloudConnectionSelector.vue';
import MqttConnectionDeleteDialog from '@/components/config/daemon/connections/mqtt/MqttConnectionDeleteDialog.vue';
import MqttConnectionForm from '@/components/config/daemon/connections/mqtt/MqttConnectionForm.vue';
import DataTable from '@/components/DataTable.vue';
import { FormAction } from '@/enums/controls';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const headers = [
	{key: 'instance', title: i18n.t('components.configuration.daemon.connections.profile')},
	{key: 'BrokerAddr', title: i18n.t('components.configuration.daemon.connections.mqtt.broker')},
	{key: 'TopicRequest', title: i18n.t('components.configuration.daemon.connections.mqtt.requestTopic')},
	{key: 'TopicResponse', title: i18n.t('components.configuration.daemon.connections.mqtt.responseTopic')},
	{key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false},
];
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const instances: Ref<IqrfGatewayDaemonMqttMessaging[]> = ref([]);

async function getConfigs(): Promise<void> {
	if (componentState.value === ComponentState.Created) {
		componentState.value = ComponentState.Loading;
	} else {
		componentState.value = ComponentState.Reloading;
	}
	service.getComponent(IqrfGatewayDaemonComponentName.IqrfMqttMessaging)
		.then((response: IqrfGatewayDaemonComponent<IqrfGatewayDaemonComponentName.IqrfMqttMessaging>): void => {
			instances.value = response.instances;
			componentState.value = ComponentState.Ready;
		})
		.catch(() => toast.error('TODO FETCH ERROR'));
}

onMounted(() => {
	getConfigs();
});
</script>
