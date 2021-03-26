<template>
	<div>
		<h1>{{ $t('network.connection.edit') }}</h1>
		<CCard>
			<CCardBody>
				<ValidationObserver v-slot='{invalid}'>
					<CForm @submit.prevent='saveConnection'>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
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
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: "network.connection.errors.interface"
							}'
						>
							<CSelect
								:value.sync='ifname'
								:label='$t("network.connection.interface")'
								:placeholder='$t("network.connection.errors.interface")'
								:options='ifnameOptions'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<CRow v-if='connection.type === "802-11-wireless"'>
							<CCol md='6'>
								<legend>{{ $t('network.wireless.modal.title') }}</legend>
								<div class='form-group'>
									<b>
										<span>{{ $t('network.wireless.modal.form.security') }}</span>
									</b> {{ $t('network.wireless.modal.form.securityTypes.' + connection.wifi.security.type) }}
								</div>
								<div
									v-if='connection.wifi.security.type === "ieee8021x"'
									class='form-group'
								>
									<CInput
										v-model='connection.wifi.security.leap.username'
										:label='$t("network.wireless.modal.form.username")'
									/>
									<CInput
										v-model='connection.wifi.security.leap.password'
										:label='$t("network.wireless.modal.form.password")'
									/>
								</div>
								<div 
									v-else-if='connection.wifi.security.type === "wep"'
									class='form-group'
								>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='required|wepKeyType'
										:custom-messages='{
											required: "network.wireless.modal.errors.wepKeyType",
											wepKeyType: "network.wireless.modal.errors.wepKeyType"
										}'
									>
										<CSelect
											:value.sync='connection.wifi.security.wep.type'
											:options='wepKeyOptions'
											:label='$t("network.wireless.modal.form.wep.type")'
											:placeholder='$t("network.wireless.modal.errors.wepKeyType")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										/>
									</ValidationProvider>
									<CSelect
										v-if='connection.wifi.security.wep.type === "key"'
										:value.sync='wepLen'
										:options='wepLenOptions'
										:label='$t("network.wireless.modal.form.wep.length")'
									/>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='required|integer|between:0,3|wepIndex'
										:custom-messages='{
											required: "network.wireless.modal.errors.wepIndex",
											integer: "forms.errors.integer",
											between: "network.wireless.modal.errors.wepIndexInvalid",
											wepIndex: "network.wireless.modal.errors.wepIndexKeyMissing"
										}'
									>
										<CInput
											v-model.number='connection.wifi.security.wep.index'
											type='number'
											min='0'
											max='3'
											:label='$t("network.wireless.modal.form.wep.index")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										/>
									</ValidationProvider>
									<ValidationProvider
										v-for='(key, index) of connection.wifi.security.wep.keys'
										:key='index'
										v-slot='{errors, touched, valid}'
										:rules='connection.wifi.security.wep.type === "key" ? "wepKey" : ""'
										:custom-messages='{
											wepKey: wepLen === "64bit" ?
												"network.wireless.modal.errors.wepKey64Invalid":
												"network.wireless.modal.errors.wepKey128Invalid"
										}'
									>
										<CInput							
											v-model='connection.wifi.security.wep.keys[index]'
											:label='$t("network.wireless.modal.form.wep.keyNum", {index: index})'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										/>
									</ValidationProvider>
								</div>
								<ValidationProvider
									v-else-if='connection.wifi.security.type === "wpa-psk"'
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
												<FontAwesomeIcon
													:icon='(pskInputType === "password" ? ["far", "eye"] : ["far", "eye-slash"])'
												/>
											</span>
										</template>
									</CInput>
								</ValidationProvider>
								<div
									v-if='connection.wifi.security.type === "wpa-eap"'
									class='form-group'
								>
									<CSelect
										:value.sync='connection.wifi.security.eap.phaseOne'
										:options='authOneOptions'
										:label='$t("network.wireless.modal.form.authPhaseOne")'
									/>
									<CInput
										v-model='connection.wifi.security.eap.anonymousIdentity'
										:label='$t("network.wireless.modal.form.anonymousIdentity")'
									/>
									<CInput
										v-model='connection.wifi.security.eap.cert'
										:label='$t("network.wireless.modal.form.caCert")'
										:disabled='connection.wifi.security.eap.noCert'
									/>
									<CSelect
										:value.sync='connection.wifi.security.eap.phaseTwo'
										:options='authTwoOptions'
										:label='$t("network.wireless.modal.form.authPhaseTwo")'
									/>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='required'
										:custom-messages='{
											required: "forms.errors.username"
										}'
									>
										<CInput
											v-model='connection.wifi.security.eap.identity'
											:label='$t("network.wireless.modal.form.username")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										/>
									</ValidationProvider>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='required'
										:custom-messages='{
											required: "forms.errors.password"
										}'
									>
										<CInput
											v-model='connection.wifi.security.eap.password'
											:label='$t("network.wireless.modal.form.password")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										/>
									</ValidationProvider>
								</div>
							</CCol>
						</CRow>
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
										id='ipv4MethodSelect'
										:value.sync='connection.ipv4.method'
										:options='ipv4Methods'
										:placeholder='$t("network.connection.ipv4.methods.null")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
										@change='ipv4MethodChanged = true'
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
										@change='ipv6MethodChanged'
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
		<CModal
			:show.sync='showModal'
			color='warning'
		>
			<template #header>
				<h5 class='modal-title'>
					test title
				</h5>
			</template>
			test prompt
			<template #footer>
				<CButton
					color='warning'
				>
					{{ $t('forms.save') }}
				</CButton> <CButton
					color='secondary'
					@click='showModal = false'
				>
					{{ $t('forms.cancel') }}
				</CButton>
			</template>
		</CModal>
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {CBadge, CButton, CCard, CCardBody, CForm, CInput, CModal, CSelect} from '@coreui/vue/src';
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required, integer, between} from 'vee-validate/dist/rules';

import {v4 as uuidv4} from 'uuid';
import ip from 'ip-regex';
import NetworkConnectionService, {ConnectionType} from '../../services/NetworkConnectionService';
import NetworkInterfaceService, {InterfaceType} from '../../services/NetworkInterfaceService';

import {AxiosResponse} from 'axios';
import {IConnection, NetworkInterface} from '../../interfaces/network';
import {IOption} from '../../interfaces/coreui';

enum WepKeyLen {
	BIT64 = '64bit',
	BIT128 = '128bit',
}

enum WepKeyType {
	KEY = 'key',
	PASSPHRASE = 'passphrase',
	UNKNOWN = 'unknown',
}

@Component({
	components: {
		CBadge,
		CButton,
		CCard,
		CCardBody,
		CForm,
		CInput,
		CModal,
		CSelect,
		FontAwesomeIcon,
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
		autoConnect: {
			enabled: true,
			priority: 0,
			retries: -1,
		},
		name: '',
		type: '',
		ipv4: {
			addresses: [],
			dns: [],
			gateway: '',
			method: 'auto',
		},
		ipv6: {
			addresses: [],
			dns: [],
			gateway: '',
			method: 'auto',
		}
	}

	/**
	 * @var {string} ifname Interface name
	 */
	private ifname = ''

	/**
	 * @var {Array<IOption>} ifnameOptions Array of CoreUI interface options
	 */
	private ifnameOptions: Array<IOption> = []

	/**
	 * @var {boolean} powerUser Indicates that user is a power user
	 */
	private powerUser = false

	/**
	 * @constant {Array<IOption>} authOneOptions CoreUI EAP phase one authentication options
	 */
	private authOneOptions: Array<IOption> = [
		{
			label: this.$t('network.wireless.modal.form.phaseOneAlgorithm.peap'),
			value: 'peap'
		},
	]

	/**
	 * @constant {Array<IOption>} authTwoOptions CoreUI EAP phase two authentication options
	 */
	private authTwoOptions: Array<IOption> = [
		{
			label: this.$t('network.wireless.modal.form.phaseTwoAlgorithm.mschapv2'),
			value: 'mschapv2'
		},
	]

	/**
	 * @constant {Array<IOption>} wepKeyOptions CoreUI wep key type select options
	 */
	private wepKeyOptions: Array<IOption> = [
		{
			label: this.$t('network.wireless.modal.form.wep.types.key'),
			value: WepKeyType.KEY
		},
		{
			label: this.$t('network.wireless.modal.form.wep.types.passphrase'),
			value: WepKeyType.PASSPHRASE
		},
	]

	/**
	 * @var {WepKeyLen} wepLen WEP key length
	 */
	private wepLen = WepKeyLen.BIT64

	/**
	 * @constant {Array<IOption>} wepLenOptions CoreUI wep key length select options
	 */
	private wepLenOptions: Array<IOption> = [
		{
			label: this.$t('network.wireless.modal.form.wep.lengths.64bit'),
			value: WepKeyLen.BIT64,
		},
		{
			label: this.$t('network.wireless.modal.form.wep.lengths.128bit'),
			value: WepKeyLen.BIT128,
		},
	]

	/**
	 * @var {string} pskInputType WPA pre-shared key input type
	 */
	private pskInputType = 'password'

	/**
	 * @var {boolean} ipv4MethodChanged Indicates that ipv4 method has been changed
	 */
	private ipv4MethodChanged = false

	/**
	 * @var {boolean} ipv6MethodChanged Indicates that ipv6 method has been changed
	 */
	private ipv6MethodChanged = false

	/**
	 * @var {boolean} showModal Show confirmation modal?
	 */
	private showModal = false

	/**
	 * @property {string} uuid Network connection configuration id
	 */
	@Prop({required: false, default: null}) uuid!: string

	/**
	 * @property {string} ssid Network connection name
	 */
	@Prop({required: false, default: null}) ssid!: string

	/**
	 * @property {string} interfaceName Default interface name
	 */
	@Prop({required: false, default: null}) interfaceName!: string

	/**
	 * @property {string} wifiModel Wifi mode
	 */
	@Prop({required: false, default: null}) wifiMode!: string

	/**
	 * @property {string} wifiSecurity Wifi security type
	 */
	@Prop({required: false, default: null}) wifiSecurity!: string

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
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
		extend('wepIndex', (index: number) => {
			return this.connection.wifi!.security.wep.keys[index] !== '';
		});
		extend('wepKey', (key: string) => {
			if (this.wepLen === WepKeyLen.BIT64) {
				return new RegExp(/^(\w{5}|[0-9a-fA-F]{10})$/).test(key);
			}
			return new RegExp(/^(\w{13}|[0-9a-fA-F]{26})$/).test(key);
		});
		extend('wepKeyType', (key: string) => {
			return key !== WepKeyType.UNKNOWN;
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
		if (this.$route.path.includes('/network/wireless/')) {
			this.getInterfaces(InterfaceType.WIFI);
		} else if (this.$route.path.includes('/network/ethernet/')) {
			this.getInterfaces(InterfaceType.ETHERNET);
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
	 * Retrieves interfaces of a type
	 * @param {InterfaceType} iftype Interface type
	 */
	private getInterfaces(iftype: InterfaceType): void {
		this.$store.commit('spinner/SHOW');
		NetworkInterfaceService.list(iftype)
			.then((response: AxiosResponse) => {
				let interfaces: Array<IOption> = [];
				response.data.forEach((item: NetworkInterface) => {
					interfaces.push({label: item.name, value: item.name});
				});
				this.ifnameOptions = interfaces;
				if (this.uuid !== null) {
					this.getConnection();
				} else {
					this.connection.name = this.ssid;
					this.connection.interface = this.interfaceName;
					if (iftype === InterfaceType.ETHERNET) {
						this.connection.type = ConnectionType.ETHERNET;
					} else if (iftype === InterfaceType.WIFI) {
						this.connection.type = ConnectionType.WIFI;
						Object.assign(this.connection, {
							wifi: {
								ssid: this.ssid,
								mode: this.wifiMode,
								security: {
									type: this.wifiSecurity,
									psk: '',
									leap: {
										username: '',
										password: ''
									},
									wep: {
										type: WepKeyType.UNKNOWN,
										index: 0,
										keys: ['', '', '', '']
									}
								}
							}
						});
						if (this.wifiSecurity === 'wpa-eap') {
							Object.assign(this.connection.wifi?.security, {
								eap: {
									phaseOne: '',
									anonymousIdentity: '',
									cert: '',
									phaseTwo: '',
									identity: '',
									password: '',
								}
							});
						}
					}
					this.storeConnectionData(this.connection);
					this.$store.commit('spinner/HIDE');
				}
			})
			.catch(() => {
				if (iftype === InterfaceType.ETHERNET) {
					this.$router.push('/network/ethernet');
				} else if (iftype === InterfaceType.WIFI) {
					this.$router.push('/network/wireless');
				}
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t('network.connection.messages.interfacesFetchFailed').toString()
				);
			});
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
				} else if (this.connection.type === ConnectionType.WIFI) {
					this.$router.push('/network/wireless');
				}
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t('network.connection.messages.connectionFetchFailed').toString()
				);
			});
	}

	/**
	 * Initializes empty arrays for the form and stores configuration
	 * @param {IConnection} connection Connection details
	 */
	private storeConnectionData(connection: IConnection): void {
		// initialize ipv4 configuration objects
		if (connection.interface) {
			this.ifname = connection.interface;
		}
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
			connection.ipv6.addresses.push({address: '', prefix: 64});
		}
		if (connection.ipv6.dns.length === 0) {
			connection.ipv6.dns.push({address: ''});
		}
		this.connection = connection;
	}

	/**
	 * Checks if connection methods have changed and creates warning notices for user
	 * If methods have not changed, connection is saved immediately
	 */
	private prepareModal(): void {
		if (!this.ipv4MethodChanged && !this.ipv6MethodChanged) {
			this.saveConnection();
			return;
		}
		this.showModal = true;
	}

	/**
	 * Saves changes made to connection
	 */
	private saveConnection(): void {
		let connection: IConnection = JSON.parse(JSON.stringify(this.connection));
		Object.assign(connection, {interface: this.ifname});
		if (connection.ipv4.method === 'manual') {
			for (const idx in connection.ipv4.addresses) {
				delete connection.ipv4.addresses[idx].prefix;
			}
		} else if (connection.ipv4.method === 'auto') {
			connection.ipv4.addresses = connection.ipv4.dns = [];
			connection.ipv4.gateway = null;
		}
		if (connection.ipv6.method === 'auto' || connection.ipv6.method === 'dhcp') {
			connection.ipv6.addresses = connection.ipv6.dns = [];
			connection.ipv6.gateway = null;
		}
		if (connection.wifi?.bssids !== undefined) {
			delete connection.wifi.bssids;
		}
		this.$store.commit('spinner/SHOW');
		if (connection.uuid === undefined) {
			connection.uuid = uuidv4();
			NetworkConnectionService.add(connection)
				.then((response: AxiosResponse) => this.connect(response.data, connection.name!))
				.catch(this.handleConnectionError);
		} else {
			NetworkConnectionService.edit(this.uuid, connection)
				.then(() => this.connect(this.uuid, connection.name!))
				.catch(this.handleConnectionError);
		}
	}

	/**
	 * @param {string} uuid Connection UUID
	 * @param {string} name Connection name
	 */
	private connect(uuid: string, name: string): void {
		NetworkConnectionService.connect(uuid)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'network.connection.messages.' + 
						(this.$route.path.includes('/add') ? 'add' : 'edit') + '.success',
						{connection: name}).toString()
				);
				if (this.connection.type === ConnectionType.ETHERNET) {
					this.$router.push('/network/ethernet');
				} else if (this.connection.type === ConnectionType.WIFI) {
					this.$router.push('/network/wireless');
				}
				
			})
			.catch(this.handleConnectError);
	}

	/**
	 * Handles connection create or edit errors
	 */
	private handleConnectionError(): void {
		this.$store.commit('spinner/HIDE');
		this.$toast.error(
			this.$t('network.connection.messages.' + 
			(this.$route.path.includes('/add') ? 'add' : 'edit') + '.failure').toString()
		);
	}

	/**
	 * Handle connect errors
	 */
	private handleConnectError(): void {
		this.$store.commit('spinner/HIDE');
		this.$toast.error(
			this.$t('network.connection.messages.edit.failure').toString()
		);
	}

}
</script>
