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
				:tooltip='$t("components.ipNetwork.wireGuard.peers.configuration.form.addPeer")'
				v-bind='props'
			/>
			<IDataTableAction
				v-else-if='componentProps.action === Action.Edit'
				:action='Action.Edit'
				:tooltip='$t("components.ipNetwork.wireGuard.peers.configuration.form.editPeer")'
				v-bind='props'
			/>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			:disabled='[ComponentState.Reloading, ComponentState.Action].includes(componentState)'
			@submit.prevent='onSubmit()'
		>
			<ICard>
				<template #title>
					<span>{{ $t("components.ipNetwork.wireGuard.peers.configuration.title") }}</span>
				</template>
				<v-alert
					v-if='componentState === ComponentState.FetchFailed'
					type='error'
					variant='tonal'
					:text='$t("common.messages.fetchFailed")'
				/>
				<v-alert
					v-if='componentState === ComponentState.NotFound'
					type='error'
					variant='tonal'
					:text='$t("components.ipNetwork.wireGuard.peers.configuration.noTunnelErrorMessage")'
				/>
				<v-skeleton-loader
					v-if='componentState !== ComponentState.NotFound'
					class='input-skeleton-loader'
					:loading='componentState === ComponentState.Loading'
					type='heading@8, button'
				>
					<ITextInput
						v-model='peerConfig.publicKey'
						:label='$t("components.ipNetwork.wireGuard.peers.configuration.form.publicKey")'
						:rules='[
							(v: string | null) => ValidationRules.required(
								v,
								$t("components.ipNetwork.wireGuard.peers.configuration.validation.publicKey"),
							),
						]'
						required
					/>
					<ITextInput
						v-model='peerConfig.psk'
						:label='$t("components.ipNetwork.wireGuard.peers.configuration.form.psk")'
					/>
					<INumberInput
						v-model='peerConfig.keepalive'
						:label='$t("components.ipNetwork.wireGuard.peers.configuration.form.keepalive")'
						:rules='[
							(v: number | null) => ValidationRules.required(
								v,
								$t("components.ipNetwork.wireGuard.peers.configuration.validation.keepalive"),
							),
						]'
					/>
					<ITextInput
						v-model='peerConfig.endpoint'
						:label='$t("components.ipNetwork.wireGuard.peers.configuration.form.endpoint")'
						:rules='[
							(v: string | null) => ValidationRules.required(
								v,
								$t("components.ipNetwork.wireGuard.peers.configuration.validation.endpoint"),
							),
						]'
					/>
					<INumberInput
						v-model='peerConfig.port'
						:label='$t("components.ipNetwork.wireGuard.peers.configuration.form.port")'
						:rules='[
							(v: number | null) => ValidationRules.required(
								v,
								$t("components.ipNetwork.wireGuard.peers.configuration.validation.portRequired"),
							),
							(v: number) => ValidationRules.between(
								v,
								0,
								65535,
								$t("components.ipNetwork.wireGuard.peers.configuration.validation.portNumber"),
							),
						]'
					/>
					<v-select
						v-model='peerConfig.tunnelId'
						:label='$t("components.ipNetwork.wireGuard.peers.configuration.form.tunnel")'
						:items='tunnels'
						item-title='name'
						item-value='id'
						required
					/>
					<div v-if='tunnelStack !== WireGuardIpStack.IPV6'>
						<h2 class='mb-3 text-h6'>
							{{ $t("components.ipNetwork.wireGuard.peers.configuration.form.allowedIps.ipv4title") }}
						</h2>
						<v-container>
							<v-row
								v-for='(addr, index) in peerConfig.allowedIPs.ipv4'
								:key='index'
								align='center'
							>
								<v-col>
									<WireGuardIpConfig
										:record-id='index'
										:ip-address='addr'
										:type='WireGuardIpStack.IPV4'
										@update-ip='updateAllowedIp'
									/>
								</v-col>
								<v-col cols='auto'>
									<IActionBtn
										:text='$t("components.ipNetwork.wireGuard.peers.configuration.form.allowedIps.remove")'
										:action='Action.Delete'
										@click='removeAllowedAddress(WireGuardIpStack.IPV4, index)'
									/>
								</v-col>
							</v-row>
							<v-row>
								<IActionBtn
									:text='$t("components.ipNetwork.wireGuard.peers.configuration.form.allowedIps.addIPv4")'
									:action='Action.Add'
									class='mb-5'
									@click='addAllowedAddress(WireGuardIpStack.IPV4)'
								/>
							</v-row>
						</v-container>
					</div>
					<div v-if='tunnelStack !== WireGuardIpStack.IPV4'>
						<h2 class='mb-3 text-h6'>
							{{ $t("components.ipNetwork.wireGuard.peers.configuration.form.allowedIps.ipv6title") }}
						</h2>
						<v-container>
							<v-row
								v-for='(addr, index) in peerConfig.allowedIPs.ipv6'
								:key='index'
								align='center'
							>
								<v-col>
									<WireGuardIpConfig
										:record-id='index'
										:ip-address='addr'
										:type='WireGuardIpStack.IPV6'
										@update-ip='updateAllowedIp'
									/>
								</v-col>
								<v-col class='auto'>
									<IActionBtn
										:text='$t("components.ipNetwork.wireGuard.peers.configuration.form.allowedIps.remove")'
										:action='Action.Delete'
										@click='removeAllowedAddress(WireGuardIpStack.IPV6, index)'
									/>
								</v-col>
							</v-row>
							<v-row>
								<IActionBtn
									:text='$t("components.ipNetwork.wireGuard.peers.configuration.form.allowedIps.addIPv6")'
									:action='Action.Add'
									class='mb-5'
									@click='addAllowedAddress(WireGuardIpStack.IPV6)'
								/>
							</v-row>
						</v-container>
					</div>
				</v-skeleton-loader>
				<template #actions>
					<IActionBtn
						v-if='![ComponentState.Error || ComponentState.FetchFailed || ComponentState.NotFound].includes(componentState)'
						:action='Action.Save'
						container-type='card'
						:loading='componentState === ComponentState.Loading'
						:disabled='!isValid.value'
						type='submit'
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
import { WireGuardIpAddress, WireGuardIpStack, WireGuardPeer, WireGuardTunnelListEntry } from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import { Action, ComponentState, IActionBtn, ICard, IDataTableAction, IModalWindow, INumberInput, ITextInput, ValidationRules } from '@iqrf/iqrf-vue-ui';
import { computed, onMounted, ref, type Ref, type TemplateRef, useTemplateRef, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { type VForm } from 'vuetify/components';

import { useApiClient } from '@/services/ApiClient';

import WireGuardIpConfig from './WireGuardIpConfig.vue';

/// Define props
const componentProps = withDefaults(
	defineProps<{
		/// Action type (add/edit)
		action: Action.Add | Action.Edit;
		/// WireGuard peer ID (for edit action)
		peerId?: number | null;
		/// List of available WireGuard tunnels
		tunnels: WireGuardTunnelListEntry[];
	}>(),
	{
		peerId: null,
	},
);

const emit = defineEmits<{
	updatePeer: [peer: WireGuardPeer];
}>();

/// WireGuard API service
const service = useApiClient().getNetworkServices().getWireGuardService();
/// Dialog visibility
const showDialog: Ref<boolean> = ref(false);
/// Form instance
const form: TemplateRef<VForm> = useTemplateRef('form');
/// Returns default WireGuard peer configuration
const getDefaultConfig = (): WireGuardPeer => ({
	publicKey: '',
	psk: '',
	keepalive: 25,
	endpoint: '',
	port: 51_820,
	allowedIPs: {
		ipv4: [],
		ipv6: [],
	},
	tunnelId: componentProps.tunnels.at(0)?.id ?? 0,
});
/// WireGuard tunnel configuration
const peerConfig: Ref<WireGuardPeer> = ref(getDefaultConfig());
/// Internationalization instance
const i18n = useI18n();
/// Component state
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
/// Tunnel IP stack
const tunnelStack: Ref<WireGuardIpStack> = computed(() => {
	const tunnel = componentProps.tunnels.find((t) => t.id === peerConfig.value.tunnelId);
	return tunnel?.stack ?? WireGuardIpStack.DUAL;
});

/**
 * Closes the dialog window
 */
function closeDialog(): void {
	showDialog.value = false;
}

/**
 * Adds another record to the allowed IP address list
 * @param {WireGuardIpStack} type Type of address to add (allowed are WireGuardIpStack.IPV4 and WireGuardIpStack.IPV6)
 */
function addAllowedAddress(type: WireGuardIpStack): void {
	if (type === WireGuardIpStack.IPV4) {
		peerConfig.value.allowedIPs.ipv4.push({ address: '', prefix: 24 });
	} else if (type === WireGuardIpStack.IPV6) {
		peerConfig.value.allowedIPs.ipv6.push({ address: '', prefix: 48 });
	} else {
		throw new TypeError('Only WireGuardIpStack.IPV4 and WireGuardIpStack.IPV6 are allowed as type values!');
	}
}

/**
 * Removes address record on given index in corresponding allowed IP address list
 * @param {WireGuardIpStack} type Type of removed address (allowed are WireGuardIpStack.IPV4 and WireGuardIpStack.IPV6)
 * @param {number} index Index of removed address in the list of allowed IPs
 */
function removeAllowedAddress(type: WireGuardIpStack, index: number): void {
	if (type === WireGuardIpStack.IPV4) {
		peerConfig.value.allowedIPs.ipv4.splice(index, 1);
	} else if (type === WireGuardIpStack.IPV6) {
		peerConfig.value.allowedIPs.ipv6.splice(index, 1);
	} else {
		throw new TypeError('Only WireGuardIpStack.IPV4 and WireGuardIpStack.IPV6 are allowed as type values!');
	}
}

/**
 * Updates IP from the list of allowed IPs.
 * @param {string|number} index index of the record in the array
 * @param {WireGuardIpAddress} value IP address instance
 * @param {WireGuardIpStack} type IP stack type (IPv4 or IPv6) the record is part of
 */
function updateAllowedIp(index: string | number, value: WireGuardIpAddress, type: WireGuardIpStack): void {
	if (typeof index === 'string') {
		index = Number.parseInt(index);
	}
	if (type === WireGuardIpStack.IPV4) {
		peerConfig.value.allowedIPs.ipv4[index].address = value.address;
		peerConfig.value.allowedIPs.ipv4[index].prefix = value.prefix;
	} else if (WireGuardIpStack.IPV6) {
		peerConfig.value.allowedIPs.ipv6[index].address = value.address;
		peerConfig.value.allowedIPs.ipv6[index].prefix = value.prefix;
	} else {
		throw new TypeError('Only WireGuardIpStack.IPV4 and WireGuardIpStack.IPV6 are allowed as type values!');
	}
}

/**
 * Fetches WireGuard peer configuration
 * @param {number} id - id of record to fetch
 */
async function fetchConfig(id: number): Promise<void> {
	try {
		peerConfig.value = await service.getPeer(id);
	} catch {
		componentState.value = ComponentState.FetchFailed;
	}
}

/**
 * Verifies and saves WireGuard peer configuration changes
 */
async function onSubmit(): Promise<void> {
	let response = null;
	try {
		if (componentProps.action === Action.Add) {
			response = await service.createPeer(peerConfig.value);
			toast.success(i18n.t('components.ipNetwork.wireGuard.peers.add.messages.success'));
		} else {
			response = await service.updatePeer(peerConfig.value.id!, peerConfig.value);
			toast.success(i18n.t('components.ipNetwork.wireGuard.peers.update.messages.success'));
		}
		emit('updatePeer', response);
		componentState.value = ComponentState.Ready;
		if (componentProps.action === Action.Add) {
			resetForm();
		}
		closeDialog();
	} catch {
		componentState.value = ComponentState.Error;
		if (componentProps.action === Action.Add) {
			toast.error(i18n.t('components.ipNetwork.wireGuard.peers.add.messages.failure'));
		} else {
			toast.error(i18n.t('components.ipNetwork.wireGuard.peers.update.messages.failure'));
		}
	}
}

/**
 * Watch for change of tunnels.
 * This is necessary for updating the form when rendered before tunnels are fetched.
 */
watch(
	() => componentProps.tunnels,
	(tunnelList) => {
		if (tunnelList.length === 0) {
			componentState.value = ComponentState.NotFound;
			return;
		}
		if (componentProps.peerId) {
			if (componentState.value !== ComponentState.FetchFailed) {
				componentState.value = ComponentState.Ready;
			}
			return;
		}

		if (!peerConfig.value.tunnelId) {
			peerConfig.value.tunnelId = tunnelList[0].id;
		}
		componentState.value = ComponentState.Ready;
	},
	{ immediate: true },
);

/**
 * Resets form back to default values
 */
function resetForm(): void {
	peerConfig.value = getDefaultConfig();
	componentState.value = ComponentState.Created;
	form.value?.resetValidation();
}

onMounted((): void => {
	if (componentProps.peerId) {
		fetchConfig(componentProps.peerId);
	}
});
</script>
