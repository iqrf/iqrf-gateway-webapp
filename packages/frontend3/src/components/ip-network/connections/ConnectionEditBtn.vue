<template>
	<v-tooltip
		:activator='activator'
		location='bottom'
	>
		{{ $t("components.ipNetwork.connections.actions.edit") }}
	</v-tooltip>
	<router-link :to>
		<v-icon
			ref='activator'
			color='info'
			size='large'
			:icon='mdiPencil'
		/>
	</router-link>
</template>

<script setup lang='ts'>
import {
	type NetworkConnectionListEntry, NetworkConnectionType,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network/NetworkConnection';
import { mdiPencil } from '@mdi/js';
import { computed, type PropType, ref, type Ref } from 'vue';
import { VIcon } from 'vuetify/components';


/// Component props
const componentProps = defineProps({
	connection: {
		type: Object as PropType<NetworkConnectionListEntry>,
		required: true,
	},
});
/// Activator ref
const activator: Ref<typeof VIcon | null> = ref(null);
/// Edit connection page URL
const to: Ref<string> = computed((): string => {
	switch (componentProps.connection.type) {
		case NetworkConnectionType.Ethernet:
			return `/ip-network/ethernet/edit/${componentProps.connection.uuid}`;
		case NetworkConnectionType.WiFi:
			return `/ip-network/wireless/edit/${componentProps.connection.uuid}`;
		case NetworkConnectionType.GSM:
			return `/ip-network/mobile/edit/${componentProps.connection.uuid}`;
		case NetworkConnectionType.VLAN:
			return `/ip-network/vlan/edit/${componentProps.connection.uuid}`;
		default:
			return '/ip-network/';
	}
});
</script>
