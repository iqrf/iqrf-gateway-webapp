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
	<div>
		<ValidationProvider
			v-slot='{errors, touched, valid}'
			rules='required'
			:custom-messages='{
				required: $t("network.connection.ipv4.errors.method"),
			}'
		>
			<v-select
				v-model='connection.ipv4.method'
				:items='methods'
				:label='$t("network.connection.ipv4.method").toString()'
				:placeholder='$t("network.connection.ipv4.methods.null").toString()'
				:success='touched ? valid : null'
				:error-messages='errors'
				@change='onMethodChangeStaticFixup'
			/>
		</ValidationProvider>
		<div v-if='connection.ipv4.method === IPV4ConfigurationMethod.MANUAL'>
			<v-alert
				v-if='!ipv4InSubnet'
				color='error'
				text
			>
				{{ $t('network.connection.ipv4.ipNotInSubnet') }}
			</v-alert>
			<ValidationProvider
				v-slot='{errors, touched, valid}'
				:rules='{
					required: connection.ipv4.method === IPV4ConfigurationMethod.MANUAL,
					ipv4: connection.ipv4.method === IPV4ConfigurationMethod.MANUAL,
				}'
				:custom-messages='{
					required: $t("network.connection.ipv4.errors.address"),
					ipv4: $t("network.connection.ipv4.errors.addressInvalid"),
				}'
			>
				<v-text-field
					v-model='connection.ipv4.addresses[0].address'
					:label='$t("network.connection.ipv4.address").toString()'
					:success='touched ? valid : null'
					:error-messages='errors'
				/>
			</ValidationProvider>
			<ValidationProvider
				v-slot='{errors, touched, valid}'
				:rules='{
					required: connection.ipv4.method === IPV4ConfigurationMethod.MANUAL,
					ipv4: connection.ipv4.method === IPV4ConfigurationMethod.MANUAL,
					netmask: connection.ipv4.method === IPV4ConfigurationMethod.MANUAL,
				}'
				:custom-messages='{
					required: $t("network.connection.ipv4.errors.mask"),
					ipv4: $t("network.connection.ipv4.errors.addressInvalid"),
					netmask: $t("network.connection.ipv4.errors.maskInvalid"),
				}'
			>
				<v-text-field
					v-model='connection.ipv4.addresses[0].mask'
					:label='$t("network.connection.ipv4.mask").toString()'
					:success='touched ? valid : null'
					:error-messages='errors'
				/>
			</ValidationProvider>
			<ValidationProvider
				v-slot='{errors, touched, valid}'
				:rules='{
					required: connection.ipv4.method === IPV4ConfigurationMethod.MANUAL,
					ipv4: connection.ipv4.method === IPV4ConfigurationMethod.MANUAL,
				}'
				:custom-messages='{
					required: $t("network.connection.ipv4.errors.gateway"),
					ipv4: $t("network.connection.ipv4.errors.addressInvalid"),
				}'
			>
				<v-text-field
					v-model='connection.ipv4.gateway'
					:label='$t("network.connection.ipv4.gateway").toString()'
					:success='touched ? valid : null'
					:error-messages='errors'
				/>
			</ValidationProvider>
			<v-divider class='mb-2' />
			<div
				v-for='(address, index) in connection.ipv4.dns'
				:key='index'
			>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					:rules='{
						required: connection.ipv4.method === IPV4ConfigurationMethod.MANUAL,
						ipv4: connection.ipv4.method === IPV4ConfigurationMethod.MANUAL,
					}'
					:custom-messages='{
						required: $t("network.connection.ipv4.errors.dns"),
						ipv4: $t("network.connection.ipv4.errors.addressInvalid"),
					}'
				>
					<v-text-field
						v-model='address.address'
						:label='$t("network.connection.ipv4.dns.address").toString()'
						:success='touched ? valid : null'
						:error-messages='errors'
					>
						<template #prepend>
							<v-btn
								color='success'
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
								v-if='connection.ipv4.dns.length > 1'
								color='error'
								small
								@click='deleteDns(index)'
							>
								<v-icon>
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
import {Component, VModel, Vue} from 'vue-property-decorator';
import {extend, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';

import IpAddressHelper from '@/helpers/IpAddressHelper';
import {subnetMask} from '@/helpers/validationRules/Network';
import {ipv4} from '@/helpers/validators';

import {ISelectItem} from '@/interfaces/Vuetify';
import {
	IPV4ConfigurationMethod,
	NetworkConnectionConfiguration,
	NetworkConnectionType,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network/NetworkConnection';

/**
 * IPv4 configuration options
 */
@Component({
	components: {
		ValidationProvider,
	},
	data: () => ({
		IPV4ConfigurationMethod,
	}),
})
export default class IPv4Configuration extends Vue {

	/**
	 * Edited connection.
	 */
	@VModel({required: true}) connection!: NetworkConnectionConfiguration;

	/**
	 * Computes array of select options for IPv4 configuration method
	 * @returns {Array<ISelectItem>} Configuration method options
	 */
	get methods(): Array<ISelectItem> {
		let methods: Array<IPV4ConfigurationMethod>;
		// let methods = ['auto', 'disabled', 'link-local', 'manual', 'shared'];
		if (this.connection.type === NetworkConnectionType.GSM) {
			methods = [IPV4ConfigurationMethod.AUTO, IPV4ConfigurationMethod.DISABLED];
		} else {
			methods = [IPV4ConfigurationMethod.AUTO, IPV4ConfigurationMethod.MANUAL, IPV4ConfigurationMethod.SHARED];
		}
		return methods.map((method: string) => ({
			value: method,
			text: this.$t(`network.connection.ipv4.methods.${method}`).toString(),
		}));
	}

	/**
	 * Initializes validation rules
	 */
	protected created(): void {
		extend('ipv4', ipv4);
		extend('netmask', subnetMask);
		extend('required', required);
	}

	/**
	 * Vue lifecycle hook mounted
	 */
	protected mounted(): void {
		this.onMethodChangeStaticFixup();
	}

	/**
	 * Populates IPv6 configuration object if empty
	 */
	private onMethodChangeStaticFixup(): void {
		if (this.connection.ipv4.addresses.length === 0) {
			this.connection.ipv4.addresses.push({address: '', prefix: 32, mask: ''});
		}
		if (this.connection.ipv4.dns.length === 0) {
			this.addDns();
		}
	}

	/**
	 * Adds a new IPv4 DNS object to configuration
	 */
	private addDns(): void {
		this.connection.ipv4.dns.push({address: ''});
	}

	/**
	 * Removes an IPv4 DNS object specified by index
	 * @param {number} index Index of DNS object
	 */
	private deleteDns(index: number): void {
		this.connection.ipv4.dns.splice(index, 1);
	}

	/**
	 * Checks if IPv4 and gateway address are in the same subnet
	 * @returns {boolean} Are addresses in the same subnet?
	 */
	get ipv4InSubnet(): boolean {
		return IpAddressHelper.ipv4ConnectionSubnetCheck(this.connection);
	}

}
</script>
