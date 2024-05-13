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
	<DeleteModalWindow
		ref='dialog'
		:tooltip='$t("components.configuration.daemon.connections.actions.delete")'
		@submit='onSubmit'
	>
		<template #title>
			{{ $t('components.configuration.daemon.connections.mqtt.delete.title') }}
		</template>
		{{ $t('components.configuration.daemon.connections.mqtt.delete.prompt', {name: connectionProfile.instance}) }}
	</DeleteModalWindow>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	IqrfGatewayDaemonComponentName,
	type IqrfGatewayDaemonMqttMessaging,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {
	ref,
	type Ref,
	type PropType,
} from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import DeleteModalWindow from '@/components/DeleteModalWindow.vue';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const componentProps = defineProps({
	connectionProfile: {
		type: Object as PropType<IqrfGatewayDaemonMqttMessaging>,
		required: true,
	},
});
const emit = defineEmits(['deleted']);
const dialog: Ref<typeof DeleteModalWindow | null> = ref(null);
const i18n = useI18n();
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();

function onSubmit(): void {
	componentState.value = ComponentState.Saving;
	service.deleteInstance(IqrfGatewayDaemonComponentName.IqrfMqttMessaging, componentProps.connectionProfile.instance)
		.then(() => {
			componentState.value = ComponentState.Ready;
			toast.success(
				i18n.t('components.configuration.daemon.connections.mqtt.messages.delete.success', { name: componentProps.connectionProfile.instance }),
			);
			close();
			emit('deleted');
		})
		.catch(() => toast.error('TODO ERROR HANDLING'));
}

function close(): void {
	dialog.value?.close();
}
</script>
