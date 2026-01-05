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
	<IModalWindow
		v-model='showDialog'
		persistent
	>
		<template #activator='{ props }'>
			<IActionBtn
				v-if='componentProps.action === Action.Add'
				container-type='card-title'
				:action='Action.Add'
				:tooltip='$t("components.ipNetwork.wireGuard.tunnels.configuration.form.addTunnel")'
				v-bind='props'
			/>
			<IDataTableAction
				v-else-if='componentProps.action === Action.Edit'
				:action='Action.Edit'
				:tooltip='$t("components.ipNetwork.wireGuard.tunnels.configuration.form.editTunnel")'
				v-bind='props'
			/>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			:disabled='[ComponentState.Reloading, ComponentState.Action].includes(componentState)'
			@submit.prevent='onSubmit(false, false)'
		>
			<ICard>
				<template #title>
					<span>{{ $t("components.ipNetwork.wireGuard.tunnels.configuration.title") }}</span>
				</template>
				<v-alert
					v-if='componentState === ComponentState.FetchFailed'
					type='error'
					variant='tonal'
					:text='$t("common.messages.fetchFailed")'
				/>
				<v-skeleton-loader
					class='input-skeleton-loader'
					:loading='componentState === ComponentState.Loading'
					type='heading@8, button'
				>
					<ITextInput
						v-model='wgConfig.name'
						:label='$t("components.ipNetwork.wireGuard.tunnels.configuration.form.name")'
						:rules='[
							(v: string | null) => ValidationRules.required(
								v,
								$t("components.ipNetwork.wireGuard.tunnels.configuration.validation.name"),
							),
						]'
						required
					/>
					<INumberInput
						v-model='wgConfig.port'
						:label='$t("components.ipNetwork.wireGuard.tunnels.configuration.form.port")'
					/>
					<ITextInput
						v-model='wgConfig.publicKey'
						:label='$t("components.ipNetwork.wireGuard.tunnels.configuration.form.publicKey")'
						:readonly='!editKeys'
						:variant='editKeys ? "filled" : "solo"'
					>
						<template #append>
							<v-btn
								color='success'
								@click='copyToClipboard(wgConfig.publicKey)'
							>
								<v-icon :icon='mdiClipboard' />
								{{ $t('common.buttons.clipboard') }}
							</v-btn>
						</template>
					</ITextInput>
					<ITextInput
						v-model='wgConfig.privateKey'
						:label='$t("components.ipNetwork.wireGuard.tunnels.configuration.form.privateKey")'
						:readonly='!editKeys'
						:variant='editKeys ? "filled" : "solo"'
					>
						<template #append>
							<v-btn
								color='success'
								@click='copyToClipboard(wgConfig.privateKey)'
							>
								<v-icon :icon='mdiClipboard' />
								{{ $t('common.buttons.clipboard') }}
							</v-btn>
						</template>
					</ITextInput>
					<div class='mb-6'>
						<WireGuardGenerateKeyDialog
							ref='generateKeyDialogInstance'
							:key-exists='wgConfig.publicKey !== ""'
							@generate-key='generateKey()'
						/>
						<IActionBtn
							:text='$t("components.ipNetwork.wireGuard.tunnels.configuration.form.changeKeyPair")'
							color='info'
							:icon='mdiUploadOutline'
							class='mr-2 ml-2'
							@click='editKeys = !editKeys'
						/>
					</div>
					<v-select
						v-model='wgConfig.stack'
						:label='$t("components.ipNetwork.wireGuard.tunnels.configuration.form.stack")'
						:items='stackSelecOptions'
					/>
					<WireGuardIpConfig
						v-if='WireGuardIpStack.IPV6 !== wgConfig.stack'
						record-id='TunnelIPv4'
						:type='WireGuardIpStack.IPV4'
						:ip-address='wgConfig.ipv4'
					/>
					<WireGuardIpConfig
						v-if='WireGuardIpStack.IPV4 !== wgConfig.stack'
						record-id='TunnelIPv6'
						:type='WireGuardIpStack.IPV6'
						:ip-address='wgConfig.ipv6'
					/>
				</v-skeleton-loader>
				<template #actions>
					<IActionBtn
						v-if='![ComponentState.Error || ComponentState.FetchFailed].includes(componentState)'
						:action='Action.Save'
						container-type='card'
						:loading='componentState === ComponentState.Loading'
						:disabled='!isValid.value'
						type='submit'
					/>
					<IActionBtn
						v-if='![ComponentState.Error || ComponentState.FetchFailed].includes(componentState)'
						color='info'
						:icon='mdiContentSave'
						container-type='card'
						:disabled='!isValid.value'
						:loading='componentState === ComponentState.Action'
						:text='$t("components.ipNetwork.wireGuard.tunnels.configuration.form.saveEnable")'
						@click='onSubmit(true, false)'
					/>
					<IActionBtn
						v-if='![ComponentState.Error || ComponentState.FetchFailed].includes(componentState)'
						color='info'
						:icon='mdiContentSave'
						container-type='card'
						:disabled='!isValid.value'
						:loading='componentState === ComponentState.Action'
						:text='$t("components.ipNetwork.wireGuard.tunnels.configuration.form.saveActivate")'
						@click='onSubmit(false, true)'
					/>
					<v-spacer />
					<IActionBtn
						:action='Action.Close'
						container-type='card'
						:disabled='componentState === ComponentState.Action'
						@click='closeDialog()'
					/>
				</template>
			</ICard>
		</v-form>
	</IModalWindow>
</template>

<script setup lang='ts'>
import { WireGuardIpStack, WireGuardTunnelConfig } from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import { Action, ComponentState, IActionBtn, ICard, IDataTableAction, IModalWindow, INumberInput, ITextInput, ValidationRules } from '@iqrf/iqrf-vue-ui';
import { mdiClipboard, mdiContentSave, mdiUploadOutline } from '@mdi/js';
import { onMounted, type PropType, ref, type Ref, TemplateRef, useTemplateRef, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { type VForm } from 'vuetify/components';

import { useApiClient } from '@/services/ApiClient';

import WireGuardGenerateKeyDialog from './WireGuardGenerateKeyDialog.vue';
import WireGuardIpConfig from './WireGuardIpConfig.vue';

/// Define props
const componentProps = defineProps({
	action: {
		type: String as PropType<Action>,
		required: true,
	},
	tunnelId: {
		type: [Number, null] as PropType<number | null>,
		required: false,
		default: null,
	},
});

const emit = defineEmits<{
	updateTunnel: [tunnel: WireGuardTunnelConfig, enabled: boolean, active: boolean];
}>();

/// WireGuard API service
const service = useApiClient().getNetworkServices().getWireGuardService();
/// Dialog visibility
const showDialog: Ref<boolean> = ref(false);
/// Form instance
const form: TemplateRef<VForm> = useTemplateRef('form');
/// Stack select values
const stackSelecOptions = [
	{
		'title': 'IPv4',
		'value': WireGuardIpStack.IPV4,
	},
	{
		'title': 'IPv6',
		'value': WireGuardIpStack.IPV6,
	},
	{
		'title': 'Dual',
		'value': WireGuardIpStack.DUAL,
	},
];
/// Returns default WireGuard tunnel configuration
const getDefaultConfig = (): WireGuardTunnelConfig => ({
	name: '',
	privateKey: '',
	publicKey: '',
	port: 51_820,
	ipv4: { address: '', prefix: 24 },
	ipv6: { address: '', prefix: 48 },
	stack: WireGuardIpStack.DUAL,
});
/// Wireguard tunnel configuration
const wgConfig: Ref<WireGuardTunnelConfig> = ref(getDefaultConfig());
/// Internationalization instance
const i18n = useI18n();
/// Component state
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
/// Allow editing of key pair
const editKeys: Ref<boolean> = ref(false);

const generateKeyDialogInstance: TemplateRef<InstanceType<typeof WireGuardGenerateKeyDialog>> = useTemplateRef('generateKeyDialogInstance');

/**
 * Closes the dialog window
 */
function closeDialog(): void {
	showDialog.value = false;
}

/**
 * Fetches wireguard configuration
 * @param {number} id - id of record to fetch
 */
async function fetchConfig(id: number): Promise<void> {
	try {
		wgConfig.value = await service.getTunnel(id);
	} catch {
		componentState.value = ComponentState.FetchFailed;
	}
}

/**
 * Verifies and saves configuration changes.
 * @param {boolean} enable enable the serice when saved (includes activate)
 * @param {boolean} activate activate the service when saved
 */
async function onSubmit(enable: boolean, activate: boolean): Promise<void> {
	let response = null;
	const payload: WireGuardTunnelConfig = { ...wgConfig.value };
	if (payload.stack === WireGuardIpStack.IPV4) {
		delete payload.ipv6;
	} else if (payload.stack === WireGuardIpStack.IPV6) {
		delete payload.ipv4;
	}
	try {
		if (componentProps.action === Action.Add) {
			response = await service.createTunnel(payload);
		} else {
			response = await service.updateTunnel(wgConfig.value.id!, payload);
		}
		if (enable) service.enableTunnel(response.id!);
		if (activate) service.activateTunnel(response.id!);
		if (componentProps.action === Action.Add) {
			toast.success(i18n.t('components.ipNetwork.wireGuard.tunnels.add.messages.success'));
		} else {
			toast.success(i18n.t('components.ipNetwork.wireGuard.tunnels.update.messages.success'));
		}
		emit('updateTunnel', response, enable, activate);
		componentState.value = ComponentState.Ready;
		if (componentProps.action === Action.Add) {
			resetForm();
		}
		closeDialog();
	} catch {
		componentState.value = ComponentState.Error;
		if (componentProps.action === Action.Add) {
			toast.error(i18n.t('components.ipNetwork.wireGuard.tunnels.add.messages.failure'));
		} else {
			toast.error(i18n.t('components.ipNetwork.wireGuard.tunnels.update.messages.failure'));
		}
	}
}

/**
 * Copy field value to clipboard
 * @param {any} value Value to copy to clipboard.
 */
function copyToClipboard(value: any): void {
	navigator.clipboard.writeText(value);
	toast.success(i18n.t('components.ipNetwork.wireGuard.tunnels.configuration.form.copyKey'));
}

/**
 * Generate key
 */
async function generateKey(): Promise<void> {
	let response = null;
	try {
		response = await service.generateKeyPair();
		generateKeyDialogInstance.value?.close();
	} catch {
		componentState.value = ComponentState.Error;
		toast.error(i18n.t('components.ipNetwork.wireGuard.tunnels.configuration.form.genKeyPair.error'));
		return;
	}
	wgConfig.value.privateKey = response.privateKey;
	wgConfig.value.publicKey = response.publicKey;
}

watch(
	() => wgConfig.value.stack,
	() => {
		form.value?.resetValidation();
	},
);

/**
 * Resets form back to default values
 */
function resetForm(): void {
	wgConfig.value = getDefaultConfig();
	editKeys.value = false;
	componentState.value = ComponentState.Created;
	form.value?.resetValidation();
}

onMounted(() => {
	if (componentProps.tunnelId) fetchConfig(componentProps.tunnelId);
});
</script>
