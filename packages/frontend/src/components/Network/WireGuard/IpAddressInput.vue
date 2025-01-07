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
		:rules='rules'
		:custom-messages='errorMessages'
	>
		<v-text-field
			v-model='address'
			:label='label'
			:success='touched ? valid : null'
			:error-messages='errors'
		>
			<template #prepend>
				<v-btn
					v-if='multiple'
					color='success'
					small
					@click='add'
				>
					<v-icon>
						mdi-plus
					</v-icon>
				</v-btn>
			</template>
			<template #append-outer>
				<v-btn
					v-if='multiple && allowRemoval'
					color='error'
					small
					@click='remove'
				>
					<v-icon>
						mdi-delete
					</v-icon>
				</v-btn>
			</template>
		</v-text-field>
	</ValidationProvider>
</template>

<script lang='ts'>
import {Component, Prop, VModel, Vue} from 'vue-property-decorator';

import {extend, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';

import {ipv4, ipv6} from '@/helpers/validators';

/**
 * IP address input
 */
@Component({
	components: {
		ValidationProvider,
	},
})
export default class IpAddressInput extends Vue {

	/**
	 * Edited IP address
	 */
	@VModel({required: true}) address!: string;

	/**
	 * @property {4|6} version IP address version
	 */
	@Prop({required: true}) version!: 4|6;

	/**
	 * @property {boolean} multiple Whether to allow multiple addresses
   */
	@Prop({required: false, default: false}) multiple!: boolean;

	/**
	 * @property {boolean} allowRemoval Whether to allow removing addresses
	 */
	@Prop({required: false, default: false}) allowRemoval!: boolean;

	/**
	 * @property {string} rules Validation rules
	 */
	get rules(): string {
		switch(this.version) {
			case 4:
				return 'required|ipv4';
			case 6:
				return 'required|ipv6';
			default:
				return '';
		}
	}

	/**
	 * @property {string} errorMessages Validation error messages
	 */
	get errorMessages(): any {
		if (this.version === 4) {
			return {
				required: this.$t('network.wireguard.tunnels.errors.ipv4'),
				ipv4: this.$t('network.wireguard.tunnels.errors.ipv4Invalid'),
			};
		} else {
			return {
				required: this.$t('network.wireguard.tunnels.errors.ipv6'),
				ipv6: this.$t('network.wireguard.tunnels.errors.ipv6Invalid'),
			};
		}
	}

	/**
	 * @property {string} label Input label
	 */
	get label(): string {
		if (this.version === 4) {
			return this.$t('network.wireguard.tunnels.form.ipv4').toString();
		} else {
			return this.$t('network.wireguard.tunnels.form.ipv6').toString();
		}
	}

	/**
	 * Initializes form validation rules
	 */
	protected mounted(): void {
		extend('required', required);
		extend('ipv4', ipv4);
		extend('ipv6', ipv6);
	}

	/**
	 * Adds a new IP address
	 */
	private add(): void {
		this.$emit('add');
	}

	/**
	 * Removes the IP address
	 */
	private remove(): void {
		this.$emit('remove');
	}

}
</script>
