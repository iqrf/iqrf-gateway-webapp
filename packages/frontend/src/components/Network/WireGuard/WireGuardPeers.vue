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
		<v-expansion-panels	accordion>
			<v-expansion-panel
				v-for='(peer, index) of tunnel.peers'
				:key='index'
			>
				<v-expansion-panel-header class='pt-0 pb-0'>
					<h6>{{ $t('network.wireguard.tunnels.form.peers') }}</h6>
					<span class='text-end'>
						<v-btn
							v-if='tunnel.peers.length > 1'
							color='error'
							small
							@click.native.stop='removePeer(index)'
						>
							<v-icon>
								mdi-delete
							</v-icon>
						</v-btn>
						<v-btn
							v-if='index === (tunnel.peers.length - 1)'
							class='ml-1'
							color='success'
							small
							@click.native.stop='addPeer'
						>
							<v-icon>
								mdi-plus
							</v-icon>
						</v-btn>
					</span>
				</v-expansion-panel-header>
				<v-expansion-panel-content>
					<v-row>
						<v-col cols='12' lg='6'>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required|base64Key'
								:custom-messages='{
									required: $t("network.wireguard.tunnels.errors.publicKeyPeer"),
									base64Key: $t("network.wireguard.tunnels.errors.base64Key"),
								}'
							>
								<v-text-field
									v-model='peer.publicKey'
									:label='$t("network.wireguard.tunnels.form.publicKey").toString()'
									:success='touched ? valid : null'
									:error-messages='errors'
								/>
							</ValidationProvider>
						</v-col>
						<v-col cols='12' lg='6'>
							<v-text-field
								v-model='peer.psk'
								:label='$t("network.wireguard.tunnels.form.psk").toString()'
							/>
						</v-col>
					</v-row>
					<v-row>
						<v-col cols='12' lg='6'>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required'
								:custom-messages='{
									required: $t("network.wireguard.tunnels.errors.endpoint"),
								}'
							>
								<v-text-field
									v-model='peer.endpoint'
									:label='$t("network.wireguard.tunnels.form.endpoint").toString()'
									:success='touched ? valid : null'
									:error-messages='errors'
								/>
							</ValidationProvider>
						</v-col>
						<v-col cols='12' lg='6'>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required|integer|between:0,65535'
								:custom-messages='{
									required: $t("network.wireguard.tunnels.errors.portPeer"),
									integer: $t("network.wireguard.tunnels.errors.portInvalid"),
									between: $t("network.wireguard.tunnels.errors.portInvalid"),
								}'
							>
								<v-text-field
									v-model.number='peer.port'
									type='number'
									min='0'
									max='65535'
									:label='$t("network.wireguard.tunnels.form.port").toString()'
									:success='touched ? valid : null'
									:error-messages='errors'
								/>
							</ValidationProvider>
						</v-col>
					</v-row>
					<v-row>
						<v-col cols='12' lg='6'>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required|integer|between:0,65535'
								:custom-messages='{
									required: $t("network.wireguard.tunnels.errors.keepalive"),
									integer: $t("forms.errors.integer"),
									between: $t("network.wireguard.tunnels.errors.keepaliveInvalid"),
								}'
							>
								<v-text-field
									v-model.number='peer.keepalive'
									type='number'
									min='0'
									max='65535'
									:label='$t("network.wireguard.tunnels.form.keepalive").toString()'
									:description='$t("network.wireguard.tunnels.form.keepaliveNote").toString()'
									:success='touched ? valid : null'
									:error-messages='errors'
								/>
							</ValidationProvider>
						</v-col>
					</v-row>
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
				</v-expansion-panel-content>
			</v-expansion-panel>
		</v-expansion-panels>
	</div>
</template>

<script lang='ts'>
import {Component, VModel, Vue} from 'vue-property-decorator';
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
