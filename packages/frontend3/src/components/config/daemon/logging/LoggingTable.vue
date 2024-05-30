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
			{{ $t('pages.configuration.daemon.logging.title') }}
		</template>
		<template #titleActions>
			<LoggingForm
				:action='Action.Add'
				@saved='getConfigs'
			/>
			<CardTitleActionBtn
				:action='Action.Reload'
				:tooltip='$t("components.configuration.daemon.logging.actions.reload")'
				@click='getConfigs'
			/>
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
						<LoggingForm
							:action='Action.Edit'
							:logging-profile='item'
							@saved='getConfigs'
						/>
						<LoggingDeleteDialog
							:logging-instance='item'
							@deleted='getConfigs'
						/>
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
import { onMounted, type Ref, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import LoggingDeleteDialog from '@/components/config/daemon/logging/LoggingDeleteDialog.vue';
import LoggingForm from '@/components/config/daemon/logging/LoggingForm.vue';
import Card from '@/components/layout/card/Card.vue';
import CardTitleActionBtn from '@/components/layout/card/CardTitleActionBtn.vue';
import DataTable from '@/components/layout/data-table/DataTable.vue';
import { useApiClient } from '@/services/ApiClient';
import { Action } from '@/types/Action';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const headers = [
	{ key: 'instance', title: i18n.t('components.configuration.daemon.logging.profile') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false },
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
