<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
		:rules='rules'
		:custom-messages='{
			required: $t(`network.wireguard.tunnels.errors.ipv${version.toString()}Prefix`),
			integer: $t(`network.wireguard.tunnels.errors.ipv${version.toString()}PrefixInvalid`),
			between: $t(`network.wireguard.tunnels.errors.ipv${version.toString()}PrefixInvalid`),
		}'
	>
		<v-text-field
			v-model.number='prefix'
			type='number'
			:label='$t(`network.wireguard.tunnels.form.ipv${version.toString()}Prefix`).toString()'
			:success='touched ? valid : null'
			:error-messages='errors'
		/>
	</ValidationProvider>
</template>

<script lang='ts'>
import {Component, Prop, VModel, Vue} from 'vue-property-decorator';
import {extend, ValidationProvider} from 'vee-validate';
import {between, integer, required} from 'vee-validate/dist/rules';

/**
 * IP address input
 */
@Component({
	components: {
		ValidationProvider,
	},
})
export default class IpAddressPrefixInput extends Vue {

	/**
	 * @property {number} prefix Edited IP address prefix
	 */
	@VModel({required: true}) prefix!: number;

	/**
	 * @property {4|6} version IP address version
	 */
	@Prop({required: true}) version!: 4|6;

	/**
	 * @property {string} rules Validation rules
	 */
	get rules(): string {
		switch(this.version) {
			case 4:
				return 'required|integer|between:1,32';
			case 6:
				return 'required|integer|between:48,128';
			default:
				return '';
		}
	}

	/**
	 * Initializes form validation rules
	 */
	protected	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
	}

}
</script>
