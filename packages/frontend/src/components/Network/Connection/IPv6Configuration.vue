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
		<ValidationProvider
			v-slot='{errors, touched, valid}'
			rules='required'
			:custom-messages='{
				required: $t("network.connection.ipv6.errors.method"),
			}'
		>
			<v-select
				v-model='connection.ipv6.method'
				:label='$t("network.connection.ipv6.method").toString()'
				:items='methods'
				:placeholder='$t("network.connection.ipv6.methods.null").toString()'
				:success='touched ? valid : null'
				:error-messages='errors'
				:disabled='disabled'
				@change='onMethodChangeStaticFixup'
			/>
		</ValidationProvider>
		<div v-if='connection.ipv6.method === IPv6ConfigurationMethod.MANUAL'>
			<v-row
				v-for='(address, index) in connection.ipv6.addresses'
				:key='index'
			>
				<v-col cols='12' lg='8'>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						:rules='{
							required: connection.ipv6.method === IPv6ConfigurationMethod.MANUAL,
							ipv6: connection.ipv6.method === IPv6ConfigurationMethod.MANUAL,
						}'
						:custom-messages='{
							required: $t("network.connection.ipv6.errors.address"),
							ipv6: $t("network.connection.ipv6.errors.addressInvalid"),
						}'
					>
						<v-text-field
							v-model='address.address'
							:disabled='disabled'
							:label='$t("network.connection.ipv6.address").toString()'
							:success='touched ? valid : null'
							:error-messages='errors'
						>
							<template #prepend>
								<v-btn
									color='success'
									small
									@click='addAddress'
								>
									<v-icon small>
										mdi-plus
									</v-icon>
								</v-btn>
							</template>
							<template #append-outer>
								<v-btn
									v-if='connection.ipv6.addresses.length > 1'
									color='error'
									small
									@click='deleteAddress(index)'
								>
									<v-icon small>
										mdi-delete
									</v-icon>
								</v-btn>
							</template>
						</v-text-field>
					</ValidationProvider>
				</v-col>
				<v-col cols='12' lg='4'>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						:rules='{
							required: connection.ipv6.method === IPv6ConfigurationMethod.MANUAL,
							between: {
								enabled: connection.ipv6.method === IPv6ConfigurationMethod.MANUAL,
								min: 48,
								max: 128,
							}
						}'
						:custom-messages='{
							required: $t("network.connection.ipv6.errors.prefix"),
							between: $t("network.connection.ipv6.errors.prefixInvalid"),
						}'
					>
						<v-text-field
							v-model.number='address.prefix'
							:disabled='disabled'
							type='number'
							min='48'
							max='128'
							:label='$t("network.connection.ipv6.prefix").toString()'
							:success='touched ? valid : null'
							:error-messages='errors'
						/>
					</ValidationProvider>
				</v-col>
			</v-row>
			<ValidationProvider
				v-slot='{errors, touched, valid}'
				:rules='{
					required: connection.ipv6.method === IPv6ConfigurationMethod.MANUAL,
					ipv6: connection.ipv6.method === IPv6ConfigurationMethod.MANUAL,
				}'
				:custom-messages='{
					required: $t("network.connection.ipv6.errors.gateway"),
					ipv6: $t("network.connection.ipv6.errors.addressInvalid"),
				}'
			>
				<v-text-field
					v-model='connection.ipv6.gateway'
					:disabled='disabled'
					:label='$t("network.connection.ipv6.gateway").toString()'
					:success='touched ? valid : null'
					:error-messages='errors'
				/>
			</ValidationProvider>
			<v-divider class='mb-2' />
			<div
				v-for='(address, index) in connection.ipv6.dns'
				:key='index+"a"'
			>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					:rules='{
						required: connection.ipv6.method === IPv6ConfigurationMethod.MANUAL,
						ipv6: connection.ipv6.method === IPv6ConfigurationMethod.MANUAL,
					}'
					:custom-messages='{
						required: $t("network.connection.ipv6.errors.dns"),
						ipv6: $t("network.connection.ipv6.errors.addressInvalid"),
					}'
				>
					<v-text-field
						v-model='address.address'
						:disabled='disabled'
						:label='$t("network.connection.ipv6.dns.address").toString()'
						:success='touched ? valid : null'
						:error-messages='errors'
					>
						<template #prepend>
							<v-btn
								class='success'
								small
								@click='addDns'
							>
								<v-icon small>
									mdi-plus
								</v-icon>
							</v-btn>
						</template>
						<template #append-outer>
							<v-btn
								v-if='connection.ipv6.dns.length > 1'
								color='error'
								small
								@click='deleteDns(index)'
							>
								<v-icon small>
									mdi-delete
								</v-icon>
							</v-btn>
						</template>
					</v-text-field>
				</ValidationProvider>
			</div>
		</div>
	</div>
</template>

<script lang='ts'>
import {Component, VModel, Prop, Vue} from 'vue-property-decorator';
import {extend, ValidationProvider} from 'vee-validate';
import {between, required} from 'vee-validate/dist/rules';

import {ipv6} from '@/helpers/validators';

import {ISelectItem} from '@/interfaces/Vuetify';
import {
	IPv6ConfigurationMethod, NetworkConnectionConfiguration, NetworkConnectionType
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';

/**
 * IPv6 configuration options
 */
@Component({
	components: {
		ValidationProvider,
	},
	data: () => ({
		IPv6ConfigurationMethod,
	}),
})
export default class IPv6Configuration extends Vue {

	/**
	 * @property {NetworkConnectionConfiguration} connection Edited connection.
	 */
	@VModel({required: true}) connection!: NetworkConnectionConfiguration;

	/**
	 * @property {boolean} disabled If true, disables all inputs.
   */
	@Prop({type: Boolean, required: true}) disabled!: boolean;

	/**
	 * Initializes validation rules
	 */
	protected created(): void {
		extend('between', between);
		extend('ipv6', ipv6);
		extend('required', required);
	}

	/**
	 * Populates IPv6 configuration object if empty
	 */
	protected onMethodChangeStaticFixup(): void {
		if (this.connection.ipv6.addresses.length === 0) {
			this.addAddress();
		}
		if (this.connection.ipv6.dns.length === 0) {
			this.addDns();
		}
	}

	/**
	 * Computes array of select options for IPv6 configuration method
	 * @returns {Array<ISelectItem>} Configuration method options
	 */
	get methods(): Array<ISelectItem> {
		let methods: Array<IPv6ConfigurationMethod>;
		// let methods = ['auto', 'dhcp', 'disabled', 'ignore', 'link-local', 'manual', 'shared'];
		if (this.connection.type == NetworkConnectionType.GSM) {
			methods = [IPv6ConfigurationMethod.AUTO, IPv6ConfigurationMethod.DISABLED];
		} else  {
			methods = [IPv6ConfigurationMethod.AUTO, IPv6ConfigurationMethod.DHCP, IPv6ConfigurationMethod.MANUAL, IPv6ConfigurationMethod.SHARED];
		}
		return methods.map((method: string) =>
			({
				value: method,
				text: this.$t(`network.connection.ipv6.methods.${method}`).toString(),
			})
		);
	}

	/**
	 * Adds a new IPv6 address object to configuration
	 */
	private addAddress(): void {
		this.connection.ipv6.addresses.push({address: '', prefix: 64});
	}

	/**
	 * Removes an IPv6 address object specified by index
	 * @param {number} index Index of address object
	 */
	private deleteAddress(index: number): void {
		this.connection.ipv6.addresses.splice(index, 1);
	}

	/**
	 * Adds a new IPv6 DNS object to configuration
	 */
	private addDns(): void {
		this.connection.ipv6.dns.push({address: ''});
	}

	/**
	 * Removes an IPv6 DNS object specified by index
	 * @param {number} index Index of DNS object
	 */
	private deleteDns(index: number): void {
		this.connection.ipv6.dns.splice(index, 1);
	}

}
</script>
