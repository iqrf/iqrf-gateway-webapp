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
	<div>
		<legend>{{ $t('network.serial.form.title') }}</legend>
		<ValidationProvider
			v-slot='{errors, touched, valid}'
			rules='required'
			:custom-messages='{
				required: $t("network.serial.errors.baudRateMissing"),
			}'
		>
			<CInput
				v-model.number='connection.serial.baudRate'
				:label='$t("network.serial.form.baudRate").toString()'
				:is-valid='touched ? valid : null'
				:invalid-feedback='errors.join(", ")'
				type='number'
			/>
		</ValidationProvider>
	</div>
</template>

<script lang='ts'>
import {Component, VModel, Vue} from 'vue-property-decorator';
import {CInput} from '@coreui/vue/src';
import {extend, ValidationProvider} from 'vee-validate';

import {required} from 'vee-validate/dist/rules';
import {IConnection} from '@/interfaces/Network/Connection';
import PasswordInput from '@/components/Core/PasswordInput.vue';

/**
 * GSM modem connection configuration
 */
@Component({
	components: {
		CInput,
		PasswordInput,
		ValidationProvider,
	},
})
export default class SerialConfiguration extends Vue {

	/**
	 * Edited connection.
	 */
	@VModel({required: true}) connection!: IConnection;

	/**
	 * Initializes validation rules
	 */
	protected created(): void {
		extend('required', required);
	}

}
</script>
