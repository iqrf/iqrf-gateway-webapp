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
			v-slot='{ isValid }'
			:disabled='[ComponentState.Reloading, ComponentState.Action].includes(componentState)'
			@submit.prevent='onSubmit(false, false)'
		>
			<ICard>
				<template #title>
					{{ $t("components.ipNetwork.wireGuard.tunnels.configuration.title") }}
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
								&nbsp;
								{{ $t('common.buttons.clipboard') }}
							</v-btn>
						</template>
					</ITextInput>
					<v-alert
						v-if='showPrivateKeyWarning'
						color='warning'
						:text='$t("components.ipNetwork.wireGuard.tunnels.configuration.form.privateKeyWarning")'
						variant='tonal'
						class='mb-5'
					/>
					<ITextInput
						v-if='showPrivateKey'
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
								&nbsp;
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
					<WireGuardIpStackSelect v-model='wgConfig.stack' />
					<WireGuardIpConfig
						v-if='WireGuardIpStack.IPV6 !== wgConfig.stack'
						ref='ipv4config'
						record-id='TunnelIPv4'
						:type='WireGuardIpStack.IPV4'
						:ip-address='wgConfig.ipv4'
					/>
					<WireGuardIpConfig
						v-if='WireGuardIpStack.IPV4 !== wgConfig.stack'
						ref='ipv6config'
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

<script setup lang="ts">
import {
	WireGuardIpStack,
	WireGuardTunnelConfig,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IDataTableAction,
	IModalWindow,
	INumberInput,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiClipboard, mdiContentSave, mdiUploadOutline } from '@mdi/js';
import {
	computed,
	ref,
	type Ref,
	type TemplateRef,
	useTemplateRef,
	watch,
} from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import WireGuardGenerateKeyDialog from '@/components/ip-network/wireGuard/WireGuardGenerateKeyDialog.vue';
import WireGuardIpConfig from '@/components/ip-network/wireGuard/WireGuardIpConfig.vue';
import WireGuardIpStackSelect from '@/components/ip-network/wireGuard/WireGuardIpStackSelect.vue';
import { useApiClient } from '@/services/ApiClient';

/// Define props
const componentProps = withDefaults(
	defineProps<{
		/// Action type (add/edit)
		action: Action.Add | Action.Edit;
		/// Tunnel ID for edit action
		tunnelId?: number | null;
	}>(),
	{
		tunnelId: null,
	},
);

const emit = defineEmits<{
	updateTunnel: [
		tunnel: WireGuardTunnelConfig,
		enabled: boolean,
		active: boolean,
	];
}>();

/// WireGuard API service
const service = useApiClient().getNetworkServices().getWireGuardService();
/// Dialog visibility
const showDialog: Ref<boolean> = ref(false);
/// Form instances
const ipv4config = useTemplateRef<InstanceType<typeof WireGuardIpConfig>>('ipv4config');
const ipv6config = useTemplateRef<InstanceType<typeof WireGuardIpConfig>>('ipv6config');
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
/// WireGuard tunnel configuration
const wgConfig: Ref<WireGuardTunnelConfig> = ref(getDefaultConfig());
/// Internationalization instance
const i18n = useI18n();
/// Component state
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
/// Allow editing of key pair
const editKeys: Ref<boolean> = ref(false);
/// Shows private key field switch
const showPrivateKey: Ref<boolean> = computed(
	() => !!wgConfig.value.privateKey ||
		editKeys.value ||
		componentProps.action === Action.Add,
);
/// Show private key warning when the key is displayed and present
const showPrivateKeyWarning: Ref<boolean> = computed(
	() => !!wgConfig.value.privateKey && showPrivateKey.value,
);
/// Generate new key dialog window instance
const generateKeyDialogInstance: TemplateRef<InstanceType<typeof WireGuardGenerateKeyDialog>> = useTemplateRef('generateKeyDialogInstance');

/**
 * Closes the dialog window
 */
function closeDialog(): void {
	showDialog.value = false;
}

/**
 * Verifies and saves configuration changes.
 * @param {boolean} enable enable the service when saved (includes activate)
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
		componentState.value = ComponentState.Action;
		if (componentProps.action === Action.Add) {
			response = await service.createTunnel(payload);
		} else {
			response = await service.updateTunnel(wgConfig.value.id!, payload);
		}
		if (enable) {
			service.enableTunnel(response.id!);
		}
		if (activate) {
			service.activateTunnel(response.id!);
		}
		if (componentProps.action === Action.Add) {
			toast.success(
				i18n.t('components.ipNetwork.wireGuard.tunnels.add.messages.success'),
			);
		} else {
			toast.success(
				i18n.t('components.ipNetwork.wireGuard.tunnels.update.messages.success'),
			);
		}
		emit('updateTunnel', response, enable, activate);
		closeDialog();
	} catch {
		componentState.value = ComponentState.Error;
		if (componentProps.action === Action.Add) {
			toast.error(
				i18n.t('components.ipNetwork.wireGuard.tunnels.add.messages.failure'),
			);
		} else {
			toast.error(
				i18n.t('components.ipNetwork.wireGuard.tunnels.update.messages.failure'),
			);
		}
	}
}

/**
 * Copy field value to clipboard
 * @param {string} value Value to copy to clipboard.
 */
function copyToClipboard(value: string | undefined): void {
	if (value === undefined || value === '') {
		return;
	}
	navigator.clipboard.writeText(value);
	toast.success(
		i18n.t('components.ipNetwork.wireGuard.tunnels.configuration.form.copyKey'),
	);
}

/**
 * Generate key
 */
async function generateKey(): Promise<void> {
	let response = null;
	try {
		componentState.value = ComponentState.Action;
		response = await service.generateKeyPair();
		generateKeyDialogInstance.value?.close();
		wgConfig.value.privateKey = response.privateKey;
		wgConfig.value.publicKey = response.publicKey;
		componentState.value = ComponentState.Ready;
	} catch {
		componentState.value = ComponentState.Error;
		toast.error(
			i18n.t(
				'components.ipNetwork.wireGuard.tunnels.configuration.form.genKeyPair.error',
			),
		);
	}
}

/**
 * Reset form validation for unused IP stack.
 */
watch(
	() => wgConfig.value.stack,
	(stack) => {
		if (stack === WireGuardIpStack.IPV4) {
			ipv6config.value?.resetValidation();
		}
		if (stack === WireGuardIpStack.IPV6) {
			ipv4config.value?.resetValidation();
		}
	},
);

/**
 * Setup the form when opened.
 */
watch(
	showDialog,
	async (dialogShown) => {
		// if closed, do nothing
		if (!dialogShown) {
			return;
		}
		componentState.value = ComponentState.Loading;
		// reset form data to default if not loading existing tunnel
		if (!componentProps.tunnelId) {
			wgConfig.value = getDefaultConfig();
			editKeys.value = false;
			componentState.value = ComponentState.Ready;
			return;
		}
		// fetch tunnel config
		try {
			wgConfig.value = await service.getTunnel(componentProps.tunnelId);
			componentState.value = ComponentState.Ready;
			// Add default value for unused stack
			if (!wgConfig.value.ipv6) {
				wgConfig.value.ipv6 = { address: '', prefix: 48 };
			}
			if (!wgConfig.value.ipv4) {
				wgConfig.value.ipv4 = { address: '', prefix: 24 };
			}
		} catch {
			componentState.value = ComponentState.FetchFailed;
		}
	},
);
</script>
