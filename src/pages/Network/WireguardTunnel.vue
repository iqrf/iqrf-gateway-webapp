<template>
	<div>
		<h1>{{ pageTitle }}</h1>
		<CCard>
			<CCardBody>
				<ValidationObserver v-slot='{invalid}'>
					<CForm @submit.prevent=''>
						<legend>{{ $t('network.wireguard.tunnels.form.interface') }}</legend>
						<CInput
							v-model='tunnel.name'
							:label='$t("network.wireguard.tunnels.form.name")'
						/>
						<CButton
							style='float: right;'
							size='sm'
							color='primary'
							@click='generateKeys'
						>
							{{ $t("network.wireguard.tunnels.form.generateKeys") }}
						</CButton>
						<CInput
							v-model='tunnel.privateKey'
							:label='$t("network.wireguard.tunnels.form.privateKey")'
						/>
						<CInput
							v-model='tunnel.publicKey'
							:label='$t("network.wireguard.tunnels.form.publicKey")'
						/>
						<CInput
							v-model.number='tunnel.port'
							:label='$t("network.wireguard.tunnels.form.port")'
						/>
						<hr>
						<legend>{{ $t('network.wireguard.tunnels.form.peers') }}</legend>
						<div
							v-for='(peer, index) of tunnel.peers'
							:key='index'
							class='form-group'
						>
							<CInput
								v-model='peer.publicKey'
								:label='$t("network.wireguard.tunnels.form.publicKey")'
							/>
							<CInput
								v-model='peer.psk'
								:label='$t("network.wireguard.tunnels.form.psk")'
							/>
							<div class='form-group'>
								<CInput
									v-model.number='peer.keepalive'
									:label='$t("network.wireguard.tunnels.form.keepalive")'
								/>
								<i>{{ $t("network.wireguard.tunnels.form.keepaliveNote") }}</i>
							</div>
							<CInput
								v-model='peer.endpoint'
								:label='$t("network.wireguard.tunnels.form.endpoint")'
							/>
							<CInput
								v-model.number='peer.port'
								:label='$t("network.wireguard.tunnels.form.port")'
							/>
							<CButton
								color='danger'
								@click='removePeer(index)'
							>
								{{ $t('network.wireguard.tunnels.form.removePeer') }}
							</CButton> <CButton
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
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required, integer, between} from 'vee-validate/dist/rules';

import WireguardService from '../../services/WireguardService';

import {AxiosResponse} from 'axios';
import {MetaInfo} from 'vue-meta';

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
	private tunnel = {
		name: '',
		privateKey: '',
		publicKey: '',
		port: 51820,
		peers: [
			{
				publicKey: '',
				psk: '',
				keepalive: 0,
				endpoint: '',
				port: 51820,
				allowedIPs: [],
			},
		],
	}

	/**
	 * Initializes form validation rules
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
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
	 * Adds a new peer object to tunnel configuration
	 */
	private addPeer(): void {
		this.tunnel.peers.push({
			publicKey: '',
			psk: '',
			keepalive: 0,
			endpoint: '',
			port: 51820,
			allowedIPs: [],
		});
	}

	/**
	 * Removes a peer object from tunnel configuration
	 */
	private removePeer(index: number): void {
		this.tunnel.peers.splice(index, 1);
	}

	private generateKeys(): void {
		WireguardService.createKeys()
			.then((response: AxiosResponse) => {
				this.tunnel.privateKey = response.data.privateKey;
				this.tunnel.publicKey = response.data.publicKey;
			});
	}

}
</script>
