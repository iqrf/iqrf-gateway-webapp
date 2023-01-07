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
		<h1>{{ pageTitle }}</h1>
		<CCard body-wrapper>
			<ValidationObserver v-slot='{invalid}'>
				<CForm @submit.prevent='saveTunnel'>
					<legend>{{ $t('network.wireguard.tunnels.form.interface') }}</legend>
					<CRow form>
						<CCol sm='12' lg='6'>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required'
								:custom-messages='{
									required: $t("network.wireguard.tunnels.errors.name")
								}'
							>
								<CInput
									v-model='tunnel.name'
									:label='$t("network.wireguard.tunnels.form.name").toString()'
									placeholder='wg0'
									:is-valid='touched ? valid : null'
									:invalid-feedback='errors.join(", ")'
								/>
							</ValidationProvider>
						</CCol>
						<CCol sm='12' lg='6'>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								:rules='{
									required: optionalPort,
									integer: true,
									between: {
										enabled: true,
										min: 0,
										max: 65535
									}
								}'
								:custom-messages='{
									required: $t("network.wireguard.tunnels.errors.portIface"),
									integer: $t("network.wireguard.tunnels.errors.portInvalid"),
									between: $t("network.wireguard.tunnels.errors.portInvalid"),
								}'
							>
								<CInput
									v-model.number='tunnel.port'
									type='number'
									min='0'
									max='65535'
									:label='$t("network.wireguard.tunnels.form.port").toString()'
									:disabled='!optionalPort'
									:is-valid='touched ? valid : null'
									:invalid-feedback='errors.join(", ")'
								>
									<template #prepend-content>
										<CInputCheckbox
											:checked.sync='optionalPort'
										/>
									</template>
								</CInput>
							</ValidationProvider>
						</CCol>
					</CRow>
					<CRow form>
						<CCol sm='12' lg='6'>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required|base64Key'
								:custom-messages='{
									required: $t("network.wireguard.tunnels.errors.privateKey"),
									base64Key: $t("network.wireguard.tunnels.errors.base64Key"),
								}'
							>
								<CInput
									v-model='tunnel.privateKey'
									:label='$t("network.wireguard.tunnels.form.privateKey").toString()'
									:is-valid='touched ? valid : null'
									:invalid-feedback='errors.join(", ")'
								>
									<template #append-content>
										<span
											class='text-success'
											@click='generateKeys'
										>
											<FontAwesomeIcon
												:icon='["far", "plus-square"]'
												size='xl'
											/>
										</span>
									</template>
								</CInput>
							</ValidationProvider>
						</CCol>
						<CCol sm='12' lg='6'>
							<CInput
								v-model='tunnel.publicKey'
								:label='$t("network.wireguard.tunnels.form.publicKeyIface").toString()'
								disabled
							>
								<template #append-content>
									<span
										v-clipboard='updateClipboard'
										v-clipboard:success='successClipboard'
										class='text-primary'
									>
										<FontAwesomeIcon
											:icon='["far", "clipboard"]'
											size='xl'
										/>
									</span>
								</template>
							</CInput>
						</CCol>
					</CRow>
					<IpStackSelector v-model='stack' />
					<CRow
						v-if='[WireguardStack.IPV4, WireguardStack.DUAL].includes(stack)'
						form
					>
						<CCol sm='12' lg='6'>
							<IpAddressInput v-model='tunnel.ipv4.address' :version='4' />
						</CCol>
						<CCol sm='12' lg='6'>
							<IpAddressPrefixInput v-model='tunnel.ipv4.prefix' :version='4' />
						</CCol>
					</CRow>
					<CRow
						v-if='[WireguardStack.IPV6, WireguardStack.DUAL].includes(stack)'
						form
					>
						<CCol sm='12' lg='6'>
							<IpAddressInput v-model='tunnel.ipv6.address' :version='6' :multiple='false' />
						</CCol>
						<CCol sm='12' lg='6'>
							<IpAddressPrefixInput v-model='tunnel.ipv6.prefix' :version='6' />
						</CCol>
					</CRow>
					<WireGuardPeers v-model='tunnel' />
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
import {CButton, CCard, CCol, CForm, CInput, CInputCheckbox, CRow} from '@coreui/vue/src';
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';
import {AxiosError, AxiosResponse} from 'axios';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {between, integer, required} from 'vee-validate/dist/rules';
import {wgBase64Key} from '@/helpers/validationRules/Network';
import {MetaInfo} from 'vue-meta';

import IpAddressPrefixInput from '@/components/Network/WireGuard/IpAddressPrefixInput.vue';
import IpAddressInput from '@/components/Network/WireGuard/IpAddressInput.vue';
import IpStackSelector from '@/components/Network/WireGuard/IpStackSelector.vue';
import WireGuardPeers from '@/components/Network/WireGuard/WireGuardPeers.vue';
import {WireguardStack} from '@/enums/Network/Wireguard';
import {extendedErrorToast} from '@/helpers/errorToast';
import {IWGTunnel} from '@/interfaces/Network/Wireguard';
import WireguardService from '@/services/WireguardService';

@Component({
	components: {
		CButton,
		CCard,
		CCol,
		CForm,
		CInput,
		CInputCheckbox,
		CRow,
		FontAwesomeIcon,
		IpAddressInput,
		IpAddressPrefixInput,
		IpStackSelector,
		WireGuardPeers,
		ValidationObserver,
		ValidationProvider,
	},
	data: () => ({
		WireguardStack,
	}),
	metaInfo(): MetaInfo {
		return {
			title: (this as unknown as WireguardTunnel).pageTitle
		};
	}
})

/**
 * WireGuard tunnel form component
 */
export default class WireguardTunnel extends Vue {

	/**
	 * @var {IWGTunnel} tunnel WireGuard VPN tunnel configuration
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
		stack: WireguardStack.DUAL,
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
					],
					stack: WireguardStack.DUAL,
				},
			},
		],
	};

	/**
	 * @var {WireguardStack} stack Interface stack
	 */
	private stack = WireguardStack.DUAL;

	/**
	 * @var {boolean} optionalPort Controls visibility of interface listen port input
	 */
	private optionalPort = false;

	/**
	 * @property {number|null} id WireGuard tunnel id
	 */
	@Prop({required: false, default: null}) id!: number;

	/**
	 * Initializes form validation rules
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
		extend('base64Key', wgBase64Key);
	}

	/**
	 * Retrieves WireGuard tunnel configuration if tunnel name prop is populated
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
		return (this.$route.path === '/ip-network/vpn/add' ?
			this.$t('network.wireguard.tunnels.add') :
			this.$t('network.wireguard.tunnels.edit')).toString();
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
	 * Retrieves WireGuard tunnel configuration
	 */
	private getTunnel(): void {
		this.$store.commit('spinner/SHOW');
		WireguardService.getTunnel(this.id)
			.then((response: IWGTunnel) => {
				this.tunnel = response;
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
	 * Creates new WireGuard tunnel
	 */
	private saveTunnel(): void {
		const tunnel: IWGTunnel = JSON.parse(JSON.stringify(this.tunnel));
		this.$store.commit('spinner/SHOW');
		if (this.stack === WireguardStack.IPV4) {
			delete tunnel.ipv6;
		} else if (this.stack === WireguardStack.IPV6) {
			delete tunnel.ipv4;
		}
		delete tunnel.stack;
		for (const idx in tunnel.peers) {
			if (tunnel.peers[idx].psk === '' || tunnel.peers[idx].psk === null) {
				delete tunnel.peers[idx].psk;
			}
			if (tunnel.peers[idx].allowedIPs.stack === WireguardStack.IPV4) {
				tunnel.peers[idx].allowedIPs.ipv6 = [];
			} else if (tunnel.peers[idx].allowedIPs.stack === WireguardStack.IPV6) {
				tunnel.peers[idx].allowedIPs.ipv4 = [];
			}
			delete tunnel.peers[idx].allowedIPs.stack;
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
		let message: string;
		if (this.$route.path === '/ip-network/vpn/add') {
			message = this.$t('network.wireguard.tunnels.messages.addSuccess', {tunnel: this.tunnel.name}).toString();
		} else {
			message = this.$t('network.wireguard.tunnels.messages.editSuccess', {tunnel: this.tunnel.name}).toString();
		}
		this.$toast.success(message);
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
