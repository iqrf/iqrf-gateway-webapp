<!--
Copyright 2017-2022 IQRF Tech s.r.o.
Copyright 2019-2022 MICRORISC s.r.o.

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
		<CCard body-wrapper>
			<ValidationObserver v-slot='{invalid}'>
				<CForm @submit.prevent='saveTunnel'>
					<legend>{{ $t('network.wireguard.tunnels.form.interface') }}</legend>
					<CRow>
						<CCol md='6'>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required'
								:custom-messages='{
									required: "network.wireguard.tunnels.errors.name"
								}'
							>
								<CInput
									v-model='tunnel.name'
									:label='$t("network.wireguard.tunnels.form.name")'
									placeholder='wg0'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<CButton
								style='float: right;'
								size='sm'
								color='primary'
								@click='generateKeys'
							>
								{{ $t("network.wireguard.tunnels.form.generateKeys") }}
							</CButton>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required|base64Key'
								:custom-messages='{
									required: "network.wireguard.tunnels.errors.privateKey",
									base64Key: "network.wireguard.tunnels.errors.base64Key"
								}'
							>
								<CInput
									v-model='tunnel.privateKey'
									:label='$t("network.wireguard.tunnels.form.privateKey")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<div
								v-if='tunnel.publicKey !== ""'
								class='form-group'
							>
								<label>
									{{ $t('network.wireguard.tunnels.form.publicKeyIface') }}
								</label>
								<CButton
									v-clipboard='updateClipboard'
									v-clipboard:success='successClipboard'
									style='float: right;'
									size='sm'
									color='primary'
								>
									{{ $t("network.wireguard.tunnels.form.copyPublicKey") }}
								</CButton>
								<p>
									{{ tunnel.publicKey }}
								</p>
							</div>
							<CInputCheckbox
								:checked.sync='optionalPort'
								:label='$t("network.wireguard.tunnels.form.portOptional")'
							/>
							<ValidationProvider
								v-if='optionalPort'
								v-slot='{errors, touched, valid}'
								rules='required|integer|between:0,65535'
								:custom-messages='{
									required: "network.wireguard.tunnels.errors.portIface",
									integer: "network.wireguard.tunnels.errors.portInvalid",
									between: "network.wireguard.tunnels.errors.portInvalid"
								}'
							>
								<CInput
									v-model.number='tunnel.port'
									type='number'
									min='0'
									max='65535'
									:label='$t("network.wireguard.tunnels.form.port")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
						</CCol>
						<CCol md='6'>
							<CSelect
								:value.sync='stack'
								:options='stackOptions'
								:label='$t("network.wireguard.tunnels.form.stack")'
							/>
							<CRow>
								<CCol
									v-if='stack === "ipv4" || stack === "both"'
									:md='stack === "ipv4" ? 12 : 6'
								>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='required|ipv4'
										:custom-messages='{
											required: "network.wireguard.tunnels.errors.ipv4",
											ipv4: "network.wireguard.tunnels.errors.ipv4Invalid"
										}'
									>
										<CInput
											v-model='tunnel.ipv4.address'
											:label='$t("network.wireguard.tunnels.form.ipv4")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										/>
									</ValidationProvider>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='required|between:1,32'
										:custom-messages='{
											required: "network.wireguard.tunnels.errors.ipv4Prefix",
											integer: "network.wireguard.tunnels.errors.ipv4PrefixInvalid",
											between: "network.wireguard.tunnels.errors.ipv4PrefixInvalid"
										}'
									>
										<CInput
											v-model.number='tunnel.ipv4.prefix'
											type='number'
											min='1'
											max='32'
											:label='$t("network.wireguard.tunnels.form.ipv4Prefix")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										/>
									</ValidationProvider>
								</CCol>
								<CCol
									v-if='stack === "ipv6" || stack === "both"'
									:md='stack === "ipv6" ? 12 : 6'
								>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='required|ipv6'
										:custom-messages='{
											required: "network.wireguard.tunnels.errors.ipv6",
											ipv6: "network.wireguard.tunnels.errors.ipv6Invalid"
										}'
									>
										<CInput
											v-model='tunnel.ipv6.address'
											:label='$t("network.wireguard.tunnels.form.ipv6")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										/>
									</ValidationProvider>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='required|integer|between:48,128'
										:custom-messages='{
											required: "network.wireguard.tunnels.errors.ipv6Prefix",
											integer: "network.wireguard.tunnels.errors.ipv6PrefixInvalid",
											between: "network.wireguard.tunnels.errors.ipv6PrefixInvalid"
										}'
									>
										<CInput
											v-model.number='tunnel.ipv6.prefix'
											type='number'
											min='48'
											max='128'
											:label='$t("network.wireguard.tunnels.form.ipv6Prefix")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										/>
									</ValidationProvider>
								</CCol>
							</CRow>
						</CCol>
					</CRow>
					<div
						v-for='(peer, index) of tunnel.peers'
						:key='index'
						class='form-group'
					>
						<hr>
						<legend>{{ $t('network.wireguard.tunnels.form.peers') }}</legend>
						<CRow>
							<CCol md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required|base64Key'
									:custom-messages='{
										required: "network.wireguard.tunnels.errors.publicKeyPeer",
										base64Key: "network.wireguard.tunnels.errors.base64Key"
									}'
								>
									<CInput
										v-model='peer.publicKey'
										:label='$t("network.wireguard.tunnels.form.publicKey")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
									/>
								</ValidationProvider>
								<CInput
									v-model='peer.psk'
									:label='$t("network.wireguard.tunnels.form.psk")'
								/>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: "network.wireguard.tunnels.errors.endpoint"
									}'
								>
									<CInput
										v-model='peer.endpoint'
										:label='$t("network.wireguard.tunnels.form.endpoint")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
									/>
								</ValidationProvider>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required|integer|between:0,65535'
									:custom-messages='{
										required: "network.wireguard.tunnels.errors.portPeer",
										integer: "network.wireguard.tunnels.errors.portInvalid",
										between: "network.wireguard.tunnels.errors.portInvalid"
									}'
								>
									<CInput
										v-model.number='peer.port'
										type='number'
										min='0'
										max='65535'
										:label='$t("network.wireguard.tunnels.form.port")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
									/>
								</ValidationProvider>
								<div class='form-group'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='required|integer|between:0,65535'
										:custom-messages='{
											required: "network.wireguard.tunnels.errors.keepalive",
											integer: "forms.errors.integer",
											between: "network.wireguard.tunnels.errors.keepaliveInvalid"
										}'
									>
										<CInput
											v-model.number='peer.keepalive'
											type='number'
											min='0'
											max='65535'
											:label='$t("network.wireguard.tunnels.form.keepalive")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										/>
									</ValidationProvider>
									<i>{{ $t("network.wireguard.tunnels.form.keepaliveNote") }}</i>
								</div>
							</CCol>
							<CCol>
								<CSelect
									:value.sync='peerStacks[index]'
									:options='stackOptions'
									:label='$t("network.wireguard.tunnels.form.stack")'
								/>
								<CRow>
									<CCol
										v-if='peerStacks[index] === "ipv4" || peerStacks[index] === "both"'
										:md='peerStacks[index] === "ipv4" ? 12 : 6'
									>
										<div
											v-for='(ip, ipIndex) of peer.allowedIPs.ipv4'
											:key='ipIndex'
										>
											<hr v-if='ipIndex > 0'>
											<ValidationProvider
												v-slot='{errors, touched, valid}'
												rules='required|ipv4'
												:custom-messages='{
													required: "network.wireguard.tunnels.errors.ipv4",
													ipv4: "network.wireguard.tunnels.errors.ipv4Invalid"
												}'
											>
												<CInput
													v-model='ip.address'
													:label='$t("network.wireguard.tunnels.form.ipv4")'
													:is-valid='touched ? valid : null'
													:invalid-feedback='$t(errors[0])'
												/>
											</ValidationProvider>
											<ValidationProvider
												v-slot='{errors, touched, valid}'
												rules='required|between:1,32'
												:custom-messages='{
													required: "network.wireguard.tunnels.errors.ipv4Prefix",
													integer: "network.wireguard.tunnels.errors.ipv4PrefixInvalid",
													between: "network.wireguard.tunnels.errors.ipv4PrefixInvalid"
												}'
											>
												<CInput
													v-model.number='ip.prefix'
													type='number'
													min='1'
													max='32'
													:label='$t("network.wireguard.tunnels.form.ipv4Prefix")'
													:is-valid='touched ? valid : null'
													:invalid-feedback='$t(errors[0])'
												/>
											</ValidationProvider>
											<CButton
												v-if='ipIndex > 0'
												color='danger'
												@click='removeIPv4(index, ipIndex)'
											>
												{{ $t('network.connection.ipv4.addresses.remove') }}
											</CButton> <CButton
												v-if='ipIndex === (peer.allowedIPs.ipv4.length - 1)'
												color='success'
												@click='addIPv4(index)'
											>
												{{ $t('network.connection.ipv4.addresses.add') }}
											</CButton>
										</div>
									</CCol>
									<CCol
										v-if='peerStacks[index] === "ipv6" || peerStacks[index] === "both"'
										:md='peerStacks[index] === "ipv6" ? 12 : 6'
									>
										<div
											v-for='(ip, ipIndex) of peer.allowedIPs.ipv6'
											:key='ipIndex + "b"'
										>
											<hr v-if='ipIndex > 0'>
											<ValidationProvider
												v-slot='{errors, touched, valid}'
												rules='required|ipv6'
												:custom-messages='{
													required: "network.wireguard.tunnels.errors.ipv6",
													ipv6: "network.wireguard.tunnels.errors.ipv6Invalid"
												}'
											>
												<CInput
													v-model='ip.address'
													:label='$t("network.wireguard.tunnels.form.ipv6")'
													:is-valid='touched ? valid : null'
													:invalid-feedback='$t(errors[0])'
												/>
											</ValidationProvider>
											<ValidationProvider
												v-slot='{errors, touched, valid}'
												rules='required|integer|between:48,128'
												:custom-messages='{
													required: "network.wireguard.tunnels.errors.ipv6Prefix",
													integer: "network.wireguard.tunnels.errors.ipv6PrefixInvalid",
													between: "network.wireguard.tunnels.errors.ipv6PrefixInvalid"
												}'
											>
												<CInput
													v-model.number='ip.prefix'
													type='number'
													min='48'
													max='128'
													:label='$t("network.wireguard.tunnels.form.ipv6Prefix")'
													:is-valid='touched ? valid : null'
													:invalid-feedback='$t(errors[0])'
												/>
											</ValidationProvider>
											<CButton
												v-if='ipIndex > 0'
												color='danger'
												@click='removeIPv6(index, ipIndex)'
											>
												{{ $t('network.connection.ipv6.addresses.remove') }}
											</CButton> <CButton
												v-if='ipIndex === (peer.allowedIPs.ipv6.length - 1)'
												color='success'
												@click='addIPv6(index)'
											>
												{{ $t('network.connection.ipv6.addresses.add') }}
											</CButton>
										</div>
									</CCol>
								</CRow>
							</CCol>
						</CRow>
						<CButton
							v-if='tunnel.peers.length !== 1'
							color='danger'
							@click='removePeer(index)'
						>
							{{ $t('network.wireguard.tunnels.form.removePeer') }}
						</CButton> <CButton
							v-if='index === (tunnel.peers.length - 1)'
							color='success'
							@click='addPeer'
						>
							{{ $t('network.wireguard.tunnels.form.addPeer') }}
						</CButton>
					</div>
					<CButton
						color='primary'
						type='submit'
						:disabled='invalid'
					>
						{{ $t('forms.save') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CForm, CInput, CInputCheckbox, CSelect} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required, integer, between} from 'vee-validate/dist/rules';

import ip from 'ip-regex';
import WireguardService from '@/services/WireguardService';

import {AxiosError, AxiosResponse} from 'axios';
import {MetaInfo} from 'vue-meta';
import {IWGTunnel} from '@/interfaces/network';
import {IOption} from '@/interfaces/coreui';
import { extendedErrorToast } from '@/helpers/errorToast';

export enum StackType {
	SINGLE_IPV4 = 'ipv4',
	SINGLE_IPV6 = 'ipv6',
	DUAL = 'both'
}

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CForm,
		CInput,
		CInputCheckbox,
		CSelect,
		ValidationObserver,
		ValidationProvider,
	},
	metaInfo(): MetaInfo {
		return {
			title: (this as unknown as WireguardTunnel).pageTitle
		};
	}
})

/**
 * Wireguard tunnel form component
 */
export default class WireguardTunnel extends Vue {

	/**
	 * @var {IWGTunnel} tunnel Wireguard VPN tunnel configuration
	 */
	private tunnel: IWGTunnel = {
		name: '',
		privateKey: '',
		publicKey: '',
		port: 51820,
		ipv4: {
			address: '',
			prefix: 24,
		},
		ipv6: {
			address: '',
			prefix: 64,
		},
		peers: [
			{
				publicKey: '',
				psk: '',
				keepalive: 25,
				endpoint: '',
				port: 51820,
				allowedIPs: {
					ipv4: [
						{
							address: '',
							prefix: 24
						}
					],
					ipv6: [
						{
							address: '',
							prefix: 64
						}
					]
				}
			},
		],
	};

	/**
	 * @var {Array<StackType>} peerStacks Array of peer stacks
	 */
	private peerStacks: Array<StackType> = [
		StackType.DUAL
	];

	/**
	 * @var {StackType} stack Interface stack
	 */
	private stack = StackType.DUAL;

	/**
	 * @constant {Array<IOption>} stackOptions Array of CoreUI select stack options
	 */
	private stackOptions: Array<IOption> = [
		{
			label: this.$t('network.wireguard.tunnels.form.stackTypes.ipv4'),
			value: StackType.SINGLE_IPV4
		},
		{
			label: this.$t('network.wireguard.tunnels.form.stackTypes.ipv6'),
			value: StackType.SINGLE_IPV6
		},
		{
			label: this.$t('network.wireguard.tunnels.form.stackTypes.both'),
			value: StackType.DUAL
		},
	];

	/**
	 * @var {boolean} optionalPort Controls visibility of interface listen port input
	 */
	private optionalPort = false;

	/**
	 * @property {number|null} id Wireguard tunnel id
	 */
	@Prop({required: false, default: null}) id!: number;

	/**
	 * Initializes form validation rules
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
		extend('ipv4', (address: string) => {
			return ip.v4({exact: true}).test(address);
		});
		extend('ipv6', (address: string) => {
			return ip.v6({exact: true}).test(address);
		});
		extend('base64Key', (key: string) => {
			return RegExp('^[0-9a-zA-Z+/]{43}=$').test(key);
		});
	}

	/**
	 * Retrieves wireguard tunnel configuration if tunnel name prop is populated
	 */
	mounted(): void {
		if (this.id !== null) {
			this.getTunnel();
		}
	}

	/**
	 * Computes page title from the path
	 * @returns {string} Page title
	 */
	get pageTitle(): string {
		return this.$route.path === '/ip-network/vpn/add' ?
			this.$t('network.wireguard.tunnels.add').toString() : this.$t('network.wireguard.tunnels.edit').toString();
	}

	/**
	 * Stores public key to clipboard
	 * @returns {string} Public key or empty string
	 */
	private updateClipboard(): string {
		if (this.tunnel.publicKey !== undefined) {
			return this.tunnel.publicKey;
		}
		return '';
	}

	/**
	 * Shows public key clipboard copy success message
	 */
	private successClipboard(): void {
		this.$toast.success(
			this.$t('network.wireguard.tunnels.messages.copyPublicKey').toString()
		);
	}

	/**
	 * Retrieves Wireguard tunnel configuration
	 */
	private getTunnel(): void {
		this.$store.commit('spinner/SHOW');
		WireguardService.getTunnel(this.id)
			.then((response: AxiosResponse) => {
				const peerStacks: Array<StackType> = [];
				for (const idx in response.data.peers) {
					if (response.data.peers[idx].allowedIPs.ipv4.length === 0) {
						response.data.peers[idx].allowedIPs.ipv4.push({address: '', prefix: 24});
						peerStacks.push(StackType.SINGLE_IPV6);
					} else if (response.data.peers[idx].allowedIPs.ipv6.length === 0) {
						response.data.peers[idx].allowedIPs.ipv6.push({address: '', prefix: 64});
						peerStacks.push(StackType.SINGLE_IPV4);
					} else {
						peerStacks.push(StackType.DUAL);
					}
				}
				if (response.data.ipv4 !== undefined && response.data.ipv6 !== undefined) {
					this.stack = StackType.DUAL;
				} else if (response.data.ipv4 !== undefined && response.data.ipv6 === undefined) {
					this.stack = StackType.SINGLE_IPV4;
					Object.assign(response.data, {ipv6: {address: '', prefix: 64}});
				} else {
					this.stack = StackType.SINGLE_IPV6;
					Object.assign(response.data, {ipv4: {address: '', prefix: 24}});
				}
				this.peerStacks = peerStacks;
				this.tunnel = response.data;
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(
					error,
					'network.wireguard.tunnels.messages.fetchFailed',
					{tunnel: this.id}
				);
				this.$router.push('/ip-network/vpn/');
			});
	}

	/**
	 * Adds a new peer object to tunnel configuration
	 */
	private addPeer(): void {
		this.tunnel.peers.push({
			publicKey: '',
			psk: '',
			keepalive: 25,
			endpoint: '',
			port: 51820,
			allowedIPs: {
				ipv4: [
					{
						address: '',
						prefix: 24,
					}
				],
				ipv6: [
					{
						address: '',
						prefix: 64
					}
				]
			},
		});
		this.peerStacks.push(StackType.SINGLE_IPV4);
	}

	/**
	 * Removes a peer object from tunnel configuration
	 * @param {number} index Peer index
	 */
	private removePeer(index: number): void {
		this.tunnel.peers.splice(index, 1);
		this.peerStacks.splice(index, 1);
	}

	/**
	 * Adds a new peer IPv4 address
	 * @param {number} index Peer index
	 */
	private addIPv4(index: number): void {
		this.tunnel.peers[index].allowedIPs.ipv4?.push({address: '', prefix: 24});
	}

	/**
	 * Removes IPv4 address from peer object
	 * @param {number} peer Peer index
	 * @param {number} index Address index
	 */
	private removeIPv4(peer: number, index: number): void {
		this.tunnel.peers[peer].allowedIPs.ipv4?.splice(index, 1);
	}

	/**
	 * Adds a new peer IPv6 address
	 * @param {number} index Peer index
	 */
	private addIPv6(index: number): void {
		this.tunnel.peers[index].allowedIPs.ipv6?.push({address: '', prefix: 64});
	}

	/**
	 * Removes IPv6 address from peer object
	 * @param {number} peer Peer index
	 * @param {number} index Address index
	 */
	private removeIPv6(peer: number, index: number): void {
		this.tunnel.peers[peer].allowedIPs.ipv6?.splice(index, 1);
	}

	/**
	 * Generates a key pair and populates the tunnel fields
	 */
	private generateKeys(): void {
		WireguardService.createKeys()
			.then((response: AxiosResponse) => {
				this.tunnel.privateKey = response.data.privateKey;
				this.tunnel.publicKey = response.data.publicKey;
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'network.wireguard.tunnels.messages.keyPairFailed'));
	}

	/**
	 * Creates new Wireguard tunnel
	 */
	private saveTunnel(): void {
		const tunnel = this.tunnel;
		this.$store.commit('spinner/SHOW');
		if (this.stack === StackType.SINGLE_IPV4) {
			delete tunnel.ipv6;
		} else if (this.stack === StackType.SINGLE_IPV6) {
			delete tunnel.ipv4;
		}
		for (const idx in tunnel.peers) {
			if (tunnel.peers[idx].psk === '' || tunnel.peers[idx].psk === null) {
				delete tunnel.peers[idx].psk;
			}
			if (this.peerStacks[idx] === StackType.SINGLE_IPV4) {
				tunnel.peers[idx].allowedIPs.ipv6 = [];
			} else if (this.peerStacks[idx] === StackType.SINGLE_IPV6) {
				tunnel.peers[idx].allowedIPs.ipv4 = [];
			}
		}
		if (!this.optionalPort) {
			delete tunnel.port;
		}
		delete tunnel.publicKey;
		if (this.$route.path === '/ip-network/vpn/add') {
			WireguardService.createTunnel(tunnel)
				.then(this.handleSuccess)
				.catch(this.handleError);
		} else {
			WireguardService.editTunnel(this.id, tunnel)
				.then(this.handleSuccess)
				.catch(this.handleError);
		}
	}

	/**
	 * Handles axios success response
	 */
	private handleSuccess(): void {
		this.$store.commit('spinner/HIDE');
		this.$toast.success(
			this.$t(
				'network.wireguard.tunnels.messages.' + (this.$route.path === '/ip-network/vpn/add' ? 'add' : 'edit') + 'Success',
				{tunnel: this.tunnel.name}
			).toString()
		);
		this.$router.push('/ip-network/vpn/');
	}

	/**
	 * Handles axios errors
	 * @param {AxiosError} error Axios error
	 */
	private handleError(error: AxiosError): void {
		extendedErrorToast(
			error,
			'network.wireguard.tunnels.messages.' + (this.$route.path === '/ip-network/vpn/add' ? 'add' : 'edit') + 'Failed',
			{tunnel: this.tunnel.name},
		);
	}

}
</script>
