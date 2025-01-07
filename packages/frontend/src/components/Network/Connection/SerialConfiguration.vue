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
			<v-text-field
				v-model.number='connection.serial.baudRate'
				:label='$t("network.serial.form.baudRate").toString()'
				:success='touched ? valid : null'
				:error-messages='errors'
				type='number'
			/>
		</ValidationProvider>
	</div>
</template>

<script lang='ts'>
import {Component, VModel, Vue} from 'vue-property-decorator';
import {extend, ValidationProvider} from 'vee-validate';

import {required} from 'vee-validate/dist/rules';
import PasswordInput from '@/components/Core/PasswordInput.vue';
import {
	NetworkConnectionConfiguration
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';

/**
 * GSM modem connection configuration
 */
@Component({
	components: {
		PasswordInput,
		ValidationProvider,
	},
})
export default class SerialConfiguration extends Vue {

	/**
	 * Edited connection.
	 */
	@VModel({required: true}) connection!: NetworkConnectionConfiguration;

	/**
	 * Initializes validation rules
	 */
	protected created(): void {
		extend('required', required);
	}

}
</script>
