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
		<h1>{{ pageTitle }}</h1>
		<v-card class='mb-5'>
			<v-card-text>
				<ValidationObserver v-slot='{invalid}'>
					<v-form>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("network.connection.errors.name"),
							}'
						>
							<v-text-field
								v-model='connection.name'
								:label='$t("network.connection.name").toString()'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<div v-if='interfaceType !== null && connection.interface !== undefined'>
							<GsmModemInput
								v-if='interfaceType === NetworkInterfaceType.GSM'
								v-model='connection.interface'
								@input='detectSerial'
							/>
							<InterfaceInput
								v-else
								v-model='connection.interface'
								:type='interfaceType'
							/>
						</div>
						<v-switch
							v-model='connection.autoConnect.enabled'
							:label='$t("network.connection.autoConnect")'
							color='primary'
							inset
							dense
						/>
						<WiFiConfiguration v-if='connection.wifi' v-model='connection' :ap='ap' />
						<div
							v-if='connection.gsm'
							:key='refresh'
						>
							<h5>{{ $t('network.mobile.form.title') }}</h5>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required|apn'
								:custom-messages='{
									required: $t("network.mobile.errors.apnMissing"),
									apn: $t("network.mobile.errors.apnInvalid"),
								}'
							>
								<v-text-field
									v-model='connection.gsm.apn'
									:label='$t("network.mobile.form.apn").toString()'
									:success='touched ? valid : null'
									:error-messages='errors'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='pin'
								:custom-messages='{
									pin: $t("network.mobile.errors.pinInvalid"),
								}'
							>
								<PasswordInput
									v-model='connection.gsm.pin'
									:label='$t("network.mobile.form.pin").toString()'
									:success='touched ? valid : null'
									:error-messages='errors'
								/>
							</ValidationProvider>
							<v-row>
								<v-col>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										:rules='{
											required: connection.gsm.password.length > 0
										}'
										:custom-messages='{
											required: $t("network.mobile.errors.credentialsMissing"),
										}'
									>
										<v-text-field
											v-model='connection.gsm.username'
											:label='$t("forms.fields.username").toString()'
											:success='touched ? valid : null'
											:error-messages='errors'
										/>
									</ValidationProvider>
								</v-col>
								<v-col>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										:rules='{
											required: connection.gsm.username.length > 0
										}'
										:custom-messages='{
											required: $t("network.mobile.errors.credentialsMissing"),
										}'
									>
										<PasswordInput
											v-model='connection.gsm.password'
											:label='$t("forms.fields.password").toString()'
											:success='touched ? valid : null'
											:error-messages='errors'
										/>
									</ValidationProvider>
								</v-col>
							</v-row>
						</div>
						<SerialConfiguration v-if='connection.serial' v-model='connection' />
						<v-row v-if='interfaceType'>
							<v-col cols='12'>
								<v-alert v-if='disabledBothIpStacks' type='error' text>
									{{ $t('network.connection.messages.disabledBothIpStacks') }}
								</v-alert>
							</v-col>
							<v-col cols='12' md='6'>
								<h5>{{ $t('network.connection.ipv4.title') }}</h5>
								<IPv4Configuration v-model='connection' />
							</v-col>
							<v-col cols='12' md='6'>
								<h5>{{ $t('network.connection.ipv6.title') }}</h5>
								<IPv6Configuration v-model='connection' :disabled='hasBrokenGsmModem' />
							</v-col>
						</v-row>
						<v-btn
							class='mr-1'
							color='primary'
							:disabled='invalid || !ipv4InSubnet || disabledBothIpStacks'
							@click.prevent='prepareModal(true)'
						>
							{{ $t('network.connection.saveAndConnect') }}
						</v-btn>
						<v-btn
							color='secondary'
							:disabled='invalid || !ipv4InSubnet || disabledBothIpStacks'
							@click.prevent='prepareModal(false)'
						>
							{{ $t('forms.save') }}
						</v-btn>
					</v-form>
				</ValidationObserver>
			</v-card-text>
		</v-card>
		<v-card
			v-if='interfaceType === NetworkInterfaceType.GSM'
		>
			<v-card-text>
				<NetworkOperators
					:allow-set='true'
					@set-operator='updateGsm'
				/>
			</v-card-text>
		</v-card>
		<v-dialog
			v-model='showModal'
			width='50%'
			persistent
			no-click-animation
		>
			<v-card>
				<v-card-title>
					{{ $t('network.connection.modal.title') }}
				</v-card-title>
				<v-card-text>
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
					{{ $t('network.connection.modal.confirmPrompt') }}
				</v-card-text>
				<v-card-actions>
					<v-spacer />
					<v-btn
						class='mr-1'
						@click='showModal = false'
					>
						{{ $t('forms.cancel') }}
					</v-btn>
					<v-btn
						color='warning'
						@click='saveConnection'
					>
						{{ $t('forms.save') }}
					</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>
	</div>
</template>

<script lang='ts'>
import axios, {AxiosError} from 'axios';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import {MetaInfo} from 'vue-meta';
import {Component, Prop, Vue} from 'vue-property-decorator';
import {v4 as uuidv4} from 'uuid';

import GsmModemInput from '@/components/Network/Connection/GsmModemInput.vue';
import InterfaceInput from '@/components/Network/Connection/InterfaceInput.vue';
import IPv4Configuration from '@/components/Network/Connection/IPv4Configuration.vue';
import IPv6Configuration from '@/components/Network/Connection/IPv6Configuration.vue';
import PasswordInput from '@/components/Core/PasswordInput.vue';
import SerialConfiguration from '@/components/Network/Connection/SerialConfiguration.vue';
import WiFiConfiguration from '@/components/Network/Connection/WiFiConfiguration.vue';
import NetworkOperators from '@/components/Network/NetworkOperators.vue';

import {extendedErrorToast} from '@/helpers/errorToast';
import {apn, pin} from '@/helpers/validationRules/Network';
import IpAddressHelper from '@/helpers/IpAddressHelper';
import {sleep} from '@/helpers/sleep';
import UrlBuilder from '@/helpers/urlBuilder';

import {
	IConnectionModal,
} from '@/interfaces/Network/Connection';

import {useApiClient} from '@/services/ApiClient';
import {
	MobileOperator,
	IPv4ConfigurationMethod,
	IPv6ConfigurationMethod,
	NetworkConnectionConfiguration,
	NetworkConnectionType,
	AccessPoint,
	WepKeyType,
	NetworkInterfaceType, NetworkConnectionCreated
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';

@Component({
	components: {
		GsmModemInput,
		InterfaceInput,
		IPv4Configuration,
		IPv6Configuration,
		NetworkOperators,
		PasswordInput,
		SerialConfiguration,
		ValidationObserver,
		ValidationProvider,
		WiFiConfiguration,
	},
	data: () => ({
		NetworkInterfaceType,
	}),
	metaInfo(): MetaInfo {
		return {
			title: (this as ConnectionForm).pageTitle
		};
	},
})

export default class ConnectionForm extends Vue {

	/**
	 * @var {NetworkConnectionConfiguration} connection Configuration of IPv4 and IPv6 connectivity
	 */
	private connection: NetworkConnectionConfiguration = {
		autoConnect: {
			enabled: true,
			priority: 0,
			retries: -1,
		},
		interface: '',
		name: '',
		type: null,
		ipv4: {
			addresses: [],
			dns: [],
			gateway: '',
			method: IPv4ConfigurationMethod.AUTO,
		},
		ipv6: {
			addresses: [],
			dns: [],
			gateway: '',
			method: IPv6ConfigurationMethod.AUTO,
		}
	};

	private backupConfig: NetworkConnectionConfiguration|null = null;

	/**
	 * @var {Record<string, string|IPv4ConfigurationMethod>} originalIPv4 IPv4 address and method before change
	 */
	private originalIPv4 = {
		address: '',
		method: IPv4ConfigurationMethod.AUTO,
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
	 * @var {number} refresh Form refresh
	 */
	private refresh = 0;

	/**
	 * @property {string} uuid Network connection configuration id
	 */
	@Prop({required: false, default: null}) uuid!: string|null;

	/**
	 * @property {string|null} ap Access point metadata in JSON string format
	 */
	@Prop({required: false, default: null}) ap!: string|null;

	/**
	 * @property {NetworkConnectionService} service Network connection service
	 */
	private service = useApiClient().getNetworkServices().getNetworkConnectionService();

	/**
	 * @property {boolean} disabledBothIpStacks Are both IP stacks disabled?
	 */
	get disabledBothIpStacks(): boolean {
		return this.connection.ipv4.method === IPv4ConfigurationMethod.DISABLED
			&& this.connection.ipv6.method === IPv6ConfigurationMethod.DISABLED;
	}

	/**
	 * @property {NetworkInterfaceType|null} interfaceType Type of interface
	 */
	get interfaceType(): NetworkInterfaceType|null {
		if (this.$route.path.includes('/ip-network/wireless/')) {
			return NetworkInterfaceType.WIFI;
		} else if (this.$route.path.includes('/ip-network/ethernet/')) {
			return NetworkInterfaceType.ETHERNET;
		} else if (this.$route.path.includes('/ip-network/mobile/')) {
			return NetworkInterfaceType.GSM;
		} else {
			return null;
		}
	}

	/**
	 * Computes page title from URL and interface type
	 * @returns {string} Page title
	 */
	get pageTitle(): string {
		let type = '';
		if (this.connection.type !== undefined) {
			type = this.$t(`network.interface.types.${this.connection.type}`).toString();
		}
		if (this.$route.path.includes('/add')) {
			return this.$t('network.connection.add', {type: type}).toString();
		}
		return this.$t('network.connection.edit', {type: type}).toString();
	}

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('required', required);
		extend('apn', apn);
		extend('pin', pin);
	}

	/**
	 * Fetches connection configuration prop is set
	 */
	mounted(): void {
		this.$store.commit('spinner/SHOW');
		if (this.uuid !== null) {
			this.getConnection();
		} else {
			const ap = JSON.parse(this.ap || '{}') as AccessPoint;
			this.connection.name = ap.ssid;
			this.connection.interface = ap.interfaceName ?? '';
			if (this.interfaceType === NetworkInterfaceType.ETHERNET) {
				this.connection.type = NetworkConnectionType.Ethernet;
			} else if (this.interfaceType === NetworkInterfaceType.WIFI) {
				this.connection.type = NetworkConnectionType.WiFi;
				Object.assign(this.connection, {
					wifi: {
						ssid: ap?.ssid,
						mode: ap?.mode,
						security: {
							type: this.getSecurityType(ap!.security),
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
				if (ap?.security === 'wpa-eap' && this.connection.wifi !== undefined) {
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
			} else if (this.interfaceType === NetworkInterfaceType.GSM) {
				this.connection.type = NetworkConnectionType.GSM;
				Object.assign(this.connection, {
					gsm: {apn: '', pin: '', username: '', password: ''}
				});
			}
			this.storeConnectionData(this.connection);
			this.$store.commit('spinner/HIDE');
		}
	}

	/**
	 * Checks if IPv4 and gateway address are in the same subnet
	 * @returns {boolean} Are addresses in the same subnet?
	 */
	get ipv4InSubnet(): boolean {
		if (this.interfaceType === NetworkInterfaceType.GSM) {
			return true;
		}
		return IpAddressHelper.ipv4ConnectionSubnetCheck(this.connection);
	}

	/**
	 * Get connection specified by prop
	 */
	private getConnection(): void {
		if (this.uuid === null) {
			return;
		}
		this.$store.commit('spinner/SHOW');
		this.service.get(this.uuid)
			.then((response: NetworkConnectionConfiguration) => {
				this.$store.commit('spinner/HIDE');
				this.storeConnectionData(response);
			})
			.catch((error: AxiosError) => {
				console.error(error);
				extendedErrorToast(
					error,
					'network.connection.messages.fetchFailed',
					{connection: this.uuid!}
				);
				if (this.connection.type === NetworkConnectionType.Ethernet) {
					this.$router.push('/ip-network/ethernet');
				} else if (this.connection.type === NetworkConnectionType.WiFi) {
					this.$router.push('/ip-network/wireless');
				} else if (this.connection.type === NetworkConnectionType.GSM) {
					this.$router.push('/ip-network/mobile');
				}
			});
	}

	/**
	 * Returns security type code from type string
	 * @param {string} type security type string
	 * @returns {string} security type code
	 */
	private getSecurityType(type: string): string {
		if (type === 'Open') {
			return 'open';
		} else if (type === 'WEP') {
			return 'wep';
		} else if (['WPA-Personal', 'WPA2-Personal', 'WPA3-Personal'].includes(type)) {
			return 'wpa-psk';
		} else if (['WPA-Enterprise', 'WPA2-Enterprise', 'WPA3-Enterprise'].includes(type)) {
			return 'wpa-eap';
		}
		return '';
	}

	/**
	 * Initializes empty arrays for the form and stores configuration
	 * @param {NetworkConnectionConfiguration} connection Connection details
	 */
	private storeConnectionData(connection: NetworkConnectionConfiguration): void {
		// initialize ipv4 configuration objects
		if (connection.ipv4.method === IPv4ConfigurationMethod.AUTO && connection.ipv4.current) {
			connection.ipv4 = connection.ipv4.current;
			delete connection.ipv4.current;
		}
		this.originalIPv4.address = connection.ipv4.addresses[0]?.address ?? '';
		this.originalIPv4.method = connection.ipv4.method ?? IPv4ConfigurationMethod.AUTO;
		// initialize ipv6 configuration objects
		if (['auto', 'dhcp'].includes(connection.ipv6.method) && connection.ipv6.current) {
			connection.ipv6 = connection.ipv6.current;
			delete connection.ipv6.current;
		}
		this.backupConfig = JSON.parse(JSON.stringify(connection));
		this.connection = connection;
	}

	/**
	 * Checks if connection methods have changed and creates warning notices for user
	 * If methods have not changed, connection is saved immediately
	 */
	private prepareModal(connect: boolean): void {
		const loc = new UrlBuilder();
		if (loc.getHostname() === 'localhost' || loc.getHostname() !== this.originalIPv4.address) {
			// localhost, or frontend accessed at IP that is not in this connection
			this.saveConnection(connect);
			return;
		}
		if (this.originalIPv4.method === IPv4ConfigurationMethod.AUTO && this.connection.ipv4.method === IPv4ConfigurationMethod.AUTO) { // ipv4 method not changed from auto
			this.saveConnection(connect);
			return;
		} else if (this.originalIPv4.method === IPv4ConfigurationMethod.AUTO && this.connection.ipv4.method === IPv4ConfigurationMethod.MANUAL) { // ipv4 method changed from auto to static
			if (this.connection.ipv4.addresses[0].address === this.originalIPv4.address) { // auto to static, but IP hasn't changed
				this.saveConnection(connect);
				return;
			}
			this.modalMessages.ipv4 = this.$t('network.connection.modal.ipv4.autoToStatic').toString();
			this.modalMessages.ipv4Addr = window.location.protocol + '//' +
				this.connection.ipv4.addresses[0].address + loc.getPort();
		} else if (this.originalIPv4.method === IPv4ConfigurationMethod.MANUAL && this.connection.ipv4.method === IPv4ConfigurationMethod.MANUAL) {
			this.modalMessages.ipv4 = this.$t('network.connection.modal.ipv4.staticIpChange').toString();
			this.modalMessages.ipv4Addr = window.location.protocol + '//' +
				this.connection.ipv4.addresses[0].address + loc.getPort();
		} else if (this.originalIPv4.method === IPv4ConfigurationMethod.MANUAL && this.connection.ipv4.method === IPv4ConfigurationMethod.AUTO) { // ipv4 method changed from static to auto
			this.modalMessages.ipv4 = this.$t('network.connection.modal.ipv4.staticToAuto').toString();
		}
		this.handleIPChanged = true;
		this.showModal = true;
	}

	/**
	 * Alters connection JSON for submission
	 * @param {NetworkConnectionConfiguration} connection Connection to prepare
	 * @returns {NetworkConnectionConfiguration} Connection prepared for submission
	 */
	private prepareConnectionToSave(connection: NetworkConnectionConfiguration): NetworkConnectionConfiguration {
		if (connection.ipv4.method === IPv4ConfigurationMethod.MANUAL) {
			for (const idx in connection.ipv4.addresses) {
				delete connection.ipv4.addresses[idx].prefix;
			}
		} else if ([IPv4ConfigurationMethod.AUTO, IPv4ConfigurationMethod.DISABLED, IPv4ConfigurationMethod.SHARED].includes(connection.ipv4.method)) {
			connection.ipv4.addresses = connection.ipv4.dns = [];
			connection.ipv4.gateway = null;
		}
		if ([IPv6ConfigurationMethod.AUTO, IPv6ConfigurationMethod.DISABLED, IPv6ConfigurationMethod.DHCP, IPv6ConfigurationMethod.SHARED].includes(connection.ipv6.method)) {
			connection.ipv6.addresses = connection.ipv6.dns = [];
			connection.ipv6.gateway = null;
		}
		if (connection.wifi?.bssids !== undefined) {
			delete connection.wifi.bssids;
		}
		if (this.connection.interface !== undefined &&
				!/tty(AMA|AMC|S)\d+/.test(this.connection.interface) &&
				this.connection.serial !== undefined) {
			delete connection.serial;
		}
		return connection;
	}

	/**
	 * @property {boolean} hasBrokenGsmModem Checks if the used modem is broken to prevent hanging on
	 */
	get hasBrokenGsmModem(): boolean {
		return this.$store.getters['gateway/board'] === 'MICRORISC s.r.o. IQD-GW04'
				&& this.connection.interface === 'ttyAMA2';
	}

	/**
	 * Restarts ModemManager service to fix broken modem
	 */
	private async restartModemManager(): Promise<void> {
		await useApiClient().getServiceService().restart('ModemManager');
		await new Promise(resolve => setTimeout(resolve, 15_000));
	}

	/**
	 * Saves changes made to connection
	 */
	private saveConnection(connect: boolean): void {
		let connection: NetworkConnectionConfiguration = JSON.parse(JSON.stringify(this.connection));
		Object.assign(connection, {interface: connection.interface});
		connection = this.prepareConnectionToSave(connection);
		if (this.showModal) {
			this.showModal = false;
		}
		this.$store.commit('spinner/SHOW', connect ?
			this.$t('network.connection.messages.saveAndConnect').toString() :
			this.$t('network.connection.messages.save').toString()
		);
		if (this.uuid === null || connection.uuid === undefined) {
			connection.uuid = uuidv4();
			this.service.create(connection)
				.then(async (response: NetworkConnectionCreated) => {
					if (connect) {
						if (this.interfaceType === NetworkInterfaceType.GSM && this.hasBrokenGsmModem) {
							await this.restartModemManager();
						}
						await this.connect(response.uuid, connection.name, true);
					} else {
						this.onSuccess();
					}
				})
				.catch((error: AxiosError) => {
					extendedErrorToast(
						error,
						'network.connection.messages.add.failed'
					);
				});
		} else {
			this.service.update(this.uuid, connection)
				.then(async () => {
					if (connect) {
						if (this.interfaceType === NetworkInterfaceType.GSM && this.hasBrokenGsmModem) {
							await this.restartModemManager();
						}
						await this.connect(this.uuid!, connection.name, false);
					} else {
						this.onSuccess();
					}
				})
				.catch((error: AxiosError) => extendedErrorToast(
					error,
					'network.connection.messages.edit.failed',
					{connection: connection.name}
				));
		}
	}

	/**
	 * Shows success toast and redirects to connection list
	 */
	private onSuccess(): void {
		this.$store.commit('spinner/HIDE');
		let message: string;
		if (this.$route.path.includes('/add')) {
			message = this.$t('network.connection.messages.add.success', {connection: name}).toString();
		} else {
			message = this.$t('network.connection.messages.edit.success', {connection: name}).toString();
		}
		this.$toast.success(message);
		if (this.connection.type === NetworkConnectionType.Ethernet) {
			this.$router.push('/ip-network/ethernet');
		} else if (this.connection.type === NetworkConnectionType.WiFi) {
			this.$router.push('/ip-network/wireless');
		} else if (this.connection.type === NetworkConnectionType.GSM) {
			this.$router.push('/ip-network/mobile');
		}
	}

	/**
	 * @param {string} uuid Connection UUID
	 * @param {string} name Connection name
	 * @param {boolean} added Connection added before connecting
	 */
	private async connect(uuid: string, name: string, added = false): Promise<void> {
		await this.service.connect(uuid, this.connection.interface ?? null)
			.then(() => {this.onSuccess();})
			.catch(async (error: AxiosError) => {
				if (!this.handleIPChanged) {
					if (added) {
						await this.service.delete(uuid);
					} else if (this.backupConfig !== null) {
						await this.service.update(uuid, this.prepareConnectionToSave(this.backupConfig));
					}
					extendedErrorToast(error, 'network.connection.messages.connect.failed', {connection: name});
					return;
				}
				if (this.originalIPv4.method === IPv4ConfigurationMethod.AUTO && this.connection.ipv4.method === IPv4ConfigurationMethod.MANUAL) {
					await this.tryRest('network.connection.messages.ipChange.autoToStatic');
				} else if (this.originalIPv4.method === IPv4ConfigurationMethod.MANUAL && this.connection.ipv4.method === IPv4ConfigurationMethod.MANUAL) {
					await this.tryRest('network.connection.messages.ipChange.staticToStatic');
				} else if (this.originalIPv4.method === IPv4ConfigurationMethod.MANUAL && this.connection.ipv4.method === IPv4ConfigurationMethod.AUTO) {
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
		useApiClient().getVersionService().getWebapp()
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

	/**
	 * Updates GSM connection object
	 * @param {MobileOperator} operator Network operator
	 */
	private updateGsm(operator: MobileOperator): void {
		if (this.connection.gsm === undefined) {
			return;
		}
		this.connection.gsm.apn = operator.apn;
		this.connection.gsm.username = operator.username ?? '';
		this.connection.gsm.password = operator.password ?? '';
		this.refresh++;
	}

	/**
	 * Detects need for serial link configuration
	 */
	private detectSerial(): void {
		if (this.connection.interface === undefined) {
			return;
		}
		if (/tty(AMA|AMC|S)\d+/.test(this.connection.interface) && this.connection.serial === undefined) {
			const serial: SerialConfiguration = {baudRate: 115200, bits: 8, parity: 'n', stopBits: 1, sendDelay: 0};
			Object.assign(this.connection, {serial: serial});
		}
		if (this.hasBrokenGsmModem) {
			this.connection.ipv6.method = IPv6ConfigurationMethod.DISABLED;
		}
	}

}
</script>
