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
