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
		ref='actionDialogInstance'
		:enabled='wgListEntry.enabled'
		:disable-tooltip='$t("components.ipNetwork.wireGuard.tunnels.columns.action.disable")'
		:enable-tooltip='$t("components.ipNetwork.wireGuard.tunnels.columns.action.enable")'
		:title='$t("components.ipNetwork.wireGuard.tunnels.disable.title")'
		:prompt='$t("components.ipNetwork.wireGuard.tunnels.disable.prompt", { name: wgListEntry.name })'
		:disable-button-text='$t("components.ipNetwork.wireGuard.tunnels.disable.disable")'
		:disable-icon='mdiStopCircleOutline'
		:enable-icon='mdiPlayCircleOutline'
		@disable='disable'
		@enable='enable'
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
import { mdiPlayCircleOutline, mdiStopCircleOutline } from '@mdi/js';
import { ref, type Ref, type TemplateRef, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import { useApiClient } from '@/services/ApiClient';

import WireGuardActionDialog from './WireGuardActionDialog.vue';


/// Component props
const componentProps = defineProps<{
	/// WireGuard tunnel data
	wgListEntry: WireGuardTunnelListEntry;
}>();
/// Component emits
const emit = defineEmits<{
	updateEnableFlag: [tunnelId: number];
}>();
/// Component state
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
/// Internationalization instance
const i18n = useI18n();
/// Network connection service
const service: WireGuardService = useApiClient().getNetworkServices().getWireGuardService();
/// Action dialog component instance
const actionDialogInstance: TemplateRef<InstanceType<typeof WireGuardActionDialog>> = useTemplateRef('actionDialogInstance');

/**
 * Activates WireGuard tunnel
 */
async function enable(): Promise<void> {
	if (componentProps.wgListEntry === undefined) {
		return;
	}
	componentState.value = ComponentState.Action;
	try {
		await service.enableTunnel(componentProps.wgListEntry.id);
		componentState.value = ComponentState.Ready;
		toast.success(
			i18n.t('components.ipNetwork.wireGuard.tunnels.columns.action.success.enable'),
		);
		emit('updateEnableFlag', componentProps.wgListEntry.id);
	} catch {
		componentState.value = ComponentState.Error;
		toast.error(
			i18n.t('components.ipNetwork.wireGuard.tunnels.columns.action.failure.enable'),
		);
	}
}

/**
 * Deactivates WireGuard tunnel
 */
async function disable(): Promise<void> {
	if (componentProps.wgListEntry === undefined) {
		return;
	}
	componentState.value = ComponentState.Action;
	try {
		await service.disableTunnel(componentProps.wgListEntry.id);
		componentState.value = ComponentState.Ready;
		toast.success(
			i18n.t('components.ipNetwork.wireGuard.tunnels.columns.action.success.disable'),
		);
		actionDialogInstance.value?.close();
		emit('updateEnableFlag', componentProps.wgListEntry.id);
	} catch {
		componentState.value = ComponentState.Error;
		toast.error(
			i18n.t('components.ipNetwork.wireGuard.tunnels.columns.action.failure.disable'),
		);
	}
}
</script>
