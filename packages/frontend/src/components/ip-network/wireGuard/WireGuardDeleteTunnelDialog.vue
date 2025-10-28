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
		:tooltip='$t("components.ipNetwork.wireGuard.tunnels.columns.action.delete")'
		persistent
		@submit='onSubmit'
	>
		<template #title>
			{{ $t('components.ipNetwork.wireGuard.tunnels.delete.title') }}
		</template>
		{{ $t('components.ipNetwork.wireGuard.tunnels.delete.prompt', { name: tunnel.name }) }}
	</IDeleteModalWindow>
</template>

<script setup lang='ts'>
import {
	type WireGuardService,
} from '@iqrf/iqrf-gateway-webapp-client/services/Network';
import {
	type WireGuardTunnelListEntry,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import {
	ComponentState,
	IDeleteModalWindow,
} from '@iqrf/iqrf-vue-ui';
import {
	type PropType,
	ref,
	type Ref,
	type TemplateRef,
	useTemplateRef,
} from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import { useApiClient } from '@/services/ApiClient';

/// Component properties
const componentProps = defineProps({
	tunnel: {
		type: Object as PropType<WireGuardTunnelListEntry>,
		required: true,
	},
});
/// Define emit events
const emit = defineEmits<{
	deleted: [];
}>();
/// Component state
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
/// Modal dialog reference
const dialog: TemplateRef<InstanceType<typeof IDeleteModalWindow>> = useTemplateRef('dialog');
/// Internationalization instance
const i18n = useI18n();
/// WireGuard service
const service: WireGuardService = useApiClient().getNetworkServices().getWireGuardService();

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
	if (componentProps.tunnel === undefined || componentProps.tunnel === null) {
		return;
	}
	componentState.value = ComponentState.Action;
	try {
		await service.deleteTunnel(componentProps.tunnel?.id);
		componentState.value = ComponentState.Ready;
		toast.success(
			i18n.t('components.ipNetwork.wireGuard.tunnels.delete.messages.success', { name: componentProps.tunnel.name }),
		);
		close();
		emit('deleted');
	} catch {
		componentState.value = ComponentState.Error;
		toast.error(
			i18n.t('components.ipNetwork.wireGuard.tunnels.delete.messages.failure', { name: componentProps.tunnel.name }),
		);
	}
}
</script>
