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
		<v-row
			v-for='(ip, ipIndex) of addresses'
			:key='ipIndex + "_" + version'
		>
			<v-col cols='12' lg='6'>
				<IpAddressInput
					v-model='ip.address'
					:version='version'
					:multiple='true'
					:allow-removal='addresses.length > 1'
					@add='add()'
					@remove='remove(ipIndex)'
				/>
			</v-col>
			<v-col cols='12' lg='6'>
				<IpAddressPrefixInput v-model='ip.prefix' :version='version' />
			</v-col>
		</v-row>
	</div>
</template>

<script lang='ts'>
import {Component, Prop, VModel, Vue} from 'vue-property-decorator';

import {IWGAllowedIP} from '@/interfaces/Network/Wireguard';
import IpAddressInput from '@/components/Network/WireGuard/IpAddressInput.vue';
import IpAddressPrefixInput from '@/components/Network/WireGuard/IpAddressPrefixInput.vue';

/**
 * WireGuard peers
 */
@Component({
	components: {
		IpAddressInput,
		IpAddressPrefixInput,
	},
})
export default class WireGuardPeerAddresses extends Vue {

	/**
	 * Edited WireGuard peer addresses
	 */
	@VModel({required: true}) addresses!: IWGAllowedIP[];

	/**
	 * @property {4|6} version IP address version
	 */
	@Prop({required: true}) version!: 4|6;

	/**
	 * Adds a new peer IP address
	 */
	private add(): void {
		this.addresses.push({address: '', prefix: 64});
	}

	/**
	 * Removes IP address from peer object
	 * @param {number} index Address index
	 */
	private remove(index: number): void {
		this.addresses.splice(index, 1);
	}

}
</script>
