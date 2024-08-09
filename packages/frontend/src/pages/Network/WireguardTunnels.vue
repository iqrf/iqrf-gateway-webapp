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
		<h1>{{ $t('network.wireguard.title') }}</h1>
		<v-card>
			<v-card-text>
				<v-data-table
					:loading='loading'
					:headers='headers'
					:items='tunnels'
					:no-data-text='$t("network.wireguard.tunnels.table.noTunnels")'
				>
					<template #top>
						<v-toolbar dense flat>
							<h5>{{ $t('network.wireguard.tunnels.title') }}</h5>
							<v-spacer />
							<v-btn
								class='mr-1'
								color='success'
								small
								to='/ip-network/vpn/add'
							>
								<v-icon small>
									mdi-plus
								</v-icon>
							</v-btn>
							<v-btn
								color='primary'
								small
								@click='getTunnels'
							>
								<v-icon small>
									mdi-refresh
								</v-icon>
							</v-btn>
						</v-toolbar>
					</template>
					<template #[`item.state`]='{item}'>
						<v-chip
							:color='item.active ? "success" : "error"'
							x-small
							label
						>
							{{ $t(`network.wireguard.tunnels.table.states.${item.active ? '' : 'in'}active`) }}
						</v-chip>
					</template>
					<template #[`item.actions`]='{item}'>
						<v-btn
							class='mr-1'
							:color='item.active ? "error" : "success"'
							small
							@click='changeActiveState(item.id, item.name, !item.active)'
						>
							<v-icon small>
								{{ item.active ? 'mdi-link-off' : 'mdi-link-plus' }}
							</v-icon>
							{{ $t(`network.wireguard.tunnels.table.action.${item.active ? 'deactivate' : 'activate'}`) }}
						</v-btn>
						<v-btn
							class='mr-1'
							:color='item.enabled ? "error" : "success"'
							small
							@click='changeEnabledState(item.id, item.name, !item.enabled)'
						>
							<v-icon small>
								{{ item.enabled ? 'mdi-close-circle-outline' : 'mdi-check-circle-outline' }}
							</v-icon>
							{{ $t(`table.actions.${item.enabled ? 'disable' : 'enable'}`) }}
						</v-btn>
						<v-btn
							class='mr-1'
							color='primary'
							small
							:to='"/ip-network/vpn/edit/" + item.id'
						>
							<v-icon small>
								mdi-pencil
							</v-icon>
							{{ $t('table.actions.edit') }}
						</v-btn>
						<v-btn
							color='error'
							small
							@click='tunnelDeleteModel = item'
						>
							<v-icon small>
								mdi-delete
							</v-icon>
							{{ $t('table.actions.delete') }}
						</v-btn>
					</template>
				</v-data-table>
			</v-card-text>
		</v-card>
		<WireGuardDeleteModal
			v-model='tunnelDeleteModel'
			@deleted='getTunnels'
		/>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import WireGuardDeleteModal from '@/components/Network/WireGuardDeleteModal.vue';

import {extendedErrorToast} from '@/helpers/errorToast';

import {AxiosError} from 'axios';
import {DataTableHeader} from 'vuetify';
import {
	WireGuardTunnelListEntry
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import {useApiClient} from '@/services/ApiClient';

@Component({
	components: {
		WireGuardDeleteModal,
	},
	metaInfo: {
		title: 'network.wireguard.title'
	}
})

/**
 * WireGuard connections component
 */
export default class WireguardTunnels extends Vue {

	/**
	 * @property {boolean} loading - Is the tunnels loading?
	 */
	private loading = false;

	/**
	 * @var {Array<WireGuardTunnelListEntry>} tunnels Array of existing tunnels
	 */
	private tunnels: Array<WireGuardTunnelListEntry> = [];

	/**
	 * @var {WireGuardTunnelListEntry} tunnelToDelete Tunnel information used in delete modal window
	 */
	private tunnelDeleteModel: WireGuardTunnelListEntry|null = null;

	/**
	 * @constant {Array<DataTableHeader>} headers Vuetify data table headers
	 */
	private readonly headers: Array<DataTableHeader> = [
		{
			value: 'name',
			text: this.$t('network.wireguard.tunnels.table.name').toString(),
		},
		{
			value: 'state',
			text: this.$t('network.wireguard.tunnels.table.state').toString(),
			filterable: false,
			sortable: false,
		},
		{
			value: 'actions',
			text: this.$t('table.actions.title').toString(),
			filterable: false,
			sortable: false,
			align: 'end',
		},
	];

	private service = useApiClient().getNetworkServices().getWireGuardService();

	/**
	 * Retrieves existing WireGuard tunnels
	 */
	mounted(): void {
		this.getTunnels();
	}

	/**
	 * Retrieves existing WireGuard tunnels and stores data into table
	 */
	private getTunnels(): Promise<void> {
		this.loading = true;
		return this.service.listTunnels()
			.then((response: WireGuardTunnelListEntry[]) => {
				this.tunnels = response;
				this.loading = false;
			})
			.catch((error: AxiosError) => {
				this.loading = false;
				extendedErrorToast(error, 'network.wireguard.tunnels.messages.listFailed');
			});
	}

	/**
	 * Changes active state of WireGuard tunnel
	 * @param {number} id WireGuard tunnel ID
	 * @param {string} name WireGuard tunnel name
	 * @param {boolean} state WireGuard tunnel state
	 */
	private changeActiveState(id: number, name: string, state: boolean): void {
		this.loading = true;
		if (state) {
			this.service.activateTunnel(id)
				.then(() => this.handleActiveSuccess(name, state))
				.catch((error: AxiosError) => {
					this.loading = false;
					extendedErrorToast(
						error,
						'network.wireguard.tunnels.messages.activateFailed',
						{tunnel: name}
					);
				});
		} else {
			this.service.deactivateTunnel(id)
				.then(() => this.handleActiveSuccess(name, state))
				.catch((error: AxiosError) => {
					this.loading = false;
					extendedErrorToast(
						error,
						'network.wireguard.tunnels.messages.deactivateFailed',
						{tunnel: name}
					);
				});
		}
	}

	/**
	 * Handles tunnel activation success
	 * @param {string} name WireGuard tunnel name
	 * @param {boolean} state WireGuard tunnel state
	 */
	private handleActiveSuccess(name: string, state: boolean): void {
		this.getTunnels().then(() => this.$toast.success(
			this.$t(
				`network.wireguard.tunnels.messages.${state ? '' : 'de'}activateSuccess`,
				{tunnel: name}
			).toString()
		));
	}

	/**
	 * Changes enabled state of WireGuard tunnel
	 * @param {number} id WireGuard tunnel ID
	 * @param {string} name WireGuard tunnel name
	 * @param {boolean} state WireGuard tunnel state
	 */
	private changeEnabledState(id: number, name: string, state: boolean): void {
		this.loading = true;
		if (state) {
			this.service.enableTunnel(id)
				.then(() => this.handleEnableSuccess(name, state))
				.catch((error: AxiosError) => {
					this.loading = false;
					extendedErrorToast(
						error,
						'network.wireguard.tunnels.messages.enableFailed',
						{tunnel: name}
					);
				});
		} else {
			this.service.disableTunnel(id)
				.then(() => this.handleEnableSuccess(name, state))
				.catch((error: AxiosError) => {
					this.loading = false;
					extendedErrorToast(
						error,
						'network.wireguard.tunnels.messages.disableFailed',
						{tunnel: name}
					);
				});
		}
	}

	/**
	 * Handles tunnel enable success
	 * @param {string} name WireGuard tunnel name
	 * @param {boolean} state WireGuard tunnel state
	 */
	private handleEnableSuccess(name: string, state: boolean): void {
		this.getTunnels().then(() => this.$toast.success(
			this.$t(`network.wireguard.tunnels.messages.${state ? 'enable' : 'disable'}Success`,
				{tunnel: name}
			).toString()
		));
	}
}
</script>
