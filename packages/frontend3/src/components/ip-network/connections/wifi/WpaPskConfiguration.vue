<template>
	<div v-if='configuration.wifi!.security.type === WifiSecurityType.WPA_PSK'>
		<PasswordInput
			v-model='configuration.wifi!.security.psk'
			:label='$t("components.ipNetwork.connections.fields.wifi.security.psk").toString()'
			:rules='[
				(v: string|null) => ValidationRules.required(v, $t("components.ipNetwork.connections.validations.wifi.security.psk.required")),
				(v: string|null) => validatePsk(v, $t("components.ipNetwork.connections.validations.wifi.security.psk.invalid")),
			]'
			required
			:prepend-inner-icon='mdiKey'
		/>
	</div>
</template>
<script setup lang="ts">
import {
	type NetworkConnectionConfiguration,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network/NetworkConnection';
import {
	WifiSecurityType,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network/Wifi';
import { mdiKey } from '@mdi/js';
import { type PropType } from 'vue';

import PasswordInput from '@/components/PasswordInput.vue';
import ValidationRules from '@/helpers/ValidationRules';

/// Connection configuration
const configuration = defineModel({
	type: Object as PropType<NetworkConnectionConfiguration>,
	required: true,
});

/**
 * Validate WPA pre-shared key
 * @param {string|null} value Value to validate
 * @param {string} error Error message
 * @return {boolean|string} Validation result
 */
function validatePsk(value: string|null, error: string): boolean | string {
	const regex = /^([\u0020-\u007e\u0080-\u00ff]{8,63}|[\da-fA-F]{64})$/;
	return (value !== null && regex.test(value)) ? true : error;
}
</script>
