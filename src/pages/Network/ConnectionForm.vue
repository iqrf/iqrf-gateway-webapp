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
		<h1>{{ $t('network.connection.edit') }}</h1>
		<CCard>
			<CCardBody>
				<ValidationObserver v-slot='{invalid}'>
					<CForm @submit.prevent='prepareModal'>
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
								<legend>{{ $t('network.wireless.form.title') }}</legend>
								<div class='form-group'>
									<b>
										<span>{{ $t('network.wireless.form.security') }}</span>
									</b> {{ $t('network.wireless.form.securityTypes.' + connection.wifi.security.type) }}
								</div>
								<div
									v-if='connection.wifi.security.type === "ieee8021x"'
									class='form-group'
								>
									<CInput
										v-model='connection.wifi.security.leap.username'
										:label='$t("network.wireless.form.username")'
									/>
									<CInput
										v-model='connection.wifi.security.leap.password'
										:label='$t("network.wireless.form.password")'
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
											required: "network.wireless.errors.wepKeyType",
											wepKeyType: "network.wireless.errors.wepKeyType"
										}'
									>
										<CSelect
											:value.sync='connection.wifi.security.wep.type'
											:options='wepKeyOptions'
											:label='$t("network.wireless.form.wep.type")'
											:placeholder='$t("network.wireless.errors.wepKeyType")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										/>
									</ValidationProvider>
									<CSelect
										v-if='connection.wifi.security.wep.type === "key"'
										:value.sync='wepLen'
										:options='wepLenOptions'
										:label='$t("network.wireless.form.wep.length")'
									/>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='required|integer|between:0,3|wepIndex'
										:custom-messages='{
											required: "network.wireless.errors.wepIndex",
											integer: "forms.errors.integer",
											between: "network.wireless.errors.wepIndexInvalid",
											wepIndex: "network.wireless.errors.wepIndexKeyMissing"
										}'
									>
										<CInput
											v-model.number='connection.wifi.security.wep.index'
											type='number'
											min='0'
											max='3'
											:label='$t("network.wireless.form.wep.index")'
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
												"network.wireless.errors.wepKey64Invalid":
												"network.wireless.errors.wepKey128Invalid"
										}'
									>
										<CInput							
											v-model='connection.wifi.security.wep.keys[index]'
											:label='$t("network.wireless.form.wep.keyNum", {index: index})'
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
										required: "network.wireless.errors.psk",
										wpaPsk: "network.wireless.errors.pskInvalid"
									}'
								>
									<CInput
										v-model='connection.wifi.security.psk'
										:type='pskInputType'
										visibility
										:label='$t("network.wireless.form.psk")'
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
										:label='$t("network.wireless.form.authPhaseOne")'
									/>
									<CInput
										v-model='connection.wifi.security.eap.anonymousIdentity'
										:label='$t("network.wireless.form.anonymousIdentity")'
									/>
									<CInput
										v-model='connection.wifi.security.eap.cert'
										:label='$t("network.wireless.form.caCert")'
										:disabled='connection.wifi.security.eap.noCert'
									/>
									<CSelect
										:value.sync='connection.wifi.security.eap.phaseTwo'
										:options='authTwoOptions'
										:label='$t("network.wireless.form.authPhaseTwo")'
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
											:label='$t("network.wireless.form.username")'
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
											:label='$t("network.wireless.form.password")'
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
										:label='$t("network.connection.ipv4.method")'
										:placeholder='$t("network.connection.ipv4.methods.null")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
									/>
								</ValidationProvider>
								<div v-if='connection.ipv4.method === "manual"'>
									<hr>
									<CAlert
										v-if='!ipv4InSubnet'
										color='danger'
									>
										{{ $t('network.connection.ipv4.ipNotInSubnet') }}
									</CAlert>
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
											:rules='connection.ipv4.method === "manual" ? "required|ipv4|dns4Subnet" : ""'
											:custom-messages='{
												required: "network.connection.ipv4.errors.dns",
												ipv4: "network.connection.ipv4.errors.addressInvalid",
												dns4Subnet: "network.connection.ipv4.dnsNotInSubnet"
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
												v-if='connection.ipv4.dns.length > 1'
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
												v-if='connection.ipv6.addresses.length > 1'
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
												v-if='connection.ipv6.dns.length > 1'
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
							:disabled='invalid || !ipv4InSubnet'
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
			size='lg'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('network.connection.modal.title') }}
				</h5>
			</template>
			<div>
				<span v-if='modalMessages.ipv4 !== ""'>
					{{ modalMessages.ipv4 }}
				</span>
				<a
					v-if='modalMessages.ipv4Add !== ""'
					style='color: blue; cursor: pointer;'
					:href='modalMessages.ipv4Addr'
					target='_blank'
				>
					{{ modalMessages.ipv4Addr }}
				</a>
			</div>
			{{ $t('network.connection.modal.confirmPrompt') }}
			<template #footer>
				<CButton
					color='warning'
					@click='saveConnection'
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

import {sleep} from '../../helpers/sleep';
import {between, integer, required} from 'vee-validate/dist/rules';
import {extendedErrorToast} from '../../helpers/errorToast';
import {v4 as uuidv4} from 'uuid';
import {WepKeyLen, WepKeyType} from '../../enums/Network/WifiSecurity';

import ip from 'ip-regex';
import NetworkConnectionService, {ConnectionType} from '../../services/NetworkConnectionService';
import NetworkInterfaceService, {InterfaceType} from '../../services/NetworkInterfaceService';
import VersionService from '../../services/VersionService';

import axios, {AxiosError, AxiosResponse} from 'axios';
import {IConnection, IConnectionModal, NetworkInterface} from '../../interfaces/network';
import {IOption} from '../../interfaces/coreui';
import UrlBuilder from '../../helpers/urlBuilder';

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

export default class ConnectionForm extends Vue {

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
			label: this.$t('network.wireless.form.phaseOneAlgorithm.peap'),
			value: 'peap'
		},
	]

	/**
	 * @constant {Array<IOption>} authTwoOptions CoreUI EAP phase two authentication options
	 */
	private authTwoOptions: Array<IOption> = [
		{
			label: this.$t('network.wireless.form.phaseTwoAlgorithm.mschapv2'),
			value: 'mschapv2'
		},
	]

	/**
	 * @constant {Array<IOption>} wepKeyOptions CoreUI wep key type select options
	 */
	private wepKeyOptions: Array<IOption> = [
		{
			label: this.$t('network.wireless.form.wep.types.key'),
			value: WepKeyType.KEY
		},
		{
			label: this.$t('network.wireless.form.wep.types.passphrase'),
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
			label: this.$t('network.wireless.form.wep.lengths.64bit'),
			value: WepKeyLen.BIT64,
		},
		{
			label: this.$t('network.wireless.form.wep.lengths.128bit'),
			value: WepKeyLen.BIT128,
		},
	]

	/**
	 * @var {string} pskInputType WPA pre-shared key input type
	 */
	private pskInputType = 'password'

	/**
	 * @var {Dictionary<string>} originalIPv4 IPv4 address and method before change
	 */
	private originalIPv4 = {
		address: '',
		method: 'auto',
	}

	/**
	 * @var {bool} handleIPChanged Controls whether or not to check for IP changes if connect fails
	 */
	private handleIPChanged = false

	/**
	 * @var {IConnectionModal} modalMessages Modal IP method change messages
	 */
	private modalMessages: IConnectionModal = {
		ipv4: '',
		ipv4Addr: '',
	}

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
			return this.connection.wifi?.security.wep.keys[index] !== '';
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
		extend('dns4Subnet', (address: string) => {
			return this.ipv4SubnetCheck(address);
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
	 * Checks if ipv4 and gateway address are in the same subnet
	 * @returns {boolean} Are addreses in the same subnet?
	 */
	get ipv4InSubnet(): boolean {
		if (this.connection.ipv4.method === 'auto') {
			return true;
		}
		const ipv4 = this.connection.ipv4.addresses[0].address;
		return this.ipv4SubnetCheck(ipv4);
	}

	/**
	 * Checks if passed IP is in subnet with gateway
	 * @param {string} address Address to check
	 * @returns {boolean} Is address in the same subnet as gateway?
	 */
	private ipv4SubnetCheck(address: string): boolean {
		const mask = this.connection.ipv4.addresses[0].mask;
		const gateway = this.connection.ipv4.gateway;
		if (gateway === null) {
			return false;
		}
		if (!ip.v4({exact: true}).test(address) ||
			!ip.v4({exact: true}).test(mask) ||
			!ip.v4({exact: true}).test(gateway)) {
			return true;
		}
		const addressInt = this.ipv4ToInt(address);
		const maskInt = this.ipv4ToInt(mask);
		const gatewayInt = this.ipv4ToInt(gateway);
		return ((addressInt & maskInt) === (gatewayInt & maskInt));
	}

	/**
	 * Converts dot representation of ip to integer value
	 * @param {string} address IPv4 address string
	 * @returns {number} integer value of ipv4
	 */
	private ipv4ToInt(address: string): number {
		return address.split('.').reduce((acc, oct) => {return acc * 256 + parseInt(oct, 10);}, 0) >>> 0;
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
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'network.connection.messages.interfacesFetchFailed');
				if (iftype === InterfaceType.ETHERNET) {
					this.$router.push('/network/ethernet');
				} else if (iftype === InterfaceType.WIFI) {
					this.$router.push('/network/wireless');
				}
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
			.catch((error: AxiosError) => {
				extendedErrorToast(
					error,
					'network.connection.messages.fetchFailed',
					{connection: this.uuid}
				);
				if (this.connection.type === ConnectionType.ETHERNET) {
					this.$router.push('/network/ethernet');
				} else if (this.connection.type === ConnectionType.WIFI) {
					this.$router.push('/network/wireless');
				}
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
		this.originalIPv4.address = connection.ipv4.addresses[0].address;
		this.originalIPv4.method = connection.ipv4.method;
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
		const loc = new UrlBuilder();
		if (loc.getHostname() === 'localhost' || loc.getHostname() !== this.originalIPv4.address) {
			// localhost, or frontend accessed at IP that is not in this connection
			this.saveConnection();
			return;
		}
		if (this.originalIPv4.method === 'auto' && this.connection.ipv4.method === 'auto') { // ipv4 method not changed from auto
			this.saveConnection();
			return;
		} else if (this.originalIPv4.method === 'auto' && this.connection.ipv4.method === 'manual') { // ipv4 method changed from auto to static
			if (this.connection.ipv4.addresses[0].address === this.originalIPv4.address) { // auto to static, but IP hasn't changed
				this.saveConnection();
				return;
			}
			this.modalMessages.ipv4 = this.$t('network.connection.modal.ipv4.autoToStatic').toString();
			this.modalMessages.ipv4Addr = window.location.protocol + '//' +
				this.connection.ipv4.addresses[0].address + loc.getPort();
		} else if (this.originalIPv4.method === 'manual' && this.connection.ipv4.method === 'manual') {
			this.modalMessages.ipv4 = this.$t('network.connection.modal.ipv4.staticIpChange').toString();
			this.modalMessages.ipv4Addr = window.location.protocol + '//' +
				this.connection.ipv4.addresses[0].address + loc.getPort();
		} else if (this.originalIPv4.method === 'manual' && this.connection.ipv4.method === 'auto') { // ipv4 method changed from static to auto
			this.modalMessages.ipv4 = this.$t('network.connection.modal.ipv4.staticToAuto').toString();
		}
		this.handleIPChanged = true;
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
		if (this.showModal) {
			this.showModal = false;
		}
		this.$store.commit('spinner/SHOW',
			this.$t('network.connection.messages.submit').toString()
		);
		if (connection.uuid === undefined) {
			connection.uuid = uuidv4();
			NetworkConnectionService.add(connection)
				.then((response: AxiosResponse) => this.connect(response.data, connection.name))
				.catch((error: AxiosError) => extendedErrorToast(
					error,
					'network.connection.messages.add.failed'
				));
		} else {
			NetworkConnectionService.edit(this.uuid, connection)
				.then(() => this.connect(this.uuid, connection.name))
				.catch((error: AxiosError) => extendedErrorToast(
					error,
					'network.connection.messages.edit.failed',
					{connection: connection.name}
				));
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
			.catch((error: AxiosError) => {
				if (!this.handleIPChanged) {
					extendedErrorToast(error, 'network.connection.messages.connect.failed', {connection: name});
					return;
				}
				if (this.originalIPv4.method === 'auto' && this.connection.ipv4.method === 'manual') {
					this.tryRest('network.connection.messages.ipChange.autoToStatic');
				} else if (this.originalIPv4.method === 'manual' && this.connection.ipv4.method === 'manual') {
					this.tryRest('network.connection.messages.ipChange.staticToStatic');
				} else if (this.originalIPv4.method === 'manual' && this.connection.ipv4.method === 'auto') {
					this.$store.commit('spinner/HIDE');
					this.$store.commit('blocking/SHOW', this.$t('network.connection.messages.ipChange.staticToAuto').toString());
				}
			});
	}

	/**
	 * Attempt a REST API request to check newly selected IP address
	 * @param {string} message Message to show in spinner
	 */
	private async tryRest(message: string): Promise<void> {
		const loc = new UrlBuilder();
		axios.defaults.baseURL = loc.getRestApiUrlFromAddr(this.connection.ipv4.addresses[0].address);
		this.$store.commit('spinner/UPDATE_TEXT',
			this.$t('network.connection.messages.ipChange.backendCheck').toString()
		);
		await sleep(10000);
		VersionService.getWebappVersionRest()
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$store.commit('blocking/SHOW',
					this.$t(message, {address: window.location.protocol + '//' + this.connection.ipv4.addresses[0].address + loc.getPort()}).toString()
				);	
			})
			.catch(() => {
				this.$store.commit('spinner/HIDE');
				this.$store.commit('blocking/SHOW', this.$t('network.connection.messages.ipChange.error').toString());
			});
	}


	// form input control functions

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

}
</script>
