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
		<h1>{{ $t('network.mobile.title') }}</h1>
		<GsmInterfaces
			ref='interfaces'
			class='mb-5'
			:connections='connections'
			@restart='loading = true'
			@refresh='getConnections'
		/>
		<v-card class='mb-5'>
			<v-card-text>
				<v-data-table
					:loading='loading'
					:headers='headers'
					:items='connections'
					:no-data-text='$t("network.mobile.messages.noConnections")'
				>
					<template #top>
						<v-toolbar dense flat>
							<h5>{{ $t('network.connection.title') }}</h5>
							<v-spacer />
							<v-btn
								class='mr-1'
								color='success'
								small
								to='/ip-network/mobile/add'
							>
								<v-icon small>
									mdi-plus
								</v-icon>
							</v-btn>
							<v-btn
								color='primary'
								small
								@click='getConnections'
							>
								<v-icon small>
									mdi-refresh
								</v-icon>
							</v-btn>
						</v-toolbar>
					</template>
					<template #[`item.interfaceName`]='{item}'>
						{{ item.interfaceName }}
					</template>
					<template #[`item.actions`]='{item}'>
						<v-btn
							:color='item.interfaceName === null ? "success" : "error"'
							class='mr-1'
							small
							@click='item.interfaceName === null ? connect(item) : disconnect(item, false)'
						>
							<v-icon small>
								{{ item.interfaceName === null ? 'mdi-link-plus' : 'mdi-link-off' }}
							</v-icon>
							{{ $t(`network.table.${item.interfaceName === null ? '' : 'dis'}connect`) }}
						</v-btn>
						<v-btn
							class='mr-1'
							small
							color='primary'
							:to='"/ip-network/mobile/edit/" + item.uuid'
						>
							<v-icon small>
								mdi-pencil
							</v-icon>
							{{ $t('table.actions.edit') }}
						</v-btn>
						<v-btn
							color='error'
							small
							@click='item.interfaceName === null ? connectionToDelete = item : connectionToDisconnect = item'
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
		<v-card>
			<v-card-text>
				<NetworkOperators />
			</v-card-text>
		</v-card>
		<v-dialog
			v-model='showDisconnectModal'
			width='50%'
			persitent
			no-click-animation
		>
			<v-card>
				<v-card-title>
					{{ $t('network.connection.modals.disconnectWithWatchdog.title') }}
				</v-card-title>
				<v-card-text>
					<span v-if='connectionToDisconnect !== null'>
						{{ $t('network.connection.modals.disconnectWithWatchdog.body', {connection: connectionToDisconnect.name}) }}
					</span>
				</v-card-text>
				<v-card-actions>
					<v-btn
						color='warning'
						@click='disconnect(connectionToDisconnect, true)'
					>
						{{ $t('network.table.disconnect') }}
					</v-btn>
					<v-btn
						color='secondary'
						class='ml-auto'
						@click='connectionToDisconnect = null'
					>
						{{ $t('forms.cancel') }}
					</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>
		<v-dialog
			v-model='showDisconnectModal'
			width='50%'
			persitent
			no-click-animation
		>
			<v-card>
				<v-card-title>
					<span v-if='monit !== null'>
						{{ $t('network.connection.modals.deleteWithWatchdog.title') }}
					</span>
					<span v-else>
						{{ $t('network.connection.modals.delete.title') }}
					</span>
				</v-card-title>
				<v-card-text>
					<span v-if='connectionToDelete !== null'>
						<span v-if='monit !== null'>
							{{ $t('network.connection.modals.deleteWithWatchdog.body', {connection: connectionToDelete.name}) }}
						</span>
						<span v-else>
							{{ $t('network.connection.modals.delete.body', {connection: connectionToDelete.name}) }}
						</span>
					</span>
				</v-card-text>
				<v-card-actions>
					<v-btn
						color='error'
						@click='remove(connectionToDelete)'
					>
						{{ $t('table.actions.delete') }}
					</v-btn>
					<v-btn
						color='secondary'
						class='ml-auto'
						@click='connectionToDelete = null'
					>
						{{ $t('forms.cancel') }}
					</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>
	</div>
</template>

<script lang='ts'>
import {Component, Ref, Vue, Watch} from 'vue-property-decorator';
import GsmInterfaces from '@/components/Network/GsmInterfaces.vue';
import NetworkOperators from '@/components/Network/NetworkOperators.vue';

import {extendedErrorToast} from '@/helpers/errorToast';

import {AxiosError, AxiosResponse} from 'axios';
import {DataTableHeader} from 'vuetify';
import {
	NetworkConnectionListEntry,
	NetworkConnectionType,
	Modem,
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import {MonitCheck} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {useApiClient} from '@/services/ApiClient';

@Component({
	components: {
		GsmInterfaces,
		NetworkOperators,
	},
	metaInfo: {
		title: 'network.mobile.title',
	},
})

/**
 * Mobile connections page
 */
export default class MobileConnections extends Vue {

	/**
	 * @property {GsmInterfaces} interfaces - GSM interfaces component
	 */
	@Ref('interfaces') readonly interfaces!: GsmInterfaces;

	/**
	 * @property {boolean} loading Loading state
	 */
	private loading = true;

	/**
	 * @var {Array<NetworkConnectionListEntry>} connections Existing mobile connections
	 */
	private connections: Array<NetworkConnectionListEntry> = [];

	/**
	 * @property {NetworkConnectionListEntry|null} connectionToDisconnect Connection to disconnect
	 */
	private connectionToDisconnect: NetworkConnectionListEntry|null = null;

	/**
	 * @property {NetworkConnectionListEntry|null} connectionToDelete Connection to delete
	 */
	private connectionToDelete: NetworkConnectionListEntry|null = null;

	/**
	 * @var {Array<Modem>} modems Available modems
	 */
	private modems: Array<Modem> = [];

	/**
	 * @constant {Array<DataTableHeader>} fields GSM connections table fields
	 */
	private readonly headers: Array<DataTableHeader> = [
		{
			value: 'name',
			text: this.$t('network.connection.name').toString(),
		},
		{
			value: 'interfaceName',
			text: this.$t('network.connection.interface').toString(),
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

	/**
	 * @property {MonitCheck | null} monit Monit check
	 */
	private monit: MonitCheck | null = null;

	/**
	 * @property {string} monitCheckName Monit check name
	 */
	private monitCheckName = 'network_ppp0';

	/**
	 * @property {MonitService} monitService Monit service
	 */
	private monitService = useApiClient().getConfigServices().getMonitService();

	/**
	 * @property {NetworkConnectionService} service Network connection service
	 */
	private service = useApiClient().getNetworkServices().getNetworkConnectionService();

	/**
	 * Builds connections table
	 */
	mounted(): void {
		this.getConnections();
	}

	get showDeleteModal(): boolean {
		return this.connectionToDelete !== null;
	}

	get showDisconnectModal(): boolean {
		return this.connectionToDisconnect !== null;
	}

	/**
	 * Retrieves GSM connections
	 */
	private getConnections(): void {
		this.loading = true;
		this.service.list(NetworkConnectionType.GSM)
			.then((connections: NetworkConnectionListEntry[]) => {
				for (const i in connections) {
					const idx = this.modems.findIndex((modem: Modem) => connections[i].interfaceName === modem.interface);
					if (idx !== -1) {
						connections[i]['signal'] = this.modems[idx].signal;
						connections[i]['rssi'] = this.modems[idx].rssi;
					}
				}
				this.connections = connections;
				this.loading = false;
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'network.connection.messages.connectionFetchFailed'));
	}

	@Watch('gateway.info')
	public async getMonitCheck(): Promise<void> {
		if (!this.$store.getters['features/isEnabled']('monit') || this.$store.getters['gateway/board'] !== 'MICRORISC s.r.o. IQD-GW04') {
			return;
		}
		await this.monitService.getCheck(this.monitCheckName)
			.then((response: AxiosResponse<MonitCheck>) => {
				this.monit = response.data;
			})
			.catch(() => {
				this.monit = null;
			});
	}

	/**
	 * Establishes a GSM connection
	 * @param {NetworkConnectionListEntry} connection GSM connection
	 */
	private connect(connection: NetworkConnectionListEntry): void {
		this.$store.commit('spinner/SHOW');
		this.service.connect(connection.uuid, connection.interfaceName)
			.then(() => {
				this.getConnections();
				this.interfaces.getData();
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'network.connection.messages.connect.success',
						{connection: connection.name}
					).toString()
				);
			})
			.catch((error: AxiosError) => extendedErrorToast(
				error,
				'network.connection.messages.connect.failed',
				{connection: connection.name}
			));
	}

	/**
	 * Terminates a GSM connection
	 * @param {NetworkConnectionListEntry} connection GSM connection
	 * @param {boolean} confirmed Confirmed termination
	 */
	private async disconnect(connection: NetworkConnectionListEntry, confirmed: boolean): Promise<void> {
		if (this.$store.getters['features/isEnabled']('monit') &&
        this.$store.getters['gateway/board'] === 'MICRORISC s.r.o. IQD-GW04' &&
        connection.interfaceName === 'ttyAMA2'
		) {
			if (confirmed) {
				await this.monitService.disableCheck(this.monitCheckName);
			} else {
				this.connectionToDisconnect = connection;
				return Promise.resolve();
			}
		}
		this.connectionToDisconnect = null;
		this.$store.commit('spinner/SHOW');
		return this.service.disconnect(connection.uuid)
			.then(() => {
				this.getConnections();
				this.interfaces.getData();
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'network.connection.messages.disconnect.success',
						{interface: connection.interfaceName, connection: connection.name}
					).toString()
				);
			})
			.catch((error: AxiosError) => extendedErrorToast(
				error,
				'network.connection.messages.disconnect.failed',
				{connection: connection.name}
			));
	}

	/**
	 * Removes a GSM connection
	 * @param {NetworkConnectionListEntry} connection GSM connection
	 */
	private async remove(connection: NetworkConnectionListEntry): Promise<void> {
		this.$store.commit('spinner/SHOW');
		if (this.$store.getters['features/isEnabled']('monit') &&
        this.$store.getters['gateway/board'] === 'MICRORISC s.r.o. IQD-GW04' &&
        connection.interfaceName === 'ttyAMA2'
		) {
			await this.monitService.disableCheck(this.monitCheckName);
		}
		this.connectionToDelete = null;
		if (connection.interfaceName !== null) {
			const result = await this.service.disconnect(connection.uuid)
				.then(() => true)
				.catch(() => false);
			if (!result) {
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t(
						'network.connection.messages.removeFailed',
						{connection: connection.name},
					).toString()
				);
				return;
			}
		}
		this.service.delete(connection.uuid)
			.then(() => {
				this.getConnections();
				this.interfaces.getData();
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'network.connection.messages.removeSuccess',
						{connection: connection.name},
					).toString()
				);
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'network.connection.messages.removeFailed');
			});
	}

}
</script>
