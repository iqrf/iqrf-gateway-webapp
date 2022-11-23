<template>
	<div class='form-group'>
		<CSelect
			:value.sync='connection.wifi.security.eap.phaseOneMethod'
			:options='authOneOptions'
			:label='$t("network.wireless.form.authPhaseOne").toString()'
		/>
		<CInput
			v-model='connection.wifi.security.eap.anonymousIdentity'
			:label='$t("network.wireless.form.anonymousIdentity").toString()'
		/>
		<CInput
			v-model='connection.wifi.security.eap.cert'
			:label='$t("network.wireless.form.caCert").toString()'
		/>
		<CSelect
			:value.sync='connection.wifi.security.eap.phaseTwoMethod'
			:options='authTwoOptions'
			:label='$t("network.wireless.form.authPhaseTwo").toString()'
		/>
		<ValidationProvider
			v-slot='{errors, touched, valid}'
			rules='required'
			:custom-messages='{
				required: $t("forms.errors.username"),
			}'
		>
			<CInput
				v-model='connection.wifi.security.eap.identity'
				:label='$t("network.wireless.form.username").toString()'
				:is-valid='touched ? valid : null'
				:invalid-feedback='errors.join(", ")'
			/>
		</ValidationProvider>
		<ValidationProvider
			v-slot='{errors, touched, valid}'
			rules='required'
			:custom-messages='{
				required: $t("forms.errors.password"),
			}'
		>
			<PasswordInput
				v-model='connection.wifi.security.eap.password'
				:label='$t("network.wireless.form.password").toString()'
				:is-valid='touched ? valid : null'
				:invalid-feedback='errors.join(", ")'
			/>
		</ValidationProvider>
	</div>
</template>

<script lang='ts'>
import {Component, VModel, Vue} from 'vue-property-decorator';
import {CInput, CSelect} from '@coreui/vue/src';
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';
import {extend, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';

import PasswordInput from '@/components/Core/PasswordInput.vue';

import {IOption} from '@/interfaces/Coreui';
import {IConnection} from '@/interfaces/Network/Connection';

/**
 * WPA-EAP configuration options
 */
@Component({
	components: {
		CInput,
		CSelect,
		FontAwesomeIcon,
		PasswordInput,
		ValidationProvider,
	},
})
export default class WpaEapConfiguration extends Vue {

	/**
	 * Edited connection.
	 */
	@VModel({required: true}) connection!: IConnection;

	/**
	 * @constant {Array<IOption>} authOneOptions CoreUI EAP phase one authentication options
	 */
	private authOneOptions: Array<IOption> = [
		{
			label: this.$t('network.wireless.form.phaseOneAlgorithm.peap'),
			value: 'peap'
		},
	];

	/**
	 * @constant {Array<IOption>} authTwoOptions CoreUI EAP phase two authentication options
	 */
	private authTwoOptions: Array<IOption> = [
		{
			label: this.$t('network.wireless.form.phaseTwoAlgorithm.mschapv2'),
			value: 'mschapv2'
		},
	];

	/**
	 * Initializes validation rules
	 */
	protected created(): void {
		extend('required', required);
	}

}
</script>
