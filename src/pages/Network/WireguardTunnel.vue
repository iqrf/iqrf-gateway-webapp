<template>
	<div>
		<h1>{{ pageTitle }}</h1>
		<CCard body-wrapper>
			<ValidationObserver v-slot='{invalid}'>
				<CForm @submit.prevent='saveTunnel'>
					<CRow>
						<CCol md='6'>
							<legend>{{ $t('network.wireguard.tunnels.form.interface') }}</legend>
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
								rules='required'
								:custom-messages='{
									required: "network.wireguard.tunnels.errors.privateKey"
								}'
							>
								<CInput
									v-model='tunnel.privateKey'
									:label='$t("network.wireguard.tunnels.form.privateKey")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required'
								:custom-messages='{
									required: "network.wireguard.tunnels.errors.publicKey"
								}'
							>
								<CInput
									v-model='tunnel.publicKey'
									:label='$t("network.wireguard.tunnels.form.publicKey")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required|ipv4'
								:custom-messages='{
									required: "network.wireguard.tunnels.errors.ipv4",
									ipv4: "network.wireguard.tunnels.errors.ipv4Invalid"
								}'
							>
								<CInput
									v-model='tunnel.ipv4'
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
									v-model.number='tunnel.ipv4Prefix'
									type='number'
									min='1'
									max='32'
									:label='$t("network.wireguard.tunnels.form.ipv4Prefix")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required|ipv6'
								:custom-messages='{
									required: "network.wireguard.tunnels.errors.ipv6",
									ipv6: "network.wireguard.tunnels.errors.ipv6Invalid"
								}'
							>
								<CInput
									v-model='tunnel.ipv6'
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
									v-model.number='tunnel.ipv6Prefix'
									type='number'
									min='48'
									max='128'
									:label='$t("network.wireguard.tunnels.form.ipv6Prefix")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<ValidationProvider
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
							<CButton
								color='primary'
								type='submit'
								:disabled='invalid'
							>
								{{ $t('forms.save') }}
							</CButton>
						</CCol>
						<CCol md='6'>
							<div
								v-for='(peer, index) of tunnel.peers'
								:key='index'
								class='form-group'
							>
								<legend>{{ $t('network.wireguard.tunnels.form.peers') }}</legend>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: "network.wireguard.tunnels.errors.publicKeyPeer"
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
										rules='required|integer'
										:custom-messages='{
											required: "network.wireguard.tunnels.errors.keepalive",
											integer: "forms.errors.integer"
										}'
									>
										<CInput
											v-model.number='peer.keepalive'
											type='number'
											:label='$t("network.wireguard.tunnels.form.keepalive")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										/>
									</ValidationProvider>
									<i>{{ $t("network.wireguard.tunnels.form.keepaliveNote") }}</i>
								</div>
								<CRow>
									<CCol md='6'>
										<legend>{{ $t('network.wireguard.tunnels.form.allowedIPv4Addrs') }}</legend>
										<div
											v-for='(ip, ipIndex) of peer.allowedIPs.ipv4'
											:key='ipIndex'
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
											</CButton><hr>
										</div>
									</CCol>
									<CCol md='6'>
										<legend>{{ $t('network.wireguard.tunnels.form.allowedIPv6Addrs') }}</legend>
										<div
											v-for='(ip, ipIndex) of peer.allowedIPs.ipv6'
											:key='ipIndex + "b"'
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
											</CButton><hr>
										</div>
									</CCol>
								</CRow>
								<CButton
									v-if='index > 0'
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
						</CCol>
					</CRow>
				</CForm>
			</ValidationObserver>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required, integer, between} from 'vee-validate/dist/rules';

import ip from 'ip-regex';
import WireguardService from '../../services/WireguardService';

import {AxiosError, AxiosResponse} from 'axios';
import {MetaInfo} from 'vue-meta';
import {IWGTunnel} from '../../interfaces/network';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CForm,
		CInput,
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
	 * Wireguard VPN tunnel configuration
	 */
	private tunnel: IWGTunnel = {
		name: '',
		privateKey: '',
		publicKey: '',
		port: 51820,
		ipv4: '',
		ipv4Prefix: 24,
		ipv6: '',
		ipv6Prefix: 64,
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
	}

	/**
	 * @property {string|null} tunnelName Wireguard tunnel name
	 */
	@Prop({required: false, default: null}) tunnelName!: string

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
	}

	/**
	 * Retrieves wireguard tunnel configuration if tunnel name prop is populated
	 */
	mounted(): void {
		if (this.tunnelName !== null) {
			this.getTunnel();
		}
	}

	/**
	 * Computes page title from the path
	 * @returns {string} Page title
	 */
	get pageTitle(): string {
		return this.$route.path === '/network/vpn/add' ? 
			this.$t('network.wireguard.tunnels.add').toString() : this.$t('network.wireguard.tunnels.edit').toString();
	}

	/**
	 * Retrieves Wireguard tunnel configuration
	 */
	private getTunnel(): void {
		this.$store.commit('spinner/SHOW');
		WireguardService.getTunnel(this.tunnelName)
			.then((response: AxiosResponse) => {
				this.tunnel = response.data;
				this.$store.commit('spinner/HIDE');
			})
			.catch(() => {
				this.$store.commit('spinner/HIDE');
				this.$router.push('/network/vpn/');
				this.$toast.error(
					this.$t(
						'network.wireguard.tunnels.messages.getFailed',
						{tunnel: this.tunnelName}
					).toString()
				);
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
	}

	/**
	 * Removes a peer object from tunnel configuration
	 * @param {number} index Peer index
	 */
	private removePeer(index: number): void {
		this.tunnel.peers.splice(index, 1);
	}

	/**
	 * Adds a new peer IPv4 address
	 * @param {number} index Peer index
	 */
	private addIPv4(index: number): void {
		this.tunnel.peers[index].allowedIPs.ipv4.push({address: '', prefix: 24});
	}

	/**
	 * Removes IPv4 address from peer object
	 * @param {number} peer Peer index
	 * @param {number} index Address index
	 */
	private removeIPv4(peer: number, index: number): void {
		this.tunnel.peers[peer].allowedIPs.ipv4.splice(index, 1);
	}

	/**
	 * Adds a new peer IPv6 address
	 * @param {number} index Peer index
	 */
	private addIPv6(index: number): void {
		this.tunnel.peers[index].allowedIPs.ipv6.push({address: '', prefix: 64});
	}

	/**
	 * Removes IPv6 address from peer object
	 * @param {number} peer Peer index
	 * @param {number} index Address index
	 */
	private removeIPv6(peer: number, index: number): void {
		this.tunnel.peers[peer].allowedIPs.ipv6.splice(index, 1);
	}

	/**
	 * Generates a key pair and populates the tunnel fields
	 */
	private generateKeys(): void {
		WireguardService.createKeys()
			.then((response: AxiosResponse) => {
				this.tunnel.privateKey = response.data.privateKey;
				this.tunnel.publicKey = response.data.publicKey;
			});
	}

	/**
	 * Creates new Wireguard tunnel
	 */
	private saveTunnel(): void {
		this.$store.commit('spinner/SHOW');
		WireguardService.createTunnel(this.tunnel)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$router.push('/network/vpn/');
				this.$toast.success(
					this.$t(
						'network.wireguard.tunnels.messages.addSuccess',
						{tunnel: this.tunnel.name}
					).toString()
				);
			})
			.catch((error: AxiosError) => {
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t(
						'network.wireguard.tunnels.messages.addFailed',
						{error: error.response !== undefined ? error.response.data.message : error.message}
					).toString()
				);
			});
	}

}
</script>
