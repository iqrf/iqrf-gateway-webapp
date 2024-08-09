<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
	<v-form
		v-if='configuration !== null'
		ref='form'
		v-slot='{ isValid }'
		:disabled='[ComponentState.Reloading, ComponentState.Saving].includes(componentState)'
	>
		<Card :header-color='actionColor'>
			<template #title>
				<span v-if='action === Action.Add'>
					{{ $t(`components.ipNetwork.connections.add.titles.${configuration.type}`) }}
				</span>
				<span v-else-if='action === Action.Edit'>
					{{ $t(`components.ipNetwork.connections.edit.titles.${configuration.type}`) }}
				</span>
			</template>
			<TextInput
				v-model='configuration.name'
				:label='$t("components.ipNetwork.connections.fields.generic.name").toString()'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("components.ipNetwork.connections.validations.generic.name.required")),
				]'
				required
				:prepend-inner-icon='mdiTextShort'
			/>
			<v-switch
				v-model='configuration.autoConnect.enabled'
				:label='$t("components.ipNetwork.connections.fields.autoConnect.enabled").toString()'
				color='primary'
				inset
				density='compact'
			/>
			<InterfaceInput
				v-if='[NetworkConnectionType.Ethernet, NetworkConnectionType.WiFi].includes(configuration.type as NetworkConnectionType)'
				v-model='configuration.interface'
				:type='interfaceType'
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
			<template #actions>
				<CardActionBtn
					:action='action'
					:disabled='!isValid.value || componentState === ComponentState.Saving'
					@click='saveConfiguration()'
				/>
			</template>
		</Card>
	</v-form>
</template>
<script setup lang='ts'>
import {
	type NetworkConnectionConfiguration,
	NetworkConnectionType,
	type NetworkInterfaceType,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import { IpNetworkUtils } from '@iqrf/iqrf-gateway-webapp-client/utils';
import { mdiTextShort } from '@mdi/js';
import { computed, onBeforeMount, type PropType, ref, type Ref } from 'vue';
import { useRouter } from 'vue-router';
import { VForm } from 'vuetify/components';

import IpConfiguration
	from '@/components/ip-network/connections/ip/IpConfiguration.vue';
import VlanConfiguration
	from '@/components/ip-network/connections/vlan/VlanConfiguration.vue';
import WiFiConfiguration
	from '@/components/ip-network/connections/wifi/WiFiConfiguration.vue';
import InterfaceInput
	from '@/components/ip-network/interfaces/InterfaceInput.vue';
import Card from '@/components/layout/card/Card.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import TextInput from '@/components/layout/form/TextInput.vue';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { Action } from '@/types/Action';
import { ComponentState } from '@/types/ComponentState';

/// Form action
const action: Ref<Action> = computed((): Action => {
	if (componentProps.uuid === null) {
		return Action.Add;
	}
	return Action.Edit;
});
/// Form action color
const actionColor: Ref<string> = computed((): string => {
	switch (action.value) {
		case Action.Add:
			return 'success';
		case Action.Edit:
			return 'primary';
		default:
			return 'grey';
	}
});
/// Connection configuration
const configuration: Ref<NetworkConnectionConfiguration | null> = ref(null);
/// Component state
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
/// Define props
const componentProps = defineProps({
	uuid: {
		type: [String, null] as PropType<string | null>,
		required: false,
		default: null,
	},
});
/// Interface type
const interfaceType: Ref<NetworkInterfaceType | null> = computed((): NetworkInterfaceType | null => {
	return IpNetworkUtils.connectionTypeToInterfaceType(configuration.value?.type ?? null);
});
/// Router
const router = useRouter();
/// Network connection service
const service = useApiClient().getNetworkServices().getNetworkConnectionService();

/**
 * Fetches network connection configuration
 */
async function fetchConfiguration(): Promise<void> {
	if (action.value === Action.Add || componentProps.uuid === null) {
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
 * Redirects to the correct network connection configuration page
 */
function redirect(): void {
	switch (configuration.value?.type) {
		case NetworkConnectionType.Ethernet:
			router.push('/ip-network/ethernet');
			break;
		case NetworkConnectionType.GSM:
			router.push('/ip-network/mobile');
			break;
		case NetworkConnectionType.VLAN:
			router.push('/ip-network/vlan');
			break;
		case NetworkConnectionType.WiFi:
			router.push('/ip-network/wireless');
			break;
	}
}

/**
 * Saves the network connection configuration
 */
function saveConfiguration(): void {
	redirect();
}

onBeforeMount(async (): Promise<void> => await fetchConfiguration());
</script>
