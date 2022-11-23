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
import LeapConfiguration from '@/pages/Network/Connection/WiFi/LeapConfiguration.vue';
import WepConfiguration from '@/pages/Network/Connection/WiFi/WepConfiguration.vue';
import WpaEapConfiguration from '@/pages/Network/Connection/WiFi/WpaEapConfiguration.vue';
import WpaPskConfiguration from '@/pages/Network/Connection/WiFi/WpaPskConfiguration.vue';

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
