<template>
	<v-form
		v-if='configuration !== null'
		ref='form'
		v-slot='{ isValid }'
		:disabled='[ComponentState.Reloading, ComponentState.Saving].includes(componentState)'
	>
		<Card :header-color='actionColor'>
			<template #title>
				<span v-if='action === FormAction.Add'>
					{{ $t(`components.ipNetwork.connections.add.titles.${configuration.type}`) }}
				</span>
				<span v-else-if='action === FormAction.Edit'>
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
				<v-btn
					:color='actionColor'
					variant='elevated'
					:disabled='!isValid || componentState === ComponentState.Saving'
					@click='saveConfiguration()'
				>
					<v-icon :icon='actionIcon' />
					{{ $t(`common.buttons.${action}`) }}
				</v-btn>
			</template>
		</Card>
	</v-form>
</template>
<script setup lang='ts'>
import {
	type NetworkConnectionConfiguration,
	NetworkConnectionType,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network/NetworkConnection';
import {
	type NetworkInterfaceType,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network/NetworkInterface';
import { IpNetworkUtils } from '@iqrf/iqrf-gateway-webapp-client/utils';
import { mdiContentSave, mdiHelpCircle, mdiPlus, mdiTextShort } from '@mdi/js';
import { computed, onBeforeMount, type PropType, ref, type Ref } from 'vue';
import { useRouter } from 'vue-router';
import { VForm, VIcon } from 'vuetify/components';

import Card from '@/components/Card.vue';
import IpConfiguration
	from '@/components/ip-network/connections/ip/IpConfiguration.vue';
import VlanConfiguration
	from '@/components/ip-network/connections/vlan/VlanConfiguration.vue';
import WiFiConfiguration
	from '@/components/ip-network/connections/wifi/WiFiConfiguration.vue';
import InterfaceInput
	from '@/components/ip-network/interfaces/InterfaceInput.vue';
import TextInput from '@/components/TextInput.vue';
import { FormAction } from '@/enums/controls';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

/// Form action
const action: Ref<FormAction> = computed((): FormAction => {
	if (componentProps.uuid === null) {
		return FormAction.Add;
	}
	return FormAction.Edit;
});
/// Form action color
const actionColor: Ref<string> = computed((): string => {
	switch (action.value) {
		case FormAction.Add:
			return 'success';
		case FormAction.Edit:
			return 'primary';
		default:
			return 'grey';
	}
});
/// Form action icon
const actionIcon: Ref<string> = computed((): string => {
	switch (action.value) {
		case FormAction.Add:
			return mdiPlus;
		case FormAction.Edit:
			return mdiContentSave;
		default:
			return mdiHelpCircle;
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
function fetchConfiguration(): void {
	if (action.value === FormAction.Add || componentProps.uuid === null) {
		return;
	}
	componentState.value = ComponentState.Loading;
	service.fetch(componentProps.uuid)
		.then((connection: NetworkConnectionConfiguration): NetworkConnectionConfiguration => {
			configuration.value = connection;
			return connection;
		});
}

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

function saveConfiguration(): void {
	redirect();
}

onBeforeMount((): void => {
	fetchConfiguration();
});
</script>
