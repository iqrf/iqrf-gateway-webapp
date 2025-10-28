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
			{{ $t('components.config.daemon.connections.websocket.messaging.title') }}
		</template>
		<template #titleActions>
			<WsMessagingForm
				ref='form'
				:action='Action.Add'
				:service-instances='serviceInstances'
				:disabled='componentState === ComponentState.Reloading'
				@saved='getConfigs()'
			/>
			<WsMessagingImportDialog
				:disabled='componentState === ComponentState.Reloading'
				@import='(m: IqrfGatewayDaemonWsMessaging) => importFromConfig(m)'
			/>
			<IActionBtn
				:action='Action.Reload'
				container-type='card-title'
				:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
				:tooltip='$t("components.config.daemon.connections.websocket.messaging.actions.reload")'
				@click='getConfigs()'
			/>
		</template>
		<IDataTable
			:headers='headers'
			:items='wsMessagings'
			:hover='true'
			:dense='true'
			:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
		>
			<template #item.instance='{ item }'>
				{{ item.instance }}
			</template>
			<template #item.acceptAsyncMsg='{ item }'>
				<BooleanCheckMarker :value='item.acceptAsyncMsg' />
			</template>
			<template #item.service='{ item }'>
				<span>
					{{ item.RequiredInterfaces[0].target.instance }}
				</span>
				<WsServiceMissingIcon
					v-if='!serviceInstances.includes(item.RequiredInterfaces[0].target.instance)'
					class='ml-1'
				/>
			</template>
			<template #item.actions='{ item }'>
				<IDataTableAction
					:action='Action.Export'
					:tooltip='$t("components.config.daemon.connections.websocket.messaging.actions.export")'
					:disabled='[ComponentState.Action, ComponentState.Reloading].includes(componentState)'
					@click='exportConfig(item)'
				/>
				<WsMessagingForm
					:action='Action.Edit'
					:messaging-instance='toRaw(item)'
					:service-instances='serviceInstances'
					:disabled='componentState === ComponentState.Reloading'
					@saved='getConfigs()'
				/>
				<WsMessagingDeleteDialog
					:connection-messaging='toRaw(item)'
					:disabled='componentState === ComponentState.Reloading'
					@deleted='getConfigs()'
				/>
			</template>
		</IDataTable>
	</ICard>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	IqrfGatewayDaemonComponentName,
	type IqrfGatewayDaemonWsMessaging,
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
import {
	PropType,
	ref,
	type Ref,
	type TemplateRef,
	toRaw,
	useTemplateRef,
} from 'vue';
import { onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import BooleanCheckMarker from '@/components/BooleanCheckMarker.vue';
import WsMessagingDeleteDialog from '@/components/config/daemon/connections/websocket/messagings/WsMessagingDeleteDialog.vue';
import WsMessagingForm from '@/components/config/daemon/connections/websocket/messagings/WsMessagingForm.vue';
import WsMessagingImportDialog from '@/components/config/daemon/connections/websocket/messagings/WsMessagingImportDialog.vue';
import WsServiceMissingIcon from '@/components/config/daemon/connections/websocket/messagings/WsServiceMissingIcon.vue';
import { useApiClient } from '@/services/ApiClient';

defineProps({
	serviceInstances: {
		type: Array as PropType<string[]>,
		required: true,
	},
});
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const headers = [
	{ key: 'instance', title: i18n.t('components.config.daemon.connections.websocket.messaging.name') },
	{ key: 'acceptAsyncMsg', title: i18n.t('components.config.daemon.connections.acceptAsyncMessages') },
	{ key: 'service', title: i18n.t('components.config.daemon.connections.websocket.messaging.service') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false },
];
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const wsMessagings: Ref<IqrfGatewayDaemonWsMessaging[]> = ref([]);
const form: TemplateRef<InstanceType<typeof WsMessagingForm>> = useTemplateRef('form');

async function getConfigs(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		wsMessagings.value = (await service.getComponent(IqrfGatewayDaemonComponentName.IqrfWsMessaging)).instances;
		componentState.value = ComponentState.Ready;
	} catch {
		toast.error(
			i18n.t('components.config.daemon.connections.websocket.messaging.messages.fetch.failed'),
		);
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
	}
}

function importFromConfig(messaging: IqrfGatewayDaemonWsMessaging): void {
	if (form.value === null) {
		return;
	}
	form.value.importFromConfig(messaging);
}

function exportConfig(config: IqrfGatewayDaemonWsMessaging): void {
	FileDownloader.downloadFromData(
		config,
		'application/json',
		`${config.component.replace('::', '__')}__${config.instance}.json`,
	);
}

onMounted(() => {
	getConfigs();
});

</script>
