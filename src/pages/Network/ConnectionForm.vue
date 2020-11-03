<template>
	<div>
		<h1>{{ $t('network.ethernet.edit') }}</h1>
		<CCard body-wrapper>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm @submit.prevent='handleSubmit'>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{required: "network.connection.form.messages.instance"}'
					>
						<CInput
							v-model='configuration.name'
							:label='$t("network.connection.name")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<fieldset>
						<legend>{{ $t('network.connection.ipv4.title') }}</legend>
						<ValidationProvider
							v-slot='{ errors, touched, valid}'
							rules='required'
							:custom-messages='{required: "network.connection.ipv4.messages.method"}'
						>
							<CSelect
								:value.sync='configuration.ipv4.method'
								:label='$t("network.connection.ipv4.method")'
								:options='ipv4Methods'
								:placeholder='$t("network.connection.ipv4.methods.null")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<div
							v-for='(address, index) in configuration.ipv4.addresses'
							:key='index'
						>
							<ValidationProvider
								v-slot='{ errors, touched, valid}'
								rules='required'
								:custom-messages='{required: "network.connection.ipv4.messages.address"}'
							>
								<CInput
									v-model='address.address'
									:label='$t("network.connection.ipv4.address")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{ errors, touched, valid}'
								rules='required'
								:custom-messages='{required: "network.connection.ipv4.messages.mask"}'
							>
								<CInput
									v-model='address.mask'
									:label='$t("network.connection.ipv4.mask")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<div class='form-group'>
								<CButton color='danger' @click='deleteIpv4Address(index)'>
									{{ $t('network.connection.ipv4.addresses.remove') }}
								</CButton>
								<CButton
									v-if='index === (configuration.ipv4.addresses.length - 1)'
									color='success'
									@click='addIpv4Address'
								>
									{{ $t('network.connection.ipv4.addresses.add') }}
								</CButton>
							</div>
						</div>
						<div class='form-group'>
							<CButton
								v-if='configuration.ipv4.addresses.length === 0'
								color='success'
								@click='addIpv4Address'
							>
								{{ $t('network.connection.ipv4.addresses.add') }}
							</CButton>
						</div>
						<ValidationProvider
							v-slot='{ errors, touched, valid}'
							:rules='configuration.ipv4.method === "manual" ? "required" : ""'
							:custom-messages='{required: "network.connection.ipv4.messages.gateway"}'
						>
							<CInput
								v-model='configuration.ipv4.gateway'
								:label='$t("network.connection.ipv4.gateway")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<div v-for='(address, index) in configuration.ipv4.dns' :key='index'>
							<ValidationProvider
								v-slot='{ errors, touched, valid}'
								rules='required'
								:custom-messages='{required: "network.connection.ipv4.messages.dns"}'
							>
								<CInput
									v-model='address.address'
									:label='$t("network.connection.ipv4.dns.address")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<div class='form-group'>
								<CButton color='danger' @click='deleteIpv4Dns(index)'>
									{{ $t('network.connection.ipv4.dns.remove') }}
								</CButton>
								<CButton
									v-if='index === (configuration.ipv4.dns.length - 1)'
									color='success'
									@click='addIpv4Dns'
								>
									{{ $t('network.connection.ipv4.dns.add') }}
								</CButton>
							</div>
						</div>
						<div v-if='configuration.ipv4.dns.length === 0' class='form-group'>
							<CButton color='success' @click='addIpv4Dns'>
								{{ $t('network.connection.ipv4.dns.add') }}
							</CButton>
						</div>
					</fieldset>
					<fieldset>
						<legend>{{ $t('network.connection.ipv6.title') }}</legend>
						<ValidationProvider
							v-slot='{ errors, touched, valid}'
							rules='required'
							:custom-messages='{required: "network.connection.ipv6.messages.method"}'
						>
							<CSelect
								:value.sync='configuration.ipv6.method'
								:label='$t("network.connection.ipv6.method")'
								:options='ipv6Methods'
								:placeholder='$t("network.connection.ipv6.methods.null")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<div
							v-for='(address, index) in configuration.ipv6.addresses'
							:key='index'
						>
							<ValidationProvider
								v-slot='{ errors, touched, valid}'
								rules='required'
								:custom-messages='{required: "network.connection.ipv6.messages.address"}'
							>
								<CInput
									v-model='address.address'
									:label='$t("network.connection.ipv6.address")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{ errors, touched, valid}'
								rules='required'
								:custom-messages='{required: "network.connection.ipv6.messages.prefix"}'
							>
								<CInput
									v-model.number='address.prefix'
									:label='$t("network.connection.ipv6.prefix")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{ errors, touched, valid}'
							>
								<CInput
									v-model='address.gateway'
									:label='$t("network.connection.ipv6.gateway")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<div class='form-group'>
								<CButton
									color='danger'
									@click='deleteIpv6Address(index)'
								>
									{{ $t('network.connection.ipv6.addresses.remove') }}
								</CButton> <CButton
									v-if='index === (configuration.ipv6.addresses.length - 1)'
									color='success'
									@click='addIpv6Address'
								>
									{{ $t('network.connection.ipv6.addresses.add') }}
								</CButton>
							</div>
						</div>
						<div class='form-group'>
							<CButton
								v-if='configuration.ipv6.addresses.length === 0'
								color='success'
								@click='addIpv6Address'
							>
								{{ $t('network.connection.ipv6.addresses.add') }}
							</CButton>
						</div>
						<div
							v-for='(address, index) in configuration.ipv6.dns'
							:key='index'
						>
							<ValidationProvider
								v-slot='{ errors, touched, valid}'
								rules='required'
								:custom-messages='{required: "network.connection.ipv6.messages.dns"}'
							>
								<CInput
									v-model='address.address'
									:label='$t("network.connection.ipv6.dns.address")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<div class='form-group'>
								<CButton
									color='danger'
									@click='deleteIpv6Dns(index)'
								>
									{{ $t('network.connection.ipv6.dns.remove') }}
								</CButton> <CButton
									v-if='index === (configuration.ipv6.dns.length - 1)'
									color='success'
									@click='addIpv6Dns'
								>
									{{ $t('network.connection.ipv6.dns.add') }}
								</CButton>
							</div>
						</div>
						<div v-if='configuration.ipv6.dns.length === 0' class='form-group'>
							<CButton color='success' @click='addIpv6Dns'>
								{{ $t('network.connection.ipv6.dns.add') }}
							</CButton>
						</div>
					</fieldset>
					<CButton type='submit' color='primary' :disabled='invalid'>
						{{ submitButton }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {CButton, CCard, CForm, CInput, CSelect} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import NetworkConnectionService, {ConnectionType} from '../../services/NetworkConnectionService';
import { IOption } from '../../interfaces/coreui';
import {AxiosError, AxiosResponse} from 'axios';

interface IpConfigAddress {
	address: string
	mask: string
	prefix?: number
}

interface IpConfigDns {
	address: string
}

interface IpConfig {
	addresses: Array<IpConfigAddress>
	dns: Array<IpConfigDns>
	gateway?: string
	method: string
}

interface ConnectionConfig {
	name?: string
	type?: string
	uuid?: string
	interface?: string
	ipv4: IpConfig
	ipv6: IpConfig
}

@Component({
	components: {
		CButton,
		CCard,
		CForm,
		CInput,
		CSelect,
		ValidationObserver,
		ValidationProvider,
	},
	metaInfo: {
		title: 'network.ethernet.edit',
	}
})

/**
 * Connection form component for Network page
 */
export default class ConnectionForm extends Vue {
	/**
	 * @var {ConnectionConfig} configuration Configuration of IPv4 and IPv6 connectivity
	 */
	private configuration: ConnectionConfig = {
		ipv4: {
			addresses: [],
			dns: [],
			gateway: '',
			method: '',
		},
		ipv6: {
			addresses: [],
			dns: [],
			method: '',
		}
	}

	/**
	 * @property {string} uuid Network connection configuration id
	 */
	@Prop({required: false, default: null}) uuid!: string

	/**
	 * Computes array of CoreUI select options for IPv4 configuration method
	 * @returns {Array<IOption>} Configuration method options
	 */
	get ipv4Methods(): Array<IOption> {
		const methods = ['auto', 'disabled', 'link-local', 'manual', 'shared'];
		return methods.map(
			(method) => ({
				value: method,
				label: this.$t('network.connection.ipv4.methods.' + method).toString(),
			})
		);
	}

	/**
	 * Computes array of CoreUI select options for IPv6 configuration method
	 * @returns {Array<IOption>} Configuration method options
	 */
	get ipv6Methods(): Array<IOption> {
		const methods = [
			'auto', 'disabled', 'dhcp', 'ignore', 'link-local', 'manual', 'shared',
		];
		return methods.map((method: string) =>
			({
				value: method,
				label: this.$t('network.connection.ipv6.methods.' + method).toString(),
			})
		);
	}

	/**
	 * Computes form submit button text depending on the action (add, edit)
	 * @return {string} Button text
	 */
	get submitButton(): string {
		return this.$route.path === '/network/add' ?
			this.$t('forms.add').toString() : this.$t('forms.save').toString();
	}

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		this.$store.commit('spinner/SHOW');
		extend('required', required);
		NetworkConnectionService.get(this.uuid)
			.then((response: AxiosResponse) => {
				this.configuration = response.data;
				this.$store.commit('spinner/HIDE');
			});
	}

	/**
	 * Adds a new IPv4 address object to configuration
	 */
	private addIpv4Address(): void {
		this.configuration.ipv4.addresses.push({address: '', mask: ''});
	}

	/**
	 * Adds a new IPv4 dns object to configuraiton
	 */
	private addIpv4Dns(): void {
		this.configuration.ipv4.dns.push({address: ''});
	}

	/**
	 * Removes an IPv4 address object specified by index
	 * @param {number} index Index of address object
	 */
	private deleteIpv4Address(index: number): void {
		this.configuration.ipv4.addresses.splice(index, 1);
	}

	/**
	 * Removes an IPv4 dns object specified by index
	 * @param {number} index Index of dns object
	 */
	private deleteIpv4Dns(index: number): void {
		this.configuration.ipv4.dns.splice(index, 1);
	}

	/**
	 * Adds a new IPv6 address object to configuration
	 */
	private addIpv6Address(): void {
		this.configuration.ipv6.addresses.push({address: '', mask: ''});
	}

	/**
	 * Adds a new IPv6 dns object to configuraiton
	 */
	private addIpv6Dns(): void {
		this.configuration.ipv6.dns.push({address: ''});
	}

	/**
	 * Removes an IPv6 address object specified by index
	 * @param {number} index Index of address object
	 */
	private deleteIpv6Address(index: number): void {
		this.configuration.ipv6.addresses.splice(index, 1);
	}

	/**
	 * Removes an IPv6 dns object specified by index
	 * @param {number} index Index of dns object
	 */
	private deleteIpv6Dns(index: number): void {
		this.configuration.ipv6.dns.splice(index, 1);
	}

	/**
	 * Updates existing network connection settings and establishes connection
	 */
	private handleSubmit(): void {
		this.$store.commit('spinner/SHOW');
		NetworkConnectionService.edit(this.uuid, this.configuration)
			.then(() => {
				NetworkConnectionService.connect(this.uuid)
					.then(() => {
						if (this.configuration.type === ConnectionType.ETHERNET) {
							this.$router.push('/network/ethernet');
						}
						this.$toast.success(
							this.$t('network.connection.messages.edit.success', {connection: this.configuration.name}).toString()
						);
						this.$store.commit('spinner/HIDE');
					})
					.catch(this.handleError);
			}).catch(this.handleError);
	}

	/**
	 * Handle submit errors
	 */
	private handleError(): void {
		this.$toast.error(
			this.$t('network.connection.messages.edit.failure').toString()
		);
		this.$store.commit('spinner/HIDE');
	}

}
</script>
