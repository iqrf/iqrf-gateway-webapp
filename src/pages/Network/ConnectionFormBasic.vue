<template>
	<div>
		<h1>{{ $t('network.connection.edit') }}</h1>
		<CCard>
			<CCardBody>
				<ValidationObserver v-slot='{invalid}'>
					<CForm @submit.prevent='saveConnection'>
						<ValidationProvider
							v-slot='{errors, touched, valid }'
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
							<CCol md='6'>
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
								<div v-if='connection.ipv4.method === "manual"'>
									<hr>
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
									<hr>
									<div
										v-for='(address, index) in connection.ipv4.dns'
										:key='index'
									>
										<ValidationProvider
											v-slot='{errors, touched, valid}'
											:rules='connection.ipv4.method === "manual" ? "required|ipv4" : ""'
											:custom-messages='{
												required: "network.connection.ipv4.errors.dns",
												ipv4: "network.connection.ipv4.errors.addressInvalid",
											}'
										>
											<CInput
												v-model='address.address'
												:label='$t("network.connection.ipv4.dns.address")'
												:is-valid='touched ? valid : null'
												:invalid-feedback='$t(errors[0])'
											/>
										</ValidationProvider>
										<div class='form-group'>
											<CButton
												v-if='index > 0'
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
								</div>
							</CCol>
							<CCol md='6'>
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
								<div v-if='connection.ipv6.method === "manual"'>
									<div
										v-for='(address, index) in connection.ipv6.addresses'
										:key='index'
									>
										<hr>
										<ValidationProvider
											v-slot='{errors, touched, valid}'
											:rules='connection.ipv6.method === "manual" ? "required|ipv6":""'
											:custom-messages='{
												required: "network.connection.ipv6.errors.address",
												ipv6: "network.connection.ipv6.errors.addressInvalid"
											}'
										>
											<CInput
												v-model='address.address'
												:label='$t("network.connection.ipv6.address")'
												:is-valid='touched ? valid : null'
												:invalid-feedback='$t(errors[0])'
											/>
										</ValidationProvider>
										<ValidationProvider
											v-slot='{errors, touched, valid}'
											:rules='connection.ipv6.method === "manual" ? "required|between:48,128":""'
											:custom-messages='{
												required: "network.connection.ipv6.errors.prefix",
												between: "network.connection.ipv6.errors.prefixInvalid"
											}'
										>
											<CInput
												v-model.number='address.prefix'
												type='number'
												min='48'
												max='128'
												:label='$t("network.connection.ipv6.prefix")'
												:is-valid='touched ? valid : null'
												:invalid-feedback='$t(errors[0])'
											/>
										</ValidationProvider>
										<div class='form-group'>
											<CButton
												v-if='index > 0'
												color='danger'
												@click='deleteIpv6Address(index)'
											>
												{{ $t('network.connection.ipv6.addresses.remove') }}
											</CButton> <CButton
												v-if='index === (connection.ipv6.addresses.length - 1)'
												color='success'
												@click='addIpv6Address'
											>
												{{ $t('network.connection.ipv6.addresses.add') }}
											</CButton>
										</div>
									</div><hr>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										:rules='connection.ipv6.method === "manual" ? "required|ipv6":""'
										:custom-messages='{
											required: "network.connection.ipv6.errors.gateway",
											ipv6: "network.connection.ipv6.errors.addressInvalid"
										}'
									>
										<CInput
											v-model='connection.ipv6.gateway'
											:label='$t("network.connection.ipv6.gateway")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										/>
									</ValidationProvider><hr>
									<div
										v-for='(address, index) in connection.ipv6.dns'
										:key='index+"a"'
									>
										<ValidationProvider
											v-slot='{errors, touched, valid}'
											:rules='connection.ipv6.method === "manual" ? "required|ipv6":""'
											:custom-messages='{
												required: "network.connection.ipv6.errors.dns",
												ipv6: "network.connection.ipv6.errors.addressInvalid"
											}'
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
												v-if='index > 0'
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
								</div>
							</CCol>
						</CRow>
						<CRow v-if='connection.type === "802-11-wireless"'>
							<CCol md='6'>
								<legend>{{ $t('network.wireless.modal.title') }}</legend>
								<div class='form-group'>
									<b>
										<span>{{ $t('network.wireless.modal.form.security') }}</span>
									</b> {{ $t('network.wireless.modal.form.securityTypes.' + connection.wifi.security.type) }}
								</div>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required|wpaPsk'
									:custom-messages='{
										required: "network.wireless.modal.errors.psk",
										wpaPsk: "network.wireless.modal.errors.pskInvalid"
									}'
								>
									<CInput
										v-model='connection.wifi.security.psk'
										:type='pskInputType'
										visibility
										:label='$t("network.wireless.modal.form.psk")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
									>
										<template #append-content>
											<span @click='pskInputType = pskInputType === "password" ? "text" : "password"'>
												<CIcon :content='pskInputType === "password" ? icons.hidden: icons.shown' />
											</span>
										</template>
									</CInput>
								</ValidationProvider>
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
import {required, between} from 'vee-validate/dist/rules';
import NetworkConnectionService, {ConnectionType} from '../../services/NetworkConnectionService';
import {IConnection} from '../../interfaces/network';
import {AxiosResponse} from 'axios';
import {IOption} from '../../interfaces/coreui';
import ip from 'ip-regex';
import {Dictionary} from 'vue-router/types/router';
import {cilLockLocked, cilLockUnlocked} from '@coreui/icons';

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
		title: 'network.connection.edit',
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
			gateway: '',
			method: '',
		}
	}

	/**
	 * @constant {Dictionary<Array<string>>} icons Dictionary of CoreUI icons
	 */
	private icons: Dictionary<Array<string>> = {
		shown: cilLockUnlocked,
		hidden: cilLockLocked
	}

	/**
	 * @var {boolean} powerUser Indicates that user is a power user
	 */
	private powerUser = false

	/**
	 * @var {string} pskInputType WPA pre-shared key input type
	 */
	private pskInputType = 'password'

	/**
	 * @property {string} uuid Network connection configuration id
	 */
	@Prop({required: false, default: null}) uuid!: string

	/**
	 * @property {string} ifname Network connection interface name
	 */
	@Prop({required: true}) ifname!: string

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('required', required);
		extend('between', between);
		extend('ipv4', (address: string) => {
			return ip.v4({exact: true}).test(address);
		});
		extend('netmask', (mask: string) => {
			const maskTokens = mask.split('.');
			let binaryMask = maskTokens.map((token: string) => {
				return parseInt(token).toString(2).padStart(8, '0');
			}).join('');
			return new RegExp(/^1{8,32}0{0,24}$/).test(binaryMask);
		});
		extend('ipv6', (address: string) => {
			return ip.v6({exact: true}).test(address);
		});
		extend('wpaPsk', (key: string) => {
			return new RegExp(/^(\w{8,63}|[0-9a-fA-F]{64})$/).test(key);
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
	 * Adds a new IPv4 dns object to configuration
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
	 * Adds a new IPv6 address object to configuration
	 */
	private addIpv6Address(): void {
		this.connection.ipv6.addresses.push({address: '', prefix: 64});
	}

	/**
	 * Removes an IPv6 address object specified by index
	 * @param {number} index Index of address object
	 */
	private deleteIpv6Address(index: number): void {
		this.connection.ipv6.addresses.splice(index, 1);
	}

	/**
	 * Adds a new IPv6 dns object to configuration
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
		// initialize ipv4 configuration objects
		if (connection.ipv4.method === 'auto' && connection.ipv4.current) {
			connection.ipv4 = connection.ipv4.current;
			delete connection.ipv4.current;
		}
		if (connection.ipv4.addresses.length === 0) {
			connection.ipv4.addresses.push({address: '', prefix: 32, mask: ''});
		}
		if (connection.ipv4.dns.length === 0) {
			connection.ipv4.dns.push({address: ''});
		}
		// initialize ipv6 configuration objects
		if ((connection.ipv6.method === 'auto' || connection.ipv6.method === 'dhcp') && connection.ipv6.current) {
			connection.ipv6 = connection.ipv6.current;
			delete connection.ipv6.current;
		}
		if (connection.ipv6.addresses.length === 0) {
			connection.ipv6.addresses.push({address: '', prefix: 128, gateway: ''});
		}
		if (connection.ipv6.dns.length === 0) {
			connection.ipv6.dns.push({address: ''});
		}
		this.connection = connection;
	}

	/**
	 * Saves changes made to connection
	 */
	private saveConnection(): void {
		let connection: IConnection = JSON.parse(JSON.stringify(this.connection));
		if (!connection.interface) {
			connection.interface = this.ifname;
		}
		if (connection.ipv4.method === 'manual') {
			const binaryMask = connection.ipv4.addresses[0].mask.split('.').map((token: string) => {
				return parseInt(token).toString(2).padStart(8, '0');
			}).join('');
			connection.ipv4.addresses[0].prefix = (binaryMask.match(/1/g)||[]).length;
		} else if (connection.ipv4.method === 'auto') {
			connection.ipv4.addresses = connection.ipv4.dns = [];
			connection.ipv4.gateway = null;
		}
		if (connection.ipv6.method === 'auto' || connection.ipv6.method === 'dhcp') {
			connection.ipv6.addresses = connection.ipv6.dns = [];
			connection.ipv6.gateway = null;
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
