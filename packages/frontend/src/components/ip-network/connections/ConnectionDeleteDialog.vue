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
		:tooltip='$t("components.ipNetwork.connections.actions.delete")'
		persistent
		@submit='onSubmit'
	>
		<template #title>
			{{ $t('components.ipNetwork.connections.delete.title') }}
		</template>
		{{ $t('components.ipNetwork.connections.delete.prompt', { name: connection.name }) }}
	</IDeleteModalWindow>
</template>

<script setup lang='ts'>
import {
	type NetworkConnectionService,
} from '@iqrf/iqrf-gateway-webapp-client/services/Network';
import {
	type NetworkConnectionListEntry,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import {
	ComponentState,
	IDeleteModalWindow,
} from '@iqrf/iqrf-vue-ui';
import { type PropType, ref, type Ref, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import { useApiClient } from '@/services/ApiClient';

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
const emit = defineEmits<{
	deleted: [];
}>();
/// Modal dialog reference
const dialog: Ref<InstanceType<typeof IDeleteModalWindow> | null> = useTemplateRef('dialog');
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
	componentState.value = ComponentState.Action;
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
