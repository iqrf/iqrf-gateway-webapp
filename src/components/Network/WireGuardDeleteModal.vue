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
	<CModal
		color='danger'
		:show='tunnelToDelete !== null'
	>
		<template #header>
			<h5 class='modal-title'>
				{{ $t('network.wireguard.tunnels.modal.title') }}
			</h5>
		</template>
		<span v-if='tunnelToDelete !== null'>
			{{ $t('network.wireguard.tunnels.modal.prompt', {tunnel: tunnelToDelete.name}) }}
		</span>
		<template #footer>
			<CButton
				color='danger'
				@click='removeTunnel(tunnelToDelete.id, tunnelToDelete.name)'
			>
				{{ $t('forms.delete') }}
			</CButton> <CButton
				color='secondary'
				@click='tunnelToDelete = null'
			>
				{{ $t('forms.cancel') }}
			</CButton>
		</template>
	</CModal>
</template>

<script lang='ts'>
import {Component, VModel, Vue} from 'vue-property-decorator';
import {CButton, CModal} from '@coreui/vue/src';

import {extendedErrorToast} from '@/helpers/errorToast';
import WireguardService from '@/services/WireguardService';

import {AxiosError} from 'axios';
import {IWG} from '@/interfaces/Network/Wireguard';

@Component({
	components: {
		CButton,
		CModal,
	},
})

/**
 * WireGuard connections component
 */
export default class WireguardTunnels extends Vue {

	/**
	 * @property {IWG|null} tunnelToDelete Tunnel information used in delete modal window
	 */
	@VModel({required: true, default: null}) tunnelToDelete!: IWG|null;

	/**
	 * Removes an existing WireGuard tunnel
	 * @param {number} id WireGuard tunnel ID
	 * @param {string} name WireGuard tunnel name
	 */
	private removeTunnel(id: number, name: string): void {
		this.tunnelToDelete = null;
		this.$store.commit('spinner/SHOW');
		WireguardService.removeTunnel(id)
			.then(() => {
				this.$emit('deleted');
			})
			.catch((error: AxiosError) => extendedErrorToast(
				error,
				'network.wireguard.tunnels.messages.deleteFailed',
				{tunnel: name}
			));
	}

}
</script>
