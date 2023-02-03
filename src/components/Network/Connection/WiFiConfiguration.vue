<!--
Copyright 2017-2023 IQRF Tech s.r.o.
Copyright 2019-2023 MICRORISC s.r.o.

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
	<CRow>
		<CCol>
			<legend>{{ $t('network.wireless.form.title') }}</legend>
			<div class='form-group'>
				<strong>
					<span>{{ $t('network.wireless.form.security') }}</span>
				</strong> {{ $t(`network.wireless.form.securityTypes.${connection.wifi.security.type}`) }}
			</div>
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
		</CCol>
	</CRow>
</template>

<script lang='ts'>
import {Component, VModel, Vue} from 'vue-property-decorator';
import {CCol, CRow} from '@coreui/vue/src';

import PasswordInput from '@/components/Core/PasswordInput.vue';
import LeapConfiguration from '@/components/Network/Connection/WiFi/LeapConfiguration.vue';
import WepConfiguration from '@/components/Network/Connection/WiFi/WepConfiguration.vue';
import WpaEapConfiguration from '@/components/Network/Connection/WiFi/WpaEapConfiguration.vue';
import WpaPskConfiguration from '@/components/Network/Connection/WiFi/WpaPskConfiguration.vue';

import {IConnection} from '@/interfaces/Network/Connection';

/**
 * Wi-Fi configuration options
 */
@Component({
	components: {
		CCol,
		CRow,
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
	@VModel({required: true}) connection!: IConnection;

}
</script>
