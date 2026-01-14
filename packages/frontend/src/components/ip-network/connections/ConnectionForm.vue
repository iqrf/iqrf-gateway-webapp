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
				:tooltip='$t("components.ipNetwork.connections.actions.add")'
				v-bind='props'
			/>
			<IDataTableAction
				v-else-if='componentProps.action === Action.Edit'
				:action='Action.Edit'
				:tooltip='$t("components.ipNetwork.connections.actions.edit")'
				v-bind='props'
			/>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			:disabled='[ComponentState.Reloading, ComponentState.Action].includes(componentState)'
			@submit.prevent='onSubmit(false)'
		>
			<ICard>
				<template #title>
					<span v-if='componentProps.action === Action.Add'>
						{{ $t(`components.ipNetwork.connections.add.titles.${componentProps.type}`) }}
					</span>
					<span v-else-if='componentProps.action === Action.Edit'>
						{{ $t(`components.ipNetwork.connections.edit.titles.${componentProps.type}`) }}
					</span>
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
					<v-responsive v-if='configuration !== null'>
						<ITextInput
							v-model='configuration.name'
							:label='$t("components.ipNetwork.connections.form.generic.name")'
							:rules='[
								(v: string|null) => ValidationRules.required(v, $t("components.ipNetwork.connections.errors.generic.name.required")),
							]'
							required
							:prepend-inner-icon='mdiTextShort'
						/>
						<v-switch
							v-model='configuration.autoConnect.enabled'
							:label='$t("components.ipNetwork.connections.form.autoConnect.enabled")'
							color='primary'
						/>
						<InterfaceInput
							v-if='[NetworkConnectionType.Ethernet, NetworkConnectionType.WiFi].includes(configuration.type!) && configuration.interface !== undefined'
							v-model='configuration.interface'
							:type='interfaceType'
						/>
						<ModemInput
							v-if='NetworkConnectionType.GSM === configuration.type && configuration.interface !== undefined'
							v-model='configuration.interface'
						/>
						<SerialConfiguration
							v-if='configuration.serial'
							v-model='configuration'
						/>
						<MobileConfiguration
							v-if='configuration.type === NetworkConnectionType.GSM'
							v-model='configuration'
						/>
						<VlanConfiguration
							v-if='configuration.type === NetworkConnectionType.VLAN'
							v-model='configuration'
						/>
						<WiFiConfiguration
							v-if='configuration.type === NetworkConnectionType.WiFi'
							v-model='configuration'
						/>
						<IpConfiguration
							v-if='configuration.type !== NetworkConnectionType.GSM'
							v-model='configuration'
						/>
					</v-responsive>
				</v-skeleton-loader>
				<template #actions>
					<IActionBtn
						v-if='![ComponentState.Error || ComponentState.FetchFailed].includes(componentState)'
						:action='Action.Save'
						container-type='card'
						:loading='componentState === ComponentState.Action'
						:disabled='!isValid.value'
						type='submit'
					/>
					<IActionBtn
						v-if='![ComponentState.Error || ComponentState.FetchFailed].includes(componentState)'
						color='info'
						container-type='card'
						:disabled='!isValid.value'
						:icon='mdiContentSave'
						:loading='componentState === ComponentState.Action'
						:text='$t("components.ipNetwork.connections.actions.saveAndConnect")'
						@click='onSubmit(true)'
					/>
					<v-spacer />
					<IActionBtn
						:action='Action.Close'
						container-type='card'
						:disabled='componentState === ComponentState.Action'
						@click='close()'
					/>
				</template>
			</ICard>
		</v-form>
	</IModalWindow>
</template>

<script setup lang='ts'>
import {
	IPv4ConfigurationMethod,
	IPv6ConfigurationMethod,
	type NetworkConnectionConfiguration,
	NetworkConnectionType,
	type NetworkInterfaceType,
	WifiMode,
	WifiSecurityType,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import { IpNetworkUtils } from '@iqrf/iqrf-gateway-webapp-client/utils';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IDataTableAction,
	IModalWindow,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiContentSave, mdiTextShort } from '@mdi/js';
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
import { type VForm } from 'vuetify/components';

import IpConfiguration
	from '@/components/ip-network/connections/ip/IpConfiguration.vue';
import MobileConfiguration
	from '@/components/ip-network/connections/mobile/MobileConfiguration.vue';
import ModemInput
	from '@/components/ip-network/connections/mobile/ModemInput.vue';
import SerialConfiguration
	from '@/components/ip-network/connections/mobile/SerialConfiguration.vue';
import VlanConfiguration
	from '@/components/ip-network/connections/vlan/VlanConfiguration.vue';
import WiFiConfiguration
	from '@/components/ip-network/connections/wifi/WiFiConfiguration.vue';
import InterfaceInput
	from '@/components/ip-network/interfaces/InterfaceInput.vue';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

/// Define props
const componentProps = withDefaults(
	defineProps<{
		/// Action type - add or edit
		action: Action.Add | Action.Edit;
		/// Network connection type
		type?: NetworkConnectionType | null;
		/// Network connection UUID
		uuid?: string | null;
	}>(),
	{
		type: null,
		uuid: null,
	},
);
/// Define emits
const emit = defineEmits<{
	change: [];
}>();
/// Connection configuration
const configuration: Ref<NetworkConnectionConfiguration | null> = ref(null);
/// Component state
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
/// Interface type
const interfaceType: Ref<NetworkInterfaceType | null> = computed((): NetworkInterfaceType | null => {
	return IpNetworkUtils.connectionTypeToInterfaceType(configuration.value?.type ?? null);
});
/// Form reference
const form: TemplateRef<VForm> = useTemplateRef('form');
/// Dialog visibility
const showDialog: Ref<boolean> = ref(false);
/// Internationalization instance
const i18n = useI18n();
/// Network connection service
const service = useApiClient().getNetworkServices().getNetworkConnectionService();

/**
 * Fetches network connection configuration
 */
async function fetchConfiguration(): Promise<void> {
	if (componentProps.action === Action.Add || componentProps.uuid === null) {
		configuration.value = {
			autoConnect: {
				enabled: true,
				priority: 0,
				retries: -1,
			},
			interface: '',
			name: '',
			type: componentProps.type!,
			ipv4: {
				method: IPv4ConfigurationMethod.AUTO,
				addresses: [],
				dns: [],
				gateway: '',
			},
			ipv6: {
				method: IPv6ConfigurationMethod.AUTO,
				addresses: [],
				dns: [],
				gateway: '',
			},
			...componentProps.type === NetworkConnectionType.GSM ? {
				gsm: {
					apn: '',
					pin: '',
					username: '',
					password: '',
				},
			} : {},
			...componentProps.type === NetworkConnectionType.VLAN ? {
				vlan: {
					flags: {
						gvrp: false,
						looseBinding: false,
						mvrp: false,
						reorderHeaders: true,
					},
					id: 1,
					parentInterface: '',
				},
			} : {},
			...componentProps.type === NetworkConnectionType.WiFi ? {
				wifi: {
					mode: WifiMode.Infrastructure,
					security: {
						type: WifiSecurityType.WPA_PSK,
					},
					ssid: '',
				},
			} : {},
		};
		componentState.value = ComponentState.Ready;
		return;
	}
	componentState.value = ComponentState.Loading;
	try {
		configuration.value = await service.get(componentProps.uuid);
		componentState.value = ComponentState.Ready;
	} catch {
		componentState.value = ComponentState.FetchFailed;
	}
}

/**
 * Closes the dialog window
 */
function close(): void {
	showDialog.value = false;
}

/**
 * Saves the network connection configuration
 * @param {boolean} connect Connect the network connection
 */
async function onSubmit(connect: boolean): Promise<void> {
	if (!await validateForm(form.value) || configuration.value === null) {
		return;
	}
	componentState.value = ComponentState.Action;
	const translationParams = { name: configuration.value.name };
	try {
		let uuid: string;
		if (componentProps.action === Action.Add) {
			uuid = (await service.create(configuration.value!)).uuid;
		} else {
			uuid = componentProps.uuid!;
			await service.update(uuid, configuration.value);
		}
		if (connect) {
			try {
				await service.connect(uuid);
				toast.success(i18n.t('components.ipNetwork.connections.connect.messages.success', translationParams));
			} catch {
				toast.error(i18n.t('components.ipNetwork.connections.connect.messages.failure', translationParams));
			}
		}
		if (componentProps.action === Action.Add) {
			toast.success(i18n.t('components.ipNetwork.connections.add.messages.success', translationParams));
		} else {
			toast.success(i18n.t('components.ipNetwork.connections.edit.messages.success', translationParams));
		}
		emit('change');
		componentState.value = ComponentState.Ready;
		close();
	} catch {
		componentState.value = ComponentState.Error;
		if (componentProps.action === Action.Add) {
			toast.error(i18n.t('components.ipNetwork.connections.add.messages.failure', translationParams));
		} else {
			toast.error(i18n.t('components.ipNetwork.connections.edit.messages.failure', translationParams));
		}
	}
}

watch(showDialog, async (value: boolean) => {
	if (value) {
		await fetchConfiguration();
	}
});
</script>
