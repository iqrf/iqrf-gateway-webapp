<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
		<v-card>
			<v-card-text>
				<ValidationObserver v-slot='{invalid}'>
					<v-form @submit.prevent='saveTunnel'>
						<h5>{{ $t('network.wireguard.tunnels.form.interface') }}</h5>
						<v-row>
							<v-col cols='12' lg='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: $t("network.wireguard.tunnels.errors.name")
									}'
								>
									<v-text-field
										v-model='tunnel.name'
										:label='$t("network.wireguard.tunnels.form.name")'
										:success='touched ? valid : null'
										:error-messages='errors'
									/>
								</ValidationProvider>
							</v-col>
							<v-col cols='12' lg='6'>
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
									<v-text-field
										v-model.number='tunnel.port'
										type='number'
										min='0'
										max='65535'
										:disabled='!optionalPort'
										:label='$t("network.wireguard.tunnels.form.port")'
										:success='touched ? valid : null'
										:error-messages='errors'
									>
										<template #prepend>
											<v-checkbox
												v-model='optionalPort'
												dense
											/>
										</template>
									</v-text-field>
								</ValidationProvider>
							</v-col>
						</v-row>
						<v-row>
							<v-col cols='12' lg='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required|base64Key'
									:custom-messages='{
										required: $t("network.wireguard.tunnels.errors.privateKey"),
										base64Key: $t("network.wireguard.tunnels.errors.base64Key"),
									}'
								>
									<v-text-field
										v-model='tunnel.privateKey'
										:label='$t("network.wireguard.tunnels.form.privateKey")'
										:success='touched ? valid : null'
										:error-messages='errors'
									>
										<template #append-outer>
											<v-btn
												small
												color='primary'
												@click='generateKeys'
											>
												<v-icon>mdi-key-plus</v-icon>
											</v-btn>
										</template>
									</v-text-field>
								</ValidationProvider>
							</v-col>
							<v-col cols='12' lg='6'>
								<v-text-field
									:value='tunnel.publicKey'
									:label='$t("network.wireguard.tunnels.form.publicKeyIface")'
									readonly
									disabled
								>
									<template #append-outer>
										<v-btn
											v-clipboard='updateClipboard'
											v-clipboard:success='successClipboard'
											small
											color='primary'
										>
											<v-icon>mdi-clipboard-outline</v-icon>
										</v-btn>
									</template>
								</v-text-field>
							</v-col>
						</v-row>
						<IpStackSelector v-model='tunnel.stack' />
						<v-row v-if='[WireGuardIpStack.IPV4, WireGuardIpStack.DUAL].includes(tunnel.stack)'>
							<v-col cols='12' lg='6'>
								<IpAddressInput v-model='tunnel.ipv4.address' :version='4' />
							</v-col>
							<v-col cols='12' lg='6'>
								<IpAddressPrefixInput v-model='tunnel.ipv4.prefix' :version='4' />
							</v-col>
						</v-row>
						<v-row v-if='[WireGuardIpStack.IPV6, WireGuardIpStack.DUAL].includes(tunnel.stack)'>
							<v-col cols='12' lg='6'>
								<IpAddressInput v-model='tunnel.ipv6.address' :version='6' :multiple='false' />
							</v-col>
							<v-col cols='12' lg='6'>
								<IpAddressPrefixInput v-model='tunnel.ipv6.prefix' :version='6' />
							</v-col>
						</v-row>
						<v-divider class='my-2' />
						<h5>Peers</h5>
						<WireGuardPeers v-model='tunnel' />
						<v-divider class='my-2' />
						<v-btn
							color='primary'
							type='submit'
							:disabled='invalid'
						>
							{{ $t('forms.save') }}
						</v-btn>
					</v-form>
				</ValidationObserver>
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {AxiosError} from 'axios';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {between, integer, required} from 'vee-validate/dist/rules';
import {wgBase64Key} from '@/helpers/validationRules/Network';
import {MetaInfo} from 'vue-meta';

import IpAddressPrefixInput from '@/components/Network/WireGuard/IpAddressPrefixInput.vue';
import IpAddressInput from '@/components/Network/WireGuard/IpAddressInput.vue';
import IpStackSelector from '@/components/Network/WireGuard/IpStackSelector.vue';
import WireGuardPeers from '@/components/Network/WireGuard/WireGuardPeers.vue';
import {extendedErrorToast} from '@/helpers/errorToast';
import {useApiClient} from '@/services/ApiClient';
import {
	WireGuardIpStack,
	WireGuardKeyPair, WireGuardTunnelConfig
} from '@iqrf/iqrf-gateway-webapp-client/types/Network/WireGuard';

@Component({
	components: {
		IpAddressInput,
		IpAddressPrefixInput,
		IpStackSelector,
		WireGuardPeers,
		ValidationObserver,
		ValidationProvider,
	},
	data: () => ({
		WireGuardIpStack,
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
	 * @var {WireGuardTunnelConfig} tunnel WireGuard VPN tunnel configuration
	 */
	private tunnel: WireGuardTunnelConfig = {
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
		stack: WireGuardIpStack.DUAL,
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
					stack: WireGuardIpStack.DUAL,
				},
			},
		],
	};

	/**
	 * @var {boolean} optionalPort Controls visibility of interface listen port input
	 */
	private optionalPort = false;

	/**
	 * @var {WireguardService} service WireGuard service
	 */
	private service = useApiClient().getNetworkServices().getWireGuardService();

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
		this.service.fetchTunnel(this.id)
			.then((response: WireGuardTunnelConfig) => {
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
		this.service.generateKeyPair()
			.then((response: WireGuardKeyPair) => {
				this.tunnel.privateKey = response.privateKey;
				this.tunnel.publicKey = response.publicKey;
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'network.wireguard.tunnels.messages.keyPairFailed'));
	}

	/**
	 * Creates new WireGuard tunnel
	 */
	private saveTunnel(): void {
		const tunnel: WireGuardTunnelConfig = JSON.parse(JSON.stringify(this.tunnel));
		this.$store.commit('spinner/SHOW');
		if (!this.optionalPort) {
			delete tunnel.port;
		}
		if (this.$route.path === '/ip-network/vpn/add') {
			this.service.createTunnel(tunnel)
				.then(this.handleSuccess)
				.catch(this.handleError);
		} else {
			this.service.editTunnel(this.id, tunnel)
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
