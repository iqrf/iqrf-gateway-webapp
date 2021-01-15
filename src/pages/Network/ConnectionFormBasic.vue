<template>
	<div>
		<h1>{{ $t('network.ethernet.' + ($route.path === '/network/add' ? 'add' : 'edit')) }}</h1>
		<CCard>
			<CCardBody>
				<ValidationObserver v-slot='{invalid}'>
					<CForm @submit.prevent='saveConnection'>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required'
							:custom-messages='{
								required: "network.connection.errors.name"
							}'
						>
							<CInput
								v-model='connection.name'
								:label='$t("network.connection.name")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<CRow>
							<CCol>
								<legend>{{ $t('network.connection.ipv4.title') }}</legend>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: "network.connection.ipv4.errors.method"
									}'
								>
									<CSelect
										:value.sync='connection.ipv4.method'
										:label='$t("network.connection.ipv4.method")'
										:options='ipv4Methods'
										:placeholder='$t("network.connection.ipv4.methods.null")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
									/>
								</ValidationProvider>
								<fieldset v-if='connection.ipv4.method === "manual"'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										:rules='connection.ipv4.method === "manual" ? "required|ipv4" : ""'
										:custom-messages='{
											required: "network.connection.ipv4.errors.address",
											ipv4: "network.connection.ipv4.errors.addressInvalid"
										}'
									>
										<CInput
											v-model='connection.ipv4.addresses[0].address'
											:label='$t("network.connection.ipv4.address")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										/>
									</ValidationProvider>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										:rules='connection.ipv4.method === "manual" ? "required|ipv4|netmask" : ""'
										:custom-messages='{
											required: "network.connection.ipv4.errors.mask",
											ipv4: "network.connection.ipv4.errors.addressInvalid",
											netmask: "network.connection.ipv4.errors.maskInvalid"
										}'
									>
										<CInput
											v-model='connection.ipv4.addresses[0].mask'
											:label='$t("network.connection.ipv4.mask")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										/>
									</ValidationProvider>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										:rules='connection.ipv4.method === "manual" ? "required|ipv4" : ""'
										:custom-messages='{
											required: "network.connection.ipv4.errors.gateway",
											ipv4: "network.connection.ipv4.errors.addressInvalid"
										}'
									>
										<CInput
											v-model='connection.ipv4.gateway'
											:label='$t("network.connection.ipv4.gateway")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										/>
									</ValidationProvider>
									<div v-for='(dns, index) in connection.ipv4.dns' :key='index'>
										<ValidationProvider
											v-slot='{errors, touched, valid}'
											:rules='connection.ipv4.method === "manual" ? "required|ipv4" : ""'
											:custom-messages='{
												required: "network.connection.ipv4.errors.dns",
												ipv4: "network.connection.ipv4.errors.addressInvalid",
											}'
										>
											<CInput
												v-model='dns.address'
												:label='$t("network.connection.ipv4.dns.address")'
												:is-valid='touched ? valid : null'
												:invalid-feedback='$t(errors[0])'
											/>
										</ValidationProvider>
										<div class='form-group'>
											<CButton
												v-if='index !== 0'
												color='danger'
												@click='deleteIpv4Dns(index)'
											>
												{{ $t('network.connection.ipv4.dns.remove') }}
											</CButton> <CButton
												v-if='index === (connection.ipv4.dns.length - 1)'
												color='success'
												@click='addIpv4Dns'
											>
												{{ $t('network.connection.ipv4.dns.add') }}
											</CButton>
										</div>
									</div>
								</fieldset>
							</CCol>
							<CCol>
								<legend>{{ $t('network.connection.ipv6.title') }}</legend>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: "network.connection.ipv6.errors.method"
									}'
								>
									<CSelect
										:value.sync='connection.ipv6.method'
										:label='$t("network.connection.ipv6.method")'
										:options='ipv6Methods'
										:placeholder='$t("network.connection.ipv6.methods.null")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
									/>
								</ValidationProvider>
								<fieldset v-if='connection.ipv6.method === "manual"'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										:rules='connection.ipv6.method === "manual" ? "required":""'
										:custom-messages='{
											required: "network.connection.ipv6.errors.address"
										}'
									>
										<CInput
											v-model='connection.ipv6.addresses[0].address'
											:label='$t("network.connection.ipv6.address")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										/>
									</ValidationProvider>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										:rules='connection.ipv6.method === "manual" ? "required":""'
										:custom-messages='{
											required: "network.connection.ipv6.errors.prefix"
										}'
									>
										<CInput
											v-model.number='connection.ipv6.addresses[0].prefix'
											:label='$t("network.connection.ipv6.prefix")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										/>
									</ValidationProvider>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										:rules='connection.ipv6.method === "manual" ? "required":""'
										:custom-messages='{
											required: "network.connection.ipv6.errors.gateway"
										}'
									>
										<CInput
											v-model='connection.ipv6.addresses[0].gateway'
											:label='$t("network.connection.ipv6.gateway")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										/>
									</ValidationProvider>
									<div v-for='(dns, index) in connection.ipv6.dns' :key='index'>
										<ValidationProvider
											v-slot='{errors, touched, valid}'
											rules='required'
											:custom-messages='{
												required: "network.connection.ipv6.errors.dns"
											}'
										>
											<CInput
												v-model='dns.address'
												:label='$t("network.connection.ipv6.dns.address")'
												:is-valid='touched ? valid : null'
												:invalid-feedback='$t(errors[0])'
											/>
										</ValidationProvider>
										<div class='form-group'>
											<CButton
												v-if='index !== 0'
												color='danger'
												@click='deleteIpv6Dns(index)'
											>
												{{ $t('network.connection.ipv6.dns.remove') }}
											</CButton> <CButton
												v-if='index === (connection.ipv6.dns.length - 1)'
												color='success'
												@click='addIpv6Dns'
											>
												{{ $t('network.connection.ipv6.dns.add') }}
											</CButton>
										</div>
									</div>
								</fieldset>
							</CCol>
						</CRow>
						<CButton
							type='submit'
							color='primary'
							:disabled='invalid'
						>
							{{ $t('forms.save') }}
						</CButton>
					</CForm>
				</ValidationObserver>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CForm, CInput, CSelect} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import NetworkConnectionService, {ConnectionType} from '../../services/NetworkConnectionService';
import {IConnection} from '../../interfaces/network';
import {AxiosResponse} from 'axios';
import {IOption} from '../../interfaces/coreui';
import ip from 'ip-regex';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
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

export default class ConnectionFormBasic extends Vue {

	/**
	 * @var {IConnection} connection Configuration of IPv4 and IPv6 connectivity
	 */
	private connection: IConnection = {
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
	 * @var {boolean} powerUser Indicates that user is a power user
	 */
	private powerUser = false

	/**
	 * @property {string} uuid Network connection configuration id
	 */
	@Prop({required: false, default: null}) uuid!: string

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('required', required);
		extend('ipv4', (address: string) => {
			return ip.v4({exact: true}).test(address);
		});
		extend('netmask', (mask: string) => {
			const maskTokens = mask.split('.');
			let binaryMask = '';
			maskTokens.forEach((token: string) => {
				binaryMask += parseInt(token).toString(2).padEnd(8, '0');
			});
			return new RegExp(/^1{8,30}0{2,24}$/).test(binaryMask);
		});
		extend('ipv6', (address: string) => {
			return ip.v6({exact: true}).test(address);
		});
	}

	/**
	 * Fetches connection configuration prop is set
	 */
	mounted(): void {
		if (this.$store.getters['user/getRole'] === 'power') {
			this.powerUser = true;
		}
		if (this.uuid !== null) {
			this.getConnection();
		}
	}

	/**
	 * Computes array of CoreUI select options for IPv4 configuration method
	 * @returns {Array<IOption>} Configuration method options
	 */
	get ipv4Methods(): Array<IOption> {
		const methods = this.powerUser ?
			['auto', 'link-local', 'manual', 'shared']:
			['auto', 'manual'];
		let methodOptions: Array<IOption> = methods.map(
			(method: string) => ({
				value: method,
				label: this.$t('network.connection.ipv4.methods.' + method).toString(),
			})
		);
		if (this.powerUser) {
			methodOptions.push({
				value: 'disabled',
				label: this.$t('states.disabled').toString()
			});
		}
		return methodOptions;
	}

	/**
	 * Computes array of CoreUI select options for IPv6 configuration method
	 * @returns {Array<IOption>} Configuration method options
	 */
	get ipv6Methods(): Array<IOption> {
		const methods = this.powerUser ?
			['auto', 'dhcp', 'ignore', 'link-local', 'manual', 'shared']:
			['auto', 'dhcp', 'manual'];
		let methodOptions: Array<IOption> = methods.map((method: string) =>
			({
				value: method,
				label: this.$t('network.connection.ipv6.methods.' + method).toString(),
			})
		);
		if (this.powerUser) {
			methodOptions.push({
				value: 'disabled',
				label: this.$t('states.disabled').toString()
			});
		}
		return methodOptions;
	}

	/**
	 * Computes page title
	 * @returns {string} Page title
	 */
	get pageTitle(): string {
		return this.$t(
			'network.ethernet.' + 
			(this.$route.path === '/network/add/' ? 'add' : 'edit')
		).toString();
	}

	/**
	 * Adds a new IPv4 dns object to configuraiton
	 */
	private addIpv4Dns(): void {
		this.connection.ipv4.dns.push({address: ''});
	}

	/**
	 * Removes an IPv4 dns object specified by index
	 * @param {number} index Index of dns object
	 */
	private deleteIpv4Dns(index: number): void {
		this.connection.ipv4.dns.splice(index, 1);
	}

	/**
	 * Adds a new IPv6 dns object to configuraiton
	 */
	private addIpv6Dns(): void {
		this.connection.ipv6.dns.push({address: ''});
	}

	/**
	 * Removes an IPv6 dns object specified by index
	 * @param {number} index Index of dns object
	 */
	private deleteIpv6Dns(index: number): void {
		this.connection.ipv6.dns.splice(index, 1);
	}

	/**
	 * Get connection specified by prop
	 */
	private getConnection(): void {
		this.$store.commit('spinner/SHOW');
		NetworkConnectionService.get(this.uuid)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.storeConnectionData(response.data);
			})
			.catch(() => {
				if (this.connection.type === ConnectionType.ETHERNET) {
					this.$router.push('/network/ethernet');
				}
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t('network.connection.messages.loadFailed').toString()
				);
			});
	}

	/**
	 * Initializes empty arrays for the form and stores configuration
	 * @param {IConnection} connection Connection details
	 */
	private storeConnectionData(connection: IConnection): void {
		if (connection.ipv4.method === 'auto') {
			if (connection.current?.ipv4) {
				connection.ipv4 = connection.current.ipv4;
			} else {
				connection.ipv4.addresses.push({address: '', prefix: 32, mask: ''});
				connection.ipv4.dns.push({address: ''});
			}
		}
		if (connection.ipv6.method === 'auto') {
			if (connection.current?.ipv6) {
				connection.ipv6 = connection.current.ipv6;
			} else {
				connection.ipv6.addresses.push({address: '', prefix: 128, gateway: ''});
				connection.ipv6.dns.push({address: ''});
			}
		}
		if (connection.current) {
			delete connection.current;
		}
		this.connection = connection;
	}

	/**
	 * Saves changes made to connection
	 */
	private saveConnection(): void {
		let connection: IConnection = JSON.parse(JSON.stringify(this.connection));
		if (connection.ipv4.method === 'auto') {
			connection.ipv4.addresses = connection.ipv4.dns = [];
			connection.ipv4.gateway = null;
		}
		if (connection.ipv6.method === 'auto' || connection.ipv6.method === 'dhcp') {
			connection.ipv6.addresses = connection.ipv6.dns = [];
		}
		this.$store.commit('spinner/SHOW');
		NetworkConnectionService.edit(this.uuid, connection)
			.then(() => {
				NetworkConnectionService.connect(this.uuid)
					.then(() => {
						if (this.connection.type === ConnectionType.ETHERNET) {
							this.$router.push('/network/ethernet');
						}
						this.$toast.success(
							this.$t('network.connection.messages.edit.success', {connection: connection.name}).toString()
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
