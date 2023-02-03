<!--
Copyright 2017-2023 IQRF Tech s.r.o.
Copyright 2019-2023 MICRORISC s.r.o.

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
		<IpStackSelector v-model='peer.allowedIPs.stack' />
		<WireGuardPeerAddresses
			v-if='[WireguardStack.IPV4, WireguardStack.DUAL].includes(peer.allowedIPs.stack)'
			v-model='peer.allowedIPs.ipv4'
			:version='4'
		/>
		<WireGuardPeerAddresses
			v-if='[WireguardStack.IPV6, WireguardStack.DUAL].includes(peer.allowedIPs.stack)'
			v-model='peer.allowedIPs.ipv6'
			:version='6'
		/>
	</div>
</template>

<script lang='ts'>
import {Component, VModel, Vue} from 'vue-property-decorator';
import {CButton, CCol, CInput, CRow} from '@coreui/vue/src';
import {extend, ValidationProvider} from 'vee-validate';
import {between, integer, required} from 'vee-validate/dist/rules';
import {wgBase64Key} from '@/helpers/validationRules/Network';

import {IWGPeer} from '@/interfaces/Network/Wireguard';
import {WireguardStack} from '@/enums/Network/Wireguard';
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
		WireGuardPeerAddresses: WireGuardPeerAddresses,
	},
	data: () => ({
		WireguardStack,
	}),
})
export default class WireGuardPeers extends Vue {

	/**
	 * Edited WireGuard peer
	 */
	@VModel({required: true}) peer!: IWGPeer;

	/**
	 * Initializes form validation rules
	 */
	protected	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
		extend('base64Key', wgBase64Key);
	}

}
</script>
