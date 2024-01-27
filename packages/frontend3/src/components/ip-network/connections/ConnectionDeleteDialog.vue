<template>
	<DeleteModalWindow
		ref='dialog'
		:component-state='componentState'
		:tooltip='$t("components.ipNetwork.connections.actions.delete")'
		@submit='onSubmit'
	>
		<template #title>
			{{ $t('components.ipNetwork.connections.delete.title') }}
		</template>
		{{ $t('components.ipNetwork.connections.delete.prompt', {name: connection.name}) }}
	</DeleteModalWindow>
</template>
<script setup lang='ts'>
import {
	type NetworkConnectionService,
} from '@iqrf/iqrf-gateway-webapp-client/services/Network/NetworkConnectionService';
import {
	type NetworkConnectionListEntry,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network/NetworkConnection';
import { type PropType, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import DeleteModalWindow from '@/components/DeleteModalWindow.vue';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

/// Component state
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
/// Component properties
const componentProps = defineProps({
	connection: {
		type: Object as PropType<NetworkConnectionListEntry>,
		required: true,
	},
});
/// Define emit events
const emit = defineEmits(['deleted']);
/// Modal dialog reference
const dialog: Ref<typeof DeleteModalWindow | null> = ref(null);
/// Internationalization instance
const i18n = useI18n();
/// Network connection service
const service: NetworkConnectionService = useApiClient().getNetworkServices().getNetworkConnectionService();

/**
 * Closes modal dialog
 */
function close(): void {
	dialog.value?.close();
}

/**
 * Handles submit event
 */
function onSubmit(): void {
	if (componentProps.connection === undefined || componentProps.connection === null) {
		return;
	}
	componentState.value = ComponentState.Saving;
	service.delete(componentProps.connection.uuid)
		.then(() => {
			componentState.value = ComponentState.Ready;
			toast.success(
				i18n.t('components.ipNetwork.connections.delete.messages.success', { name: componentProps.connection.name }),
			);
			close();
			emit('deleted');
		})
		.catch(() => {
			componentState.value = ComponentState.Error;
			toast.error(
				i18n.t('components.ipNetwork.connections.delete.messages.failure', { name: componentProps.connection.name }),
			);
		});
}
</script>
