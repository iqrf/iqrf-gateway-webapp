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
	<IDeleteModalWindow
		ref='dialog'
		:component-state='componentState'
		:tooltip='$t("components.config.daemon.connections.websocket.service.actions.delete")'
		:disabled='disabled'
		persistent
		@submit='onSubmit()'
	>
		<template #title>
			{{ $t('components.config.daemon.connections.websocket.service.delete.title') }}
		</template>
		{{ $t('components.config.daemon.connections.websocket.service.delete.prompt', { name: connectionService.instance }) }}
	</IDeleteModalWindow>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	IqrfGatewayDaemonComponentName,
	type ShapeWebsocketService,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {
	ComponentState,
	IDeleteModalWindow,
} from '@iqrf/iqrf-vue-ui';
import {
	type PropType,
	ref,
	type Ref,
	useTemplateRef,
} from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import { useApiClient } from '@/services/ApiClient';

const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const componentProps = defineProps({
	connectionService: {
		type: Object as PropType<ShapeWebsocketService>,
		required: true,
	},
	disabled: {
		type: Boolean,
		required: false,
		default: false,
	},
});
const emit = defineEmits<{
	deleted: [];
}>();
const dialog: Ref<InstanceType<typeof IDeleteModalWindow> | null> = useTemplateRef('dialog');
const i18n = useI18n();
const service: IqrfGatewayDaemonService = useApiClient()
	.getConfigServices()
	.getIqrfGatewayDaemonService();

async function onSubmit(): Promise<void> {
	componentState.value = ComponentState.Action;
	const translationParams = { name: componentProps.connectionService.instance };
	try {
		await service.deleteInstance(IqrfGatewayDaemonComponentName.ShapeWebsocketService, componentProps.connectionService.instance);
		toast.success(
			i18n.t('components.config.daemon.connections.websocket.service.messages.delete.success', translationParams),
		);
		close();
		emit('deleted');
	} catch {
		toast.error(
			i18n.t('components.config.daemon.connections.websocket.service.messages.delete.failed', translationParams),
		);
	}
	componentState.value = ComponentState.Idle;
}

function close(): void {
	dialog.value?.close();
}
</script>
