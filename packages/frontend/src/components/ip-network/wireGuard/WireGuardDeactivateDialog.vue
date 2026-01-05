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
	<WireGuardActionDialog
		ref='acitonDialogInstance'
		:enabled='wgListEntry.active'
		:disable-tooltip='$t("components.ipNetwork.wireGuard.tunnels.columns.action.deactivate")'
		:enable-tooltip='$t("components.ipNetwork.wireGuard.tunnels.columns.action.activate")'
		:title='$t("components.ipNetwork.wireGuard.tunnels.deactivate.title")'
		:prompt='$t("components.ipNetwork.wireGuard.tunnels.deactivate.prompt", { name: wgListEntry.name })'
		:disable-button-text='$t("components.ipNetwork.wireGuard.tunnels.deactivate.deactivate")'
		:disable-icon='mdiLinkVariantOff'
		:enable-icon='mdiLinkVariant'
		@disable='deactivate'
		@enable='activate'
	/>
</template>

<script setup lang='ts'>
import {
	WireGuardService,
} from '@iqrf/iqrf-gateway-webapp-client/services/Network';
import {
	WireGuardTunnelListEntry,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import {
	ComponentState,
} from '@iqrf/iqrf-vue-ui';
import { mdiLinkVariant, mdiLinkVariantOff } from '@mdi/js';
import { type PropType, ref, type Ref, TemplateRef, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import { useApiClient } from '@/services/ApiClient';

import WireGuardActionDialog from './WireGuardActionDialog.vue';

/// Component props
const componentProps = defineProps({
	/// WireGuard tunnel data
	wgListEntry: {
		type: Object as PropType<WireGuardTunnelListEntry>,
		required: true,
	},
});
/// Component emits
const emit = defineEmits<{
	updateActiveFlag: [tunnelId: number];
}>();
/// Component state
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
/// Internationalization instance
const i18n = useI18n();
/// Network connection service
const service: WireGuardService = useApiClient().getNetworkServices().getWireGuardService();
/// Action dialog component instance
const acitonDialogInstance: TemplateRef<InstanceType<typeof WireGuardActionDialog>> = useTemplateRef('acitonDialogInstance');

/**
 * Activates WireGuard tunnel
 */
async function activate(): Promise<void> {
	if (componentProps.wgListEntry === undefined) {
		return;
	}
	componentState.value = ComponentState.Action;
	try {
		await service.activateTunnel(componentProps.wgListEntry.id);
		componentState.value = ComponentState.Ready;
		toast.success(i18n.t('components.ipNetwork.wireGuard.tunnels.columns.action.success.activate'));
		emit('updateActiveFlag', componentProps.wgListEntry.id);
	} catch {
		componentState.value = ComponentState.Error;
		toast.error(i18n.t('components.ipNetwork.wireGuard.tunnels.columns.action.failure.activate'));
	}
}

/**
 * Deactivates WireGuard tunnel
 */
async function deactivate(): Promise<void> {
	if (componentProps.wgListEntry === undefined) {
		return;
	}
	componentState.value = ComponentState.Action;
	try {
		await service.deactivateTunnel(componentProps.wgListEntry.id);
		componentState.value = ComponentState.Ready;
		toast.success(
			i18n.t('components.ipNetwork.wireGuard.tunnels.columns.action.success.deactivate'),
		);
		acitonDialogInstance.value?.close();
		emit('updateActiveFlag', componentProps.wgListEntry.id);
	} catch {
		componentState.value = ComponentState.Error;
		toast.error(
			i18n.t('components.ipNetwork.wireGuard.tunnels.columns.action.failure.deactivate'),
		);
	}
}
</script>
