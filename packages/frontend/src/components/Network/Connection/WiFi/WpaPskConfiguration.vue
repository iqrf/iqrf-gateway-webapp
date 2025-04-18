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
	<ValidationProvider
		v-slot='{errors, touched, valid}'
		rules='required|wpaPsk'
		:custom-messages='{
			required: $t("network.wireless.errors.psk"),
			wpaPsk: $t("network.wireless.errors.pskInvalid"),
		}'
	>
		<PasswordInput
			v-model='connection.wifi.security.psk'
			:label='$t("network.wireless.form.psk").toString()'
			:success='touched ? valid : null'
			:error-messages='errors'
		/>
	</ValidationProvider>
</template>

<script lang='ts'>
import {Component, VModel, Vue} from 'vue-property-decorator';
import {extend, ValidationProvider} from 'vee-validate';
import PasswordInput from '@/components/Core/PasswordInput.vue';

import {required} from 'vee-validate/dist/rules';

import {
	NetworkConnectionConfiguration
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';

/**
 * WPA-PSK configuration options
 */
@Component({
	components: {
		PasswordInput,
		ValidationProvider,
	},
})
export default class WpaPskConfiguration extends Vue {

	/**
	 * Edited connection.
	 */
	@VModel({required: true}) connection!: NetworkConnectionConfiguration;

	/**
	 * Initializes validation rules
	 */
	protected created(): void {
		extend('required', required);
		extend('wpaPsk', (key: string) => {
			return /^([\u0020-\u007e\u0080-\u00ff]{8,63}|[\da-fA-F]{64})$/.test(key);
		});
	}

}
</script>
