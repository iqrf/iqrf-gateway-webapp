<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

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
		<legend>{{ $t('network.mobile.form.title') }}</legend>
		<ValidationProvider
			v-slot='{errors, touched, valid}'
			rules='required|apn'
			:custom-messages='{
				required: $t("network.mobile.errors.apnMissing"),
				apn: $t("network.mobile.errors.apnInvalid"),
			}'
		>
			<CInput
				v-model='connection.gsm.apn'
				:label='$t("network.mobile.form.apn").toString()'
				:is-valid='touched ? valid : null'
				:invalid-feedback='errors.join(", ")'
			/>
		</ValidationProvider>
		<ValidationProvider
			v-slot='{errors, touched, valid}'
			rules='pin'
			:custom-messages='{
				pin: $t("network.mobile.errors.pinInvalid"),
			}'
		>
			<PasswordInput
				v-model='connection.gsm.pin'
				:label='$t("network.mobile.form.pin").toString()'
				:is-valid='touched ? valid : null'
				:invalid-feedback='errors.join(", ")'
			/>
		</ValidationProvider>
		<CRow>
			<CCol>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					:rules='{
						required: connection.gsm.password.length > 0
					}'
					:custom-messages='{
						required: $t("network.mobile.errors.credentialsMissing"),
					}'
				>
					<CInput
						v-model='connection.gsm.username'
						:label='$t("forms.fields.username").toString()'
						:is-valid='touched ? valid : null'
						:invalid-feedback='errors.join(", ")'
					/>
				</ValidationProvider>
			</CCol>
			<CCol>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					:rules='{
						required: connection.gsm.username.length > 0
					}'
					:custom-messages='{
						required: $t("network.mobile.errors.credentialsMissing"),
					}'
				>
					<PasswordInput
						v-model='connection.gsm.password'
						:label='$t("forms.fields.password").toString()'
						:is-valid='touched ? valid : null'
						:invalid-feedback='errors.join(", ")'
					/>
				</ValidationProvider>
				<CCol />
			</ccol>
		</CRow>
	</div>
</template>

<script lang='ts'>
import {Component, VModel, Vue} from 'vue-property-decorator';
import {CCol, CInput, CRow} from '@coreui/vue/src';
import {extend, ValidationProvider} from 'vee-validate';

import {required} from 'vee-validate/dist/rules';
import {apn, pin} from '@/helpers/validationRules/Network';

import {IConnection} from '@/interfaces/Network/Connection';
import PasswordInput from '@/components/Core/PasswordInput.vue';

/**
 * GSM modem connection configuration
 */
@Component({
	components: {
		CCol,
		CInput,
		CRow,
		PasswordInput,
		ValidationProvider,
	},
})
export default class GsmConfiguration extends Vue {

	/**
	 * Edited connection.
	 */
	@VModel({required: true}) connection!: IConnection;

	/**
	 * Initializes validation rules
	 */
	protected created(): void {
		extend('required', required);
		extend('apn', apn);
		extend('pin', pin);
	}

}
</script>
