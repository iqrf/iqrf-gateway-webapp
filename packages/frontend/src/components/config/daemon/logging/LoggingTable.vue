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
			{{ $t('pages.config.daemon.logging.title') }}
		</template>
		<template #titleActions>
			<LoggingForm
				:action='Action.Add'
				:disabled='componentState === ComponentState.Reloading'
				@saved='getConfig()'
			/>
			<IActionBtn
				:action='Action.Reload'
				container-type='card-title'
				:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
				:disabled='componentState === ComponentState.Action'
				:tooltip='$t("components.config.daemon.logging.actions.reload")'
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
			<template #item.actions='{ item }'>
				<LoggingForm
					:action='Action.Edit'
					:logging-profile='toRaw(item)'
					:disabled='componentState === ComponentState.Reloading'
					@saved='getConfig()'
				/>
				<LoggingDeleteDialog
					:logging-instance='item'
					:disabled='componentState === ComponentState.Reloading'
					@deleted='getConfig()'
				/>
			</template>
		</IDataTable>
	</ICard>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	IqrfGatewayDaemonComponentName,
	type ShapeTraceFileService,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IDataTable,
} from '@iqrf/iqrf-vue-ui';
import { computed, onBeforeMount, ref, type Ref, toRaw } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import LoggingDeleteDialog from '@/components/config/daemon/logging/LoggingDeleteDialog.vue';
import LoggingForm from '@/components/config/daemon/logging/LoggingForm.vue';
import { useApiClient } from '@/services/ApiClient';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const headers = [
	{ key: 'instance', title: i18n.t('components.config.daemon.logging.profile') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false },
];
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const instances: Ref<ShapeTraceFileService[]> = ref([]);

const noDataText = computed(() => {
	if (componentState.value === ComponentState.FetchFailed) {
		return i18n.t('components.config.daemon.logging.table.fetchError');
	}
	return i18n.t('components.config.daemon.logging.table.noData');
});

async function getConfig(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		instances.value = (await service.getComponent(IqrfGatewayDaemonComponentName.ShapeTraceFile)).instances;
		componentState.value = ComponentState.Ready;
	} catch {
		toast.error(
			i18n.t('components.config.daemon.logging.messages.list.failed'),
		);
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
	}
}

onBeforeMount(() => {
	getConfig();
});
</script>
