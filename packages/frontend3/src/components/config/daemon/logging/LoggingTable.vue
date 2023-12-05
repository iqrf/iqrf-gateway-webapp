<template>
	<Card>
		<template #title>
			{{ $t('pages.configuration.daemon.logging.title') }}
		</template>
		<template #titleActions>
			<LoggingForm
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
				{{ $t('components.configuration.daemon.logging.actions.reload') }}
			</v-tooltip>
		</template>
		<v-skeleton-loader
			class='input-skeleton-loader'
			:loading='componentState === ComponentState.Loading'
			type='table-heading, table-row-divider@2, table-row'
		>
			<v-responsive>
				<DataTable
					:headers='headers'
					:items='instances'
					:hover='true'
					:dense='true'
					:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
				>
					<template #item.actions='{ item }'>
						<span>
							<LoggingForm
								:action='FormAction.Edit'
								:logging-profile='item'
								@saved='getConfigs'
							/>
							<v-tooltip
								activator='parent'
								location='bottom'
							>
								{{ $t('components.configuration.daemon.logging.actions.edit') }}
							</v-tooltip>
						</span>
						<span>
							<LoggingDeleteDialog
								:logging-instance='item'
								@deleted='getConfigs'
							/>
							<v-tooltip
								activator='parent'
								location='bottom'
							>
								{{ $t('components.configuration.daemon.logging.actions.delete') }}
							</v-tooltip>
						</span>
					</template>
				</DataTable>
			</v-responsive>
		</v-skeleton-loader>
	</Card>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	type IqrfGatewayDaemonComponent,
	IqrfGatewayDaemonComponentName,
	type ShapeTraceFileService,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { mdiReload } from '@mdi/js';
import {
	onMounted,
	type Ref,
	ref,
} from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import Card from '@/components/Card.vue';
import LoggingDeleteDialog from '@/components/config/daemon/logging/LoggingDeleteDialog.vue';
import LoggingForm from '@/components/config/daemon/logging/LoggingForm.vue';
import DataTable from '@/components/DataTable.vue';
import { FormAction } from '@/enums/controls';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const headers = [
	{key: 'instance', title: i18n.t('components.configuration.daemon.logging.profile')},
	{key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false},
];
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const instances: Ref<ShapeTraceFileService[]> = ref([]);

async function getConfigs(): Promise<void> {
	if (componentState.value === ComponentState.Created) {
		componentState.value = ComponentState.Loading;
	} else {
		componentState.value = ComponentState.Reloading;
	}
	service.getComponent(IqrfGatewayDaemonComponentName.ShapeTraceFile)
		.then((response: IqrfGatewayDaemonComponent<IqrfGatewayDaemonComponentName.ShapeTraceFile>): void => {
			instances.value = response.instances;
			componentState.value = ComponentState.Ready;
		})
		.catch(() => toast.error('TODO FETCH ERROR'));
}

onMounted(() => {
	getConfigs();
});
</script>
