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
								required: $t("network.connection.errors.name"),
							}'
						>
							<CInput
								v-model='connection.name'
								:label='$t("network.connection.name").toString()'
								:is-valid='touched ? valid : null'
								:invalid-feedback='errors.join(", ")'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("network.connection.errors.interface"),
							}'
						>
							<CSelect
								:value.sync='ifname'
								:label='$t("network.connection.interface").toString()'
								:placeholder='$t("network.connection.errors.interface").toString()'
								:options='ifnameOptions'
								:is-valid='touched ? valid : null'
								:invalid-feedback='errors.join(", ")'
							/>
						</ValidationProvider>
						<WiFiConfiguration v-if='connection.type === "802-11-wireless"' v-model='connection' />
						<CRow>
							<CCol md='6'>
								<legend>{{ $t('network.connection.ipv4.title') }}</legend>
								<IPv4Configuration v-model='connection' />
							</CCol>
							<CCol md='6'>
								<legend>{{ $t('network.connection.ipv6.title') }}</legend>
								<IPv6Configuration v-model='connection' />
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
					v-if='modalMessages.ipv4Addr !== ""'
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
import {CButton, CCard, CCardBody, CCol, CForm, CInput, CModal, CRow, CSelect} from '@coreui/vue/src';
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {required} from 'vee-validate/dist/rules';
import {ConfigurationMethod} from '@/enums/Network/Ip';
import {extendedErrorToast} from '@/helpers/errorToast';
import {sleep} from '@/helpers/sleep';
import {v4 as uuidv4} from 'uuid';
import {WepKeyLen, WepKeyType} from '@/enums/Network/WifiSecurity';
import ip from 'ip-regex';
import UrlBuilder from '@/helpers/urlBuilder';

import NetworkConnectionService, {ConnectionType} from '@/services/NetworkConnectionService';
import NetworkInterfaceService, {InterfaceType} from '@/services/NetworkInterfaceService';
import VersionService from '@/services/VersionService';

import axios, {AxiosError, AxiosResponse} from 'axios';
import {IConnection, IConnectionModal, NetworkInterface} from '@/interfaces/Network/Connection';
import {IOption} from '@/interfaces/Coreui';
import IPv6Configuration from '@/pages/Network/Connection/IPv6Configuration.vue';
import IPv4Configuration from '@/pages/Network/Connection/IPv4Configuration.vue';
import WiFiConfiguration
	from '@/pages/Network/Connection/WiFiConfiguration.vue';
import IpAddressHelper from '@/helpers/IpAddressHelper';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCol,
		CForm,
		CInput,
		CModal,
		CRow,
		CSelect,
		FontAwesomeIcon,
		IPv4Configuration,
		IPv6Configuration,
		ValidationObserver,
		ValidationProvider,
		WiFiConfiguration,
	},
	data: () => ({
		ConfigurationMethod,
		WepKeyLen,
		WepKeyType,
	}),
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
			method: ConfigurationMethod.AUTO,
		},
		ipv6: {
			addresses: [],
			dns: [],
			gateway: '',
			method: ConfigurationMethod.AUTO,
		}
	};

	/**
	 * @var {string} ifname Interface name
	 */
	private ifname = '';

	/**
	 * @var {Array<IOption>} ifnameOptions Array of CoreUI interface options
	 */
	private ifnameOptions: Array<IOption> = [];

	/**
	 * @var {Record<string, string|ConfigurationMethod>} originalIPv4 IPv4 address and method before change
	 */
	private originalIPv4 = {
		address: '',
		method: ConfigurationMethod.AUTO,
	};

	/**
	 * @var {bool} handleIPChanged Controls whether or not to check for IP changes if connect fails
	 */
	private handleIPChanged = false;

	/**
	 * @var {IConnectionModal} modalMessages Modal IP method change messages
	 */
	private modalMessages: IConnectionModal = {
		ipv4: '',
		ipv4Addr: '',
	};

	/**
	 * @var {boolean} showModal Show confirmation modal?
	 */
	private showModal = false;

	/**
	 * @property {string} uuid Network connection configuration id
	 */
	@Prop({required: false, default: null}) uuid!: string;

	/**
	 * @property {string} ssid Network connection name
	 */
	@Prop({required: false, default: null}) ssid!: string;

	/**
	 * @property {string} interfaceName Default interface name
	 */
	@Prop({required: false, default: null}) interfaceName!: string;

	/**
	 * @property {string} wifiModel Wifi mode
	 */
	@Prop({required: false, default: null}) wifiMode!: string;

	/**
	 * @property {string} wifiSecurity Wifi security type
	 */
	@Prop({required: false, default: null}) wifiSecurity!: string;

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('required', required);
	}

	/**
	 * Fetches connection configuration prop is set
	 */
	mounted(): void {
		if (this.$route.path.includes('/ip-network/wireless/')) {
			this.getInterfaces(InterfaceType.WIFI);
		} else if (this.$route.path.includes('/ip-network/ethernet/')) {
			this.getInterfaces(InterfaceType.ETHERNET);
		}
	}

	/**
	 * Checks if IPv4 and gateway address are in the same subnet
	 * @returns {boolean} Are addresses in the same subnet?
	 */
	get ipv4InSubnet(): boolean {
		if (this.connection.ipv4.method === 'auto') {
			return true;
		}
		const address = this.connection.ipv4.addresses[0].address;
		const mask = this.connection.ipv4.addresses[0].mask;
		const gateway = this.connection.ipv4.gateway;
		return IpAddressHelper.ipv4SubnetCheck(address, mask, gateway);
	}

	/**
	 * Retrieves interfaces of a type
	 * @param {InterfaceType} iftype Interface type
	 */
	private getInterfaces(iftype: InterfaceType): void {
		this.$store.commit('spinner/SHOW');
		NetworkInterfaceService.list(iftype)
			.then((response: AxiosResponse) => {
				const interfaces: Array<IOption> = [];
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
						if (this.wifiSecurity === 'wpa-eap' && this.connection.wifi !== undefined) {
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
					this.$router.push('/ip-network/ethernet');
				} else if (iftype === InterfaceType.WIFI) {
					this.$router.push('/ip-network/wireless');
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
					this.$router.push('/ip-network/ethernet');
				} else if (this.connection.type === ConnectionType.WIFI) {
					this.$router.push('/ip-network/wireless');
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
		const connection: IConnection = JSON.parse(JSON.stringify(this.connection));
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
				let message: string;
				if (this.$route.path.includes('/add')) {
					message = this.$t('network.connection.messages.add.success', {connection: name}).toString();
				} else {
					message = this.$t('network.connection.messages.edit.success', {connection: name}).toString();
				}
				this.$toast.success(message);
				if (this.connection.type === ConnectionType.ETHERNET) {
					this.$router.push('/ip-network/ethernet');
				} else if (this.connection.type === ConnectionType.WIFI) {
					this.$router.push('/ip-network/wireless');
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

}
</script>
