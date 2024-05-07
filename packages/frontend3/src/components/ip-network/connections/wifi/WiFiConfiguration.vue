<template>
	<h2 class='mb-3 text-h6'>
		{{ $t("components.ipNetwork.connections.fields.wifi.title") }}
	</h2>
	<TextInput
		v-model='configuration.wifi!.ssid'
		:label='$t("components.ipNetwork.connections.fields.wifi.ssid").toString()'
		:rules='[
			(v: string|null) => ValidationRules.required(v, $t("components.ipNetwork.connections.validations.wifi.ssid.required")),
		]'
		required
		:prepend-inner-icon='mdiWifi'
	/>
	<WiFiSecurityTypeInput v-model='configuration.wifi!.security.type' disabled />
	<WpaPskConfiguration v-model='configuration' />
</template>

<script setup lang='ts'>
import {
	type NetworkConnectionConfiguration,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network/NetworkConnection';
import { mdiWifi } from '@mdi/js';
import { type PropType } from 'vue';

import WiFiSecurityTypeInput
	from '@/components/ip-network/connections/wifi/WiFiSecurityTypeInput.vue';
import WpaPskConfiguration
	from '@/components/ip-network/connections/wifi/WpaPskConfiguration.vue';
import TextInput from '@/components/TextInput.vue';
import ValidationRules from '@/helpers/ValidationRules';

const configuration = defineModel({
	type: Object as PropType<NetworkConnectionConfiguration>,
	required: true,
});
</script>
