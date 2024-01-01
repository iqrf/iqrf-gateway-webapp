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
	<v-dialog
		v-model='showModal'
		width='50%'
		persistent
		no-click-animation
	>
		<v-card v-if='tunnel !== null'>
			<v-card-title>
				{{ $t('network.wireguard.tunnels.modal.title') }}
			</v-card-title>
			<v-card-text>
				{{ $t('network.wireguard.tunnels.modal.prompt', {tunnel: tunnel.name}) }}
			</v-card-text>
			<v-card-actions>
				<v-spacer />
				<v-btn
					@click='hideModal'
				>
					{{ $t('forms.cancel') }}
				</v-btn>
				<v-btn
					color='error'
					@click='removeTunnel(tunnel.id, tunnel.name)'
				>
					{{ $t('forms.delete') }}
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>

<script lang='ts'>
import {Component, VModel, Vue} from 'vue-property-decorator';

import {extendedErrorToast} from '@/helpers/errorToast';
import WireguardService from '@/services/WireguardService';

import {AxiosError} from 'axios';
import {IWG} from '@/interfaces/Network/Wireguard';

/**
 * WireGuard connections component
 */
@Component
export default class WireguardTunnels extends Vue {

	/**
	 * @property {IWG|null} tunnel Tunnel information used in delete modal window
	 */
	@VModel({required: true, default: null}) tunnel!: IWG|null;

	/**
	 * Computes modal display condition
	 */
	get showModal(): boolean {
		return this.tunnel !== null;
	}

	/**
	 * Removes an existing WireGuard tunnel
	 * @param {number} id WireGuard tunnel ID
	 * @param {string} name WireGuard tunnel name
	 */
	private removeTunnel(id: number, name: string): void {
		this.tunnel = null;
		this.$store.commit('spinner/SHOW');
		WireguardService.removeTunnel(id)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$emit('deleted');
			})
			.catch((error: AxiosError) => extendedErrorToast(
				error,
				'network.wireguard.tunnels.messages.deleteFailed',
				{tunnel: name}
			));
	}

	/**
	 * Hides delete modal
	 */
	private hideModal(): void {
		this.tunnel = null;
	}
}
</script>
