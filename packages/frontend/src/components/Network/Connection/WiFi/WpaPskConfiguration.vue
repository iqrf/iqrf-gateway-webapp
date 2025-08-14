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
			:is-valid='touched ? valid : null'
			:invalid-feedback='errors.join(", ")'
		/>
	</ValidationProvider>
</template>

<script lang='ts'>
import {Component, VModel, Vue} from 'vue-property-decorator';
import {extend, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';

import PasswordInput from '@/components/Core/PasswordInput.vue';

import {IConnection} from '@/interfaces/Network/Connection';

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
	@VModel({required: true}) connection!: IConnection;

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
