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
	<div>
		<v-select
			v-model='connection.wifi.security.eap.phaseOneMethod'
			:items='authOneOptions'
			:label='$t("network.wireless.form.authPhaseOne").toString()'
		/>
		<v-text-field
			v-model='connection.wifi.security.eap.anonymousIdentity'
			:label='$t("network.wireless.form.anonymousIdentity").toString()'
		/>
		<v-text-field
			v-model='connection.wifi.security.eap.cert'
			:label='$t("network.wireless.form.caCert").toString()'
		/>
		<v-select
			v-model='connection.wifi.security.eap.phaseTwoMethod'
			:items='authTwoOptions'
			:label='$t("network.wireless.form.authPhaseTwo").toString()'
		/>
		<ValidationProvider
			v-slot='{errors, touched, valid}'
			rules='required'
			:custom-messages='{
				required: $t("forms.errors.username"),
			}'
		>
			<v-text-field
				v-model='connection.wifi.security.eap.identity'
				:label='$t("network.wireless.form.username").toString()'
				:success='touched ? valid : null'
				:error-messages='errors'
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
				:success='touched ? valid : null'
				:error-messages='errors'
			/>
		</ValidationProvider>
	</div>
</template>

<script lang='ts'>
import {Component, VModel, Vue} from 'vue-property-decorator';
import PasswordInput from '@/components/Core/PasswordInput.vue';

import {extend, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';

import {IConnection} from '@/interfaces/Network/Connection';
import {ISelectItem} from '@/interfaces/Vuetify';

/**
 * WPA-EAP configuration options
 */
@Component({
	components: {
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
	 * @constant {Array<ISelectItem>} authOneOptions EAP phase one authentication options
	 */
	private readonly authOneOptions: Array<ISelectItem> = [
		{
			text: this.$t('network.wireless.form.phaseOneAlgorithm.peap'),
			value: 'peap'
		},
	];

	/**
	 * @constant {Array<ISelectItem>} authTwoOptions EAP phase two authentication options
	 */
	private readonly authTwoOptions: Array<ISelectItem> = [
		{
			text: this.$t('network.wireless.form.phaseTwoAlgorithm.mschapv2'),
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
