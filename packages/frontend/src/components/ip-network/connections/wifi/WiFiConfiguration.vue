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
	<div v-if='configuration.wifi'>
		<h2 class='mb-3 text-h6'>
			{{ $t("components.ipNetwork.connections.form.wifi.title") }}
		</h2>
		<ITextInput
			v-model='configuration.wifi.ssid'
			:label='$t("components.ipNetwork.connections.form.wifi.ssid")'
			:rules='[
				(v: unknown) => ValidationRules.required(v, $t("components.ipNetwork.connections.errors.wifi.ssid.required")),
			]'
			required
			:prepend-inner-icon='mdiWifi'
		/>
		<WiFiSecurityTypeInput
			v-model='configuration.wifi.security.type'
		/>
		<LeapConfigurationFields
			v-if='configuration.wifi.security.type === WifiSecurityType.LEAP'
			v-model='configuration'
		/>
		<WpaEapConfiguration
			v-if='configuration.wifi.security.type === WifiSecurityType.WPA_EAP'
			v-model='configuration'
		/>
		<WpaPskConfiguration
			v-if='configuration.wifi.security.type === WifiSecurityType.WPA_PSK'
			v-model='configuration'
		/>
	</div>
</template>

<script setup lang='ts'>
import {
	EapConfiguration,
	EapPhaseOneMethod,
	EapPhaseTwoMethod,
	LeapConfiguration,
	type NetworkConnectionConfiguration,
	WepConfiguration,
	WepKeyLen,
	WepKeyType,
	WifiSecurityType,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import {
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiWifi } from '@mdi/js';
import { type PropType, watch } from 'vue';

import LeapConfigurationFields
	from '@/components/ip-network/connections/wifi/LeapConfiguration.vue';
import WiFiSecurityTypeInput
	from '@/components/ip-network/connections/wifi/WiFiSecurityTypeInput.vue';
import WpaEapConfiguration
	from '@/components/ip-network/connections/wifi/WpaEapConfiguration.vue';
import WpaPskConfiguration
	from '@/components/ip-network/connections/wifi/WpaPskConfiguration.vue';

/// Network connection configuration
const configuration = defineModel({
	type: Object as PropType<NetworkConnectionConfiguration>,
	required: true,
});

// Add specific security configuration if selected and is not present
watch(configuration, (value: NetworkConnectionConfiguration): void => {
	if (value.wifi?.security === undefined) {
		return;
	}
	switch (value.wifi.security.type) {
		case WifiSecurityType.LEAP:
			Object.assign(value.wifi.security, {
				leap: {
					username: '',
					password: '',
				} as LeapConfiguration,
			});
			break;
		case WifiSecurityType.WEP:
			Object.assign(value.wifi.security, {
				wep: {
					keyLen: WepKeyLen.BIT128,
					keyType: WepKeyType.KEY,
					keys: ['', '', '', ''],
				} as WepConfiguration,
			});
			break;
		case WifiSecurityType.WPA_EAP:
			Object.assign(value.wifi.security, {
				eap: {
					anonymousIdentity: '',
					cert: '',
					identity: '',
					password: '',
					phaseOneMethod: EapPhaseOneMethod.PEAP,
					phaseTwoMethod: EapPhaseTwoMethod.MSCHAPV2,
				} as EapConfiguration,
			});
			break;
		case WifiSecurityType.WPA_PSK:
			Object.assign(value.wifi.security, {
				psk: '',
			});
			break;
	}
});
</script>
