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
		:tooltip='$t("components.config.daemon.logging.actions.delete")'
		:disabled='disabled'
		persistent
		@submit='onSubmit()'
	>
		<template #title>
			{{ $t('components.config.daemon.logging.delete.title') }}
		</template>
		{{ $t('components.config.daemon.logging.delete.prompt', { name: loggingInstance.instance }) }}
	</IDeleteModalWindow>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	IqrfGatewayDaemonComponentName,
	type ShapeTraceFileService,
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

const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const componentProps = defineProps({
	loggingInstance: {
		type: Object as PropType<ShapeTraceFileService>,
		required: true,
	},
	disabled: {
		type: Boolean,
		required: false,
		default: false,
	},
});
const emit = defineEmits(['deleted']);
const dialog: Ref<typeof IDeleteModalWindow | null> = ref(null);
const i18n = useI18n();
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();

async function onSubmit(): Promise<void> {
	componentState.value = ComponentState.Action;
	try {
		await service.deleteInstance(IqrfGatewayDaemonComponentName.ShapeTraceFile, componentProps.loggingInstance.instance);
		toast.success(
			i18n.t('components.config.daemon.logging.messages.delete.success', { name: componentProps.loggingInstance.instance }),
		);
		close();
		emit('deleted');
	} catch {
		toast.error(
			i18n.t('components.config.daemon.logging.messages.delete.failed', { name: componentProps.loggingInstance.instance }),
		);
	}
	componentState.value = ComponentState.Idle;
}

function close(): void {
	dialog.value?.close();
}
</script>
