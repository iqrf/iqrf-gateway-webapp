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
		<ValidationProvider
			v-slot='{errors, touched, valid}'
			rules='required'
			:custom-messages='{
				required: $t("network.connection.ipv4.errors.method"),
			}'
		>
			<CSelect
				id='ipv4MethodSelect'
				:value.sync='connection.ipv4.method'
				:options='methods'
				:label='$t("network.connection.ipv4.method").toString()'
				:placeholder='$t("network.connection.ipv4.methods.null").toString()'
				:is-valid='touched ? valid : null'
				:invalid-feedback='errors.join(", ")'
			/>
		</ValidationProvider>
		<div v-if='connection.ipv4.method === ConfigurationMethod.MANUAL'>
			<hr>
			<CAlert
				v-if='!ipv4InSubnet'
				color='danger'
			>
				{{ $t('network.connection.ipv4.ipNotInSubnet') }}
			</CAlert>
			<ValidationProvider
				v-slot='{errors, touched, valid}'
				:rules='{
					required: connection.ipv4.method === ConfigurationMethod.MANUAL,
					ipv4: connection.ipv4.method === ConfigurationMethod.MANUAL,
				}'
				:custom-messages='{
					required: $t("network.connection.ipv4.errors.address"),
					ipv4: $t("network.connection.ipv4.errors.addressInvalid"),
				}'
			>
				<CInput
					v-model='connection.ipv4.addresses[0].address'
					:label='$t("network.connection.ipv4.address").toString()'
					:is-valid='touched ? valid : null'
					:invalid-feedback='errors.join(", ")'
				/>
			</ValidationProvider>
			<ValidationProvider
				v-slot='{errors, touched, valid}'
				:rules='{
					required: connection.ipv4.method === ConfigurationMethod.MANUAL,
					ipv4: connection.ipv4.method === ConfigurationMethod.MANUAL,
					netmask: connection.ipv4.method === ConfigurationMethod.MANUAL,
				}'
				:custom-messages='{
					required: $t("network.connection.ipv4.errors.mask"),
					ipv4: $t("network.connection.ipv4.errors.addressInvalid"),
					netmask: $t("network.connection.ipv4.errors.maskInvalid"),
				}'
			>
				<CInput
					v-model='connection.ipv4.addresses[0].mask'
					:label='$t("network.connection.ipv4.mask").toString()'
					:is-valid='touched ? valid : null'
					:invalid-feedback='errors.join(", ")'
				/>
			</ValidationProvider>
			<ValidationProvider
				v-slot='{errors, touched, valid}'
				:rules='{
					required: connection.ipv4.method === ConfigurationMethod.MANUAL,
					ipv4: connection.ipv4.method === ConfigurationMethod.MANUAL,
				}'
				:custom-messages='{
					required: $t("network.connection.ipv4.errors.gateway"),
					ipv4: $t("network.connection.ipv4.errors.addressInvalid"),
				}'
			>
				<CInput
					v-model='connection.ipv4.gateway'
					:label='$t("network.connection.ipv4.gateway").toString()'
					:is-valid='touched ? valid : null'
					:invalid-feedback='errors.join(", ")'
				/>
			</ValidationProvider>
			<hr>
			<div
				v-for='(address, index) in connection.ipv4.dns'
				:key='index'
			>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					:rules='{
						required: connection.ipv4.method === ConfigurationMethod.MANUAL,
						ipv4: connection.ipv4.method === ConfigurationMethod.MANUAL,
					}'
					:custom-messages='{
						required: $t("network.connection.ipv4.errors.dns"),
						ipv4: $t("network.connection.ipv4.errors.addressInvalid"),
					}'
				>
					<CInput
						v-model='address.address'
						:label='$t("network.connection.ipv4.dns.address").toString()'
						:is-valid='touched ? valid : null'
						:invalid-feedback='errors.join(", ")'
					>
						<template #prepend-content>
							<span
								class='text-success'
								@click='addDns'
							>
								<FontAwesomeIcon :icon='["far", "plus-square"]' size='xl' />
							</span>
						</template>
						<template #append-content>
							<span
								v-if='connection.ipv4.dns.length > 1'
								class='text-danger'
								@click='deleteDns(index)'
							>
								<FontAwesomeIcon :icon='["far", "trash-alt"]' size='xl' />
							</span>
						</template>
					</CInput>
				</ValidationProvider>
			</div>
		</div>
	</div>
</template>

<script lang='ts'>
import {Component, VModel, Vue} from 'vue-property-decorator';

import {CAlert, CCol, CInput, CRow, CSelect} from '@coreui/vue/src';
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';
import {extend, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';

import {ConfigurationMethod} from '@/enums/Network/Ip';
import IpAddressHelper from '@/helpers/IpAddressHelper';
import {ipv4} from '@/helpers/validators';
import {IOption} from '@/interfaces/Coreui';
import {IConnection} from '@/interfaces/Network/Connection';

/**
 * IPv4 configuration options
 */
@Component({
	components: {
		CAlert,
		CCol,
		CInput,
		CRow,
		CSelect,
		FontAwesomeIcon,
		ValidationProvider,
	},
	data: () => ({
		ConfigurationMethod,
	}),
})
export default class IPv4Configuration extends Vue {

	/**
	 * Edited connection.
	 */
	@VModel({required: true}) connection!: IConnection;

	/**
	 * Computes array of CoreUI select options for IPv4 configuration method
	 * @returns {Array<IOption>} Configuration method options
	 */
	get methods(): Array<IOption> {
		//const methods = ['auto', 'disabled', 'link-local', 'manual', 'shared'];
		const methods = ['auto', 'manual', 'shared'];
		return methods.map((method: string) => ({
			value: method,
			label: this.$t(`network.connection.ipv4.methods.${method}`).toString(),
		}));
	}

	/**
	 * Initializes validation rules
	 */
	protected created(): void {
		extend('ipv4', ipv4);
		extend('netmask', (mask: string) => {
			const maskTokens = mask.split('.');
			const binaryMask = maskTokens.map((token: string) => {
				return parseInt(token).toString(2).padStart(8, '0');
			}).join('');
			return new RegExp(/^1{8,32}0{0,24}$/).test(binaryMask);
		});
		extend('required', required);
	}

	/**
	 * Vue lifecycle hook mounted
	 */
	protected mounted(): void {
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
