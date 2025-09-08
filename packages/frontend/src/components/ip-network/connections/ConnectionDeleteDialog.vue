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
	<DeleteModalWindow
		ref='dialog'
		:component-state='componentState'
		:tooltip='$t("components.ipNetwork.connections.actions.delete")'
		@submit='onSubmit'
	>
		<template #title>
			{{ $t('components.ipNetwork.connections.delete.title') }}
		</template>
		{{ $t('components.ipNetwork.connections.delete.prompt', { name: connection.name }) }}
	</DeleteModalWindow>
</template>
<script setup lang='ts'>
import {
	type NetworkConnectionService,
} from '@iqrf/iqrf-gateway-webapp-client/services/Network';
import {
	type NetworkConnectionListEntry,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
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
async function onSubmit(): Promise<void> {
	if (componentProps.connection === undefined) {
		return;
	}
	componentState.value = ComponentState.Saving;
	const translationParams = { name: componentProps.connection.name };
	try {
		await service.delete(componentProps.connection.uuid);
		componentState.value = ComponentState.Ready;
		toast.success(
			i18n.t('components.ipNetwork.connections.delete.messages.success', translationParams),
		);
		close();
		emit('deleted');
	} catch {
		componentState.value = ComponentState.Error;
		toast.error(
			i18n.t('components.ipNetwork.connections.delete.messages.failure', translationParams),
		);
	}
}
</script>
