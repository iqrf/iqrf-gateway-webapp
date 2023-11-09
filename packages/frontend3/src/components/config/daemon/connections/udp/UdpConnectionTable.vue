<template>
	<Card>
		<template #title>
			{{ $t('pages.configuration.daemon.connections.udp.title') }}
		</template>
		<template #titleActions>
			<UdpConnectionForm
				:action='FormAction.Add'
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
				{{ $t('components.configuration.daemon.connections.udp.actions.reload') }}
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
import { mdiReload } from '@mdi/js';
import { type Ref, ref } from 'vue';
import { onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import Card from '@/components/Card.vue';
import UdpConnectionDeleteDialog from '@/components/config/daemon/connections/udp/UdpConnectionDeleteDialog.vue';
import UdpConnectionForm from '@/components/config/daemon/connections/udp/UdpConnectionForm.vue';
import DataTable from '@/components/DataTable.vue';
import { FormAction } from '@/enums/controls';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';


const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const headers = [
	{key: 'instance', title: i18n.t('components.configuration.daemon.connections.profile')},
	{key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false},
];
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const instances: Ref<IqrfGatewayDaemonUdpMessaging[]> = ref([]);

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

onMounted(() => {
	getConfigs();
});
</script>