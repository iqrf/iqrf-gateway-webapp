<template>
	<div>
		<ValidationProvider
			v-slot='{errors, touched, valid}'
			rules='required'
			:custom-messages='{
				required: $t("network.connection.ipv6.errors.method"),
			}'
		>
			<CSelect
				:value.sync='connection.ipv6.method'
				:label='$t("network.connection.ipv6.method").toString()'
				:options='methods'
				:placeholder='$t("network.connection.ipv6.methods.null").toString()'
				:is-valid='touched ? valid : null'
				:invalid-feedback='errors.join(", ")'
			/>
		</ValidationProvider>
		<div v-if='connection.ipv6.method === ConfigurationMethod.MANUAL'>
			<hr>
			<CRow
				v-for='(address, index) in connection.ipv6.addresses'
				:key='index'
				form
			>
				<CCol sm='12' lg='8'>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						:rules='{
							required: connection.ipv6.method === ConfigurationMethod.MANUAL,
							ipv6: connection.ipv6.method === ConfigurationMethod.MANUAL,
						}'
						:custom-messages='{
							required: $t("network.connection.ipv6.errors.address"),
							ipv6: $t("network.connection.ipv6.errors.addressInvalid"),
						}'
					>
						<CInput
							v-model='address.address'
							:label='$t("network.connection.ipv6.address").toString()'
							:is-valid='touched ? valid : null'
							:invalid-feedback='errors.join(", ")'
						>
							<template #prepend-content>
								<span
									class='text-success'
									@click='addAddress'
								>
									<FontAwesomeIcon :icon='["far", "plus-square"]' size='xl' />
								</span>
							</template>
							<template #append-content>
								<span
									v-if='connection.ipv6.addresses.length > 1'
									class='text-danger'
									@click='deleteAddress(index)'
								>
									<FontAwesomeIcon :icon='["far", "trash-alt"]' size='xl' />
								</span>
							</template>
						</CInput>
					</ValidationProvider>
				</CCol>
				<CCol sm='12' lg='4'>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						:rules='{
							required: connection.ipv6.method === ConfigurationMethod.MANUAL,
							between: {
								enabled: connection.ipv6.method === ConfigurationMethod.MANUAL,
								min: 48,
								max: 128,
							}
						}'
						:custom-messages='{
							required: $t("network.connection.ipv6.errors.prefix"),
							between: $t("network.connection.ipv6.errors.prefixInvalid"),
						}'
					>
						<CInput
							v-model.number='address.prefix'
							type='number'
							min='48'
							max='128'
							:label='$t("network.connection.ipv6.prefix").toString()'
							:is-valid='touched ? valid : null'
							:invalid-feedback='errors.join(", ")'
						/>
					</ValidationProvider>
				</CCol>
			</CRow>
			<ValidationProvider
				v-slot='{errors, touched, valid}'
				:rules='{
					required: connection.ipv6.method === ConfigurationMethod.MANUAL,
					ipv6: connection.ipv6.method === ConfigurationMethod.MANUAL,
				}'
				:custom-messages='{
					required: $t("network.connection.ipv6.errors.gateway"),
					ipv6: $t("network.connection.ipv6.errors.addressInvalid"),
				}'
			>
				<CInput
					v-model='connection.ipv6.gateway'
					:label='$t("network.connection.ipv6.gateway").toString()'
					:is-valid='touched ? valid : null'
					:invalid-feedback='errors.join(", ")'
				/>
			</ValidationProvider><hr>
			<div
				v-for='(address, index) in connection.ipv6.dns'
				:key='index+"a"'
			>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					:rules='{
						required: connection.ipv6.method === ConfigurationMethod.MANUAL,
						ipv6: connection.ipv6.method === ConfigurationMethod.MANUAL,
					}'
					:custom-messages='{
						required: $t("network.connection.ipv6.errors.dns"),
						ipv6: $t("network.connection.ipv6.errors.addressInvalid"),
					}'
				>
					<CInput
						v-model='address.address'
						:label='$t("network.connection.ipv6.dns.address").toString()'
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
								v-if='connection.ipv6.dns.length > 1'
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

import {CCol, CInput, CRow, CSelect} from '@coreui/vue/src';
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';
import ip from 'ip-regex';
import {extend, ValidationProvider} from 'vee-validate';
import {between, required} from 'vee-validate/dist/rules';

import {ConfigurationMethod} from '@/enums/Network/Ip';

import {IOption} from '@/interfaces/Coreui';
import {IConnection} from '@/interfaces/Network/Connection';

/**
 * IPv6 configuration options
 */
@Component({
	components: {
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
export default class IPv6Configuration extends Vue {

	/**
	 * Edited connection.
	 */
	@VModel({required: true}) connection!: IConnection;


	/**
	 * Initializes validation rules
	 */
	protected created(): void {
		extend('between', between);
		extend('ipv6', (address: string) => {
			return ip.v6({exact: true}).test(address);
		});
		extend('required', required);
	}

	/**
	 * Computes array of CoreUI select options for IPv6 configuration method
	 * @returns {Array<IOption>} Configuration method options
	 */
	get methods(): Array<IOption> {
		// const methods = ['auto', 'dhcp', 'disabled', 'ignore', 'link-local', 'manual', 'shared'];
		const methods = ['auto', 'dhcp', 'manual', 'shared'];
		return methods.map((method: string) =>
			({
				value: method,
				label: this.$t(`network.connection.ipv6.methods.${method}`).toString(),
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
