<template>
	<DeleteModalWindow
		ref='dialog'
		:component-state='componentState'
		:tooltip='$t("components.configuration.daemon.logging.actions.delete")'
		@submit='onSubmit'
	>
		<template #title>
			{{ $t('components.configuration.daemon.logging.delete.title') }}
		</template>
		{{ $t('components.configuration.daemon.logging.delete.prompt', {name: loggingInstance.instance}) }}
	</DeleteModalWindow>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	IqrfGatewayDaemonComponentName,
	type ShapeTraceFileService,
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
	loggingInstance: {
		type: Object as PropType<ShapeTraceFileService>,
		required: true,
	},
});
const emit = defineEmits(['deleted']);
const dialog: Ref<typeof DeleteModalWindow | null> = ref(null);
const i18n = useI18n();
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();

function onSubmit(): void {
	componentState.value = ComponentState.Saving;
	service.deleteInstance(IqrfGatewayDaemonComponentName.ShapeTraceFile, componentProps.loggingInstance.instance)
		.then(() => {
			componentState.value = ComponentState.Ready;
			toast.success(
				i18n.t('components.configuration.daemon.logging.messages.delete.success', { name: componentProps.loggingInstance.instance }),
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
