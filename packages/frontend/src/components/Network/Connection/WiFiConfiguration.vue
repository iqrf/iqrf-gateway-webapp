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
	<v-row>
		<v-col>
			<h6>{{ $t('network.wireless.form.title') }}</h6>
			<v-text-field
				:label='$t("network.wireless.form.security")'
				:value='$t(`network.wireless.form.securityTypes.${connection.wifi.security.type}`)'
				disabled
				readonly
			/>
			<LeapConfiguration
				v-if='connection.wifi.security.type === "ieee8021x"'
				v-model='connection'
			/>
			<WepConfiguration
				v-if='connection.wifi.security.type === "wep"'
				v-model='connection'
			/>
			<WpaEapConfiguration
				v-if='connection.wifi.security.type === "wpa-eap"'
				v-model='connection'
			/>
			<WpaPskConfiguration
				v-if='connection.wifi.security.type === "wpa-psk"'
				v-model='connection'
			/>
		</v-col>
	</v-row>
</template>

<script lang='ts'>
import {Component, VModel, Vue} from 'vue-property-decorator';
import LeapConfiguration from '@/components/Network/Connection/WiFi/LeapConfiguration.vue';
import PasswordInput from '@/components/Core/PasswordInput.vue';
import WepConfiguration from '@/components/Network/Connection/WiFi/WepConfiguration.vue';
import WpaEapConfiguration from '@/components/Network/Connection/WiFi/WpaEapConfiguration.vue';
import WpaPskConfiguration from '@/components/Network/Connection/WiFi/WpaPskConfiguration.vue';

import {
	NetworkConnectionConfiguration
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';

/**
 * Wi-Fi configuration options
 */
@Component({
	components: {
		LeapConfiguration,
		PasswordInput,
		WepConfiguration,
		WpaEapConfiguration,
		WpaPskConfiguration,
	},
})
export default class WiFiConfiguration extends Vue {

	/**
	 * Edited connection.
	 */
	@VModel({required: true}) connection!: NetworkConnectionConfiguration;

}
</script>
