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
		<div
			v-for='(peer, index) of tunnel.peers'
			:key='index'
			class='form-group'
		>
			<hr>
			<legend>{{ $t('network.wireguard.tunnels.form.peers') }}</legend>
			<CRow form>
				<CCol sm='12' lg='6'>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='required|base64Key'
						:custom-messages='{
							required: $t("network.wireguard.tunnels.errors.publicKeyPeer"),
							base64Key: $t("network.wireguard.tunnels.errors.base64Key"),
						}'
					>
						<CInput
							v-model='peer.publicKey'
							:label='$t("network.wireguard.tunnels.form.publicKey").toString()'
							:is-valid='touched ? valid : null'
							:invalid-feedback='errors.join(", ")'
						/>
					</ValidationProvider>
				</CCol>
				<CCol sm='12' lg='6'>
					<CInput
						v-model='peer.psk'
						:label='$t("network.wireguard.tunnels.form.psk").toString()'
					/>
				</CCol>
			</CRow>
			<CRow form>
				<CCol sm='12' lg='6'>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='required'
						:custom-messages='{
							required: $t("network.wireguard.tunnels.errors.endpoint"),
						}'
					>
						<CInput
							v-model='peer.endpoint'
							:label='$t("network.wireguard.tunnels.form.endpoint").toString()'
							:is-valid='touched ? valid : null'
							:invalid-feedback='errors.join(", ")'
						/>
					</ValidationProvider>
				</CCol>
				<CCol sm='12' lg='6'>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='required|integer|between:0,65535'
						:custom-messages='{
							required: $t("network.wireguard.tunnels.errors.portPeer"),
							integer: $t("network.wireguard.tunnels.errors.portInvalid"),
							between: $t("network.wireguard.tunnels.errors.portInvalid"),
						}'
					>
						<CInput
							v-model.number='peer.port'
							type='number'
							min='0'
							max='65535'
							:label='$t("network.wireguard.tunnels.form.port").toString()'
							:is-valid='touched ? valid : null'
							:invalid-feedback='errors.join(", ")'
						/>
					</ValidationProvider>
				</CCol>
			</CRow>
			<CRow form>
				<CCol sm='12' lg='6'>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='required|integer|between:0,65535'
						:custom-messages='{
							required: $t("network.wireguard.tunnels.errors.keepalive"),
							integer: $t("forms.errors.integer"),
							between: $t("network.wireguard.tunnels.errors.keepaliveInvalid"),
						}'
					>
						<CInput
							v-model.number='peer.keepalive'
							type='number'
							min='0'
							max='65535'
							:label='$t("network.wireguard.tunnels.form.keepalive").toString()'
							:description='$t("network.wireguard.tunnels.form.keepaliveNote").toString()'
							:is-valid='touched ? valid : null'
							:invalid-feedback='errors.join(", ")'
						/>
					</ValidationProvider>
				</CCol>
			</CRow>
			<IpStackSelector v-model='tunnel.peers[index].allowedIPs.stack' />
			<WireGuardPeerAddressFamily
				v-if='[WireguardStack.IPV4, WireguardStack.DUAL].includes(tunnel.peers[index].allowedIPs.stack)'
				v-model='tunnel.peers[index].allowedIPs.ipv4'
				:version='4'
			/>
			<WireGuardPeerAddressFamily
				v-if='[WireguardStack.IPV6, WireguardStack.DUAL].includes(tunnel.peers[index].allowedIPs.stack)'
				v-model='tunnel.peers[index].allowedIPs.ipv6'
				:version='6'
			/>
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
	</div>
</template>

<script lang='ts'>
import {Component, VModel, Vue} from 'vue-property-decorator';
import {CButton, CCol, CInput, CRow} from '@coreui/vue/src';
import {extend, ValidationProvider} from 'vee-validate';
import {between, integer, required} from 'vee-validate/dist/rules';
import {wgBase64Key} from '@/helpers/validationRules/Network';

import {WireguardStack} from '@/enums/Network/Wireguard';
import {IWGTunnel} from '@/interfaces/Network/Wireguard';
import IpStackSelector from '@/components/Network/WireGuard/IpStackSelector.vue';
import WireGuardPeerAddresses from '@/components/Network/WireGuard/WireGuardPeerAddresses.vue';

/**
 * WireGuard peers
 */
@Component({
	components: {
		CButton,
		CCol,
		CInput,
		CRow,
		IpStackSelector,
		ValidationProvider,
		WireGuardPeerAddressFamily: WireGuardPeerAddresses,
	},
	data: () => ({
		WireguardStack,
	}),
})
export default class WireGuardPeers extends Vue {

	/**
	 * Edited WireGuard tunnel
	 */
	@VModel({required: true}) tunnel!: IWGTunnel;

	/**
	 * Initializes form validation rules
	 */
	protected	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
		extend('base64Key', wgBase64Key);
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
				],
				stack: WireguardStack.DUAL,
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

}
</script>
