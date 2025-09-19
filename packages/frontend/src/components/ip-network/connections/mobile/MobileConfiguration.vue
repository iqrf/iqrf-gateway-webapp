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
	<div v-if='configuration.gsm'>
		<h2 class='mb-3 text-h6'>
			{{ $t("components.ipNetwork.connections.form.gsm.title") }}
		</h2>
		<v-text-field
			v-model='configuration.gsm.apn'
			:label='$t("components.ipNetwork.connections.form.gsm.apn")'
			:rules='[
				(v: unknown) => ValidationRules.required(v, $t("components.ipNetwork.connections.errors.gsm.apn.required")),
				(v: unknown) => {
					if (typeof v !== "string" || !/^[a-z0-9.-]+$/.test(v)) {
						return $t("components.ipNetwork.connections.errors.gsm.apn.apn");
					}
					return true;
				},
			]'
			required
			:prepend-inner-icon='mdiAccessPoint'
		/>
		<v-text-field
			v-model='configuration.gsm.pin'
			:label='$t("components.ipNetwork.connections.form.gsm.pin")'
			:rules='[
				(v: unknown) => {
					if (typeof v !== "number" || !/^\d{4,8}$/.test(v.toString())) {
						return $t("components.ipNetwork.connections.errors.gsm.pin.pin");
					}
					return true;
				},
			]'
			:prepend-inner-icon='mdiKey'
		/>
		<v-text-field
			v-model='configuration.gsm.username'
			:label='$t("components.ipNetwork.connections.form.gsm.username")'
			:rules='[
				(v: unknown) => ValidationRules.requiredIf(v, configuration.gsm!.password.length > 1, $t("components.ipNetwork.connections.errors.gsm.username.required")),
			]'
			:prepend-inner-icon='mdiAccount'
		/>
		<IPasswordInput
			v-model='configuration.gsm.password'
			:label='$t("components.ipNetwork.connections.form.gsm.password")'
			:rules='[
				(v: unknown) => ValidationRules.requiredIf(v, configuration.gsm!.username.length > 1, $t("components.ipNetwork.connections.errors.gsm.password.required")),
			]'
			:prepend-inner-icon='mdiKey'
		/>
	</div>
</template>

<script setup lang='ts'>
import {
	IPv6ConfigurationMethod,
	type NetworkConnectionConfiguration,
	type SerialConnection,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import {
	IPasswordInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiAccessPoint, mdiAccount, mdiKey } from '@mdi/js';
import { storeToRefs } from 'pinia';
import { computed, ComputedRef, type PropType, watch } from 'vue';

import { useGatewayStore } from '@/store/gateway';

/// Network connection configuration
const configuration = defineModel({
	type: Object as PropType<NetworkConnectionConfiguration>,
	required: true,
});

const gatewayStore = useGatewayStore();
const { board: gatewayModel } = storeToRefs(gatewayStore);

/// Whether the GSM modem is broken
const hasBrokenGsmModem: ComputedRef<boolean> = computed((): boolean =>
	gatewayModel.value === 'MICRORISC s.r.o. IQD-GW04' && configuration.value.interface === 'ttyAMA2',
);

// Add Serial link configuration if the interface is ttyAMA or ttyAMC or ttyS
watch(configuration.value, (value: NetworkConnectionConfiguration): void => {
	if (value.interface === undefined) {
		return;
	}
	if (/tty(?:AMA|AMC|S)\d+/.test(value.interface) && value.serial === undefined) {
		const serial: SerialConnection = {
			baudRate: 115_200,
			bits: 8,
			parity: 'n',
			stopBits: 1,
			sendDelay: 0,
		};
		Object.assign(configuration.value, { serial: serial });
	}
	if (hasBrokenGsmModem.value) {
		configuration.value.ipv6.method = IPv6ConfigurationMethod.DISABLED;
	}
});
</script>
