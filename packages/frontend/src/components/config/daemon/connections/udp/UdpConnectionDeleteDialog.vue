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
		:tooltip='$t("components.config.daemon.connections.actions.delete")'
		@submit='onSubmit()'
	>
		<template #title>
			{{ $t('components.config.daemon.connections.udp.delete.title') }}
		</template>
		{{ $t('components.config.daemon.connections.udp.delete.prompt', { name: connectionProfile.instance }) }}
	</IDeleteModalWindow>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	IqrfGatewayDaemonComponentName,
	type IqrfGatewayDaemonUdpMessaging,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {
	ComponentState,
	IDeleteModalWindow,
} from '@iqrf/iqrf-vue-ui';
import {
	type PropType,
	ref,
	type Ref,
} from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import { useApiClient } from '@/services/ApiClient';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const componentProps = defineProps({
	connectionProfile: {
		type: Object as PropType<IqrfGatewayDaemonUdpMessaging>,
		required: true,
	},
});
const emit = defineEmits(['deleted']);
const dialog: Ref<typeof IDeleteModalWindow | null> = ref(null);
const i18n = useI18n();
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();

async function onSubmit(): Promise<void> {
	componentState.value = ComponentState.Action;
	try {
		await service.deleteInstance(IqrfGatewayDaemonComponentName.IqrfUdpMessaging, componentProps.connectionProfile.instance);
		toast.success(
			i18n.t('components.config.daemon.connections.udp.messages.delete.success', { name: componentProps.connectionProfile.instance }),
		);
		close();
		emit('deleted');

	} catch {
		toast.error('TODO ERROR HANDLING');
	}
	componentState.value = ComponentState.Ready;
}

function close(): void {
	dialog.value?.close();
}
</script>
