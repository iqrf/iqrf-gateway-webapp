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
	WifiSecurityType,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import { mdiKey } from '@mdi/js';
import { type PropType } from 'vue';

import PasswordInput from '@/components/layout/form/PasswordInput.vue';
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
	const regex = /^([\u0020-\u007e\u0080-\u00ff]{8,63}|[\dA-Fa-f]{64})$/;
	return (value !== null && regex.test(value)) ? true : error;
}
</script>
