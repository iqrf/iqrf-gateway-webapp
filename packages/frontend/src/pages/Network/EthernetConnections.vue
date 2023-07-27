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
		<h1>{{ $t('network.ethernet.title') }}</h1>
		<NetworkInterfaces
			ref='interfaces'
			class='mb-5'
			:type='InterfaceType.ETHERNET'
		/>
		<v-card>
			<v-card-text>
				<v-data-table
					:loading='loading'
					:headers='headers'
					:items='connections'
					:no-data-text='$t("network.connection.messages.noConnections")'
				>
					<template #top>
						<v-toolbar dense flat>
							<h5>{{ $t('network.connection.title') }}</h5>
							<v-spacer />
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
					<template #[`item.name`]='{item}'>
						<v-chip
							v-if='item.interfaceName !== null'
							color='success'
							label
							small
						>
							{{ $t('network.connection.states.connected') }}
						</v-chip>
						{{ item.name }}
					</template>
					<template #[`item.interfaceName`]='{item}'>
						{{ item.interfaceName }}
					</template>
					<template #[`item.actions`]='{item}'>
						<v-btn
							v-if='item.interfaceName === null'
							class='mr-1'
							color='success'
							small
							@click='connect(item)'
						>
							<v-icon small>
								mdi-link
							</v-icon>
							{{ $t('network.table.connect') }}
						</v-btn>
						<v-btn
							v-else
							class='mr-1'
							color='error'
							small
							@click='hostname === "localhost" ? disconnect(item) : connectionModal = item'
						>
							<v-icon small>
								mdi-link-off
							</v-icon>
							{{ $t('network.table.disconnect') }}
						</v-btn>
						<v-btn
							color='primary'
							small
							:to='"/ip-network/ethernet/edit/" + item.uuid'
						>
							<v-icon small>
								mdi-pencil
							</v-icon>
							{{ $t('table.actions.edit') }}
						</v-btn>
					</template>
				</v-data-table>
			</v-card-text>
		</v-card>
		<v-dialog
			v-model='connectionModal'
			width='50%'
			persistent
			no-click-animation
		>
			<v-card>
				<v-card-title>
					<h5>{{ $t('network.ethernet.modal.title') }}</h5>
				</v-card-title>
				<v-card-text>
					{{ $t('network.ethernet.modal.prompt') }}
				</v-card-text>
				<v-card-actions>
					<v-spacer />
					<v-btn
						@click='connectionModal = null'
					>
						{{ $t('forms.cancel') }}
					</v-btn>
					<v-btn
						color='warning'
						@click='disconnect(connectionModal)'
					>
						{{ $t('network.table.disconnect') }}
					</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>
	</div>
</template>

<script lang='ts'>
import {AxiosError} from 'axios';
import {Component, Ref, Vue} from 'vue-property-decorator';
import {DataTableHeader} from 'vuetify';

import NetworkInterfaces from '@/components/Network/NetworkInterfaces.vue';

import {ConnectionType} from '@/enums/Network/ConnectionType';
import {InterfaceType} from '@/enums/Network/InterfaceType';
import {extendedErrorToast} from '@/helpers/errorToast';

import NetworkConnectionService from '@/services/NetworkConnectionService';

import {NetworkConnection} from '@/interfaces/Network/Connection';
import {useApiClient} from '@/services/ApiClient';

@Component({
	components: {
		NetworkInterfaces,
	},
	data: () => ({
		InterfaceType,
	}),
	metaInfo: {
		title: 'network.ethernet.title',
	},
})

export default class EthernetConnections extends Vue {

	/**
	 * @property {NetworkInterfaces} interfaces Network interfaces component
	 */
	@Ref('interfaces') interfaces!: NetworkInterfaces;

	/**
	 * @property {boolean} loading Loading state
	 */
	private loading = true;

	/**
	 * @var {string} hostname Window hostname
	 */
	private hostname = '';

	/**
	 * @var {Array<NetworkConnection>} connections Array of existing network connections
	 */
	private connections: Array<NetworkConnection> = [];

	/**
	 * @var {NetworkConnection|null} connectionModal Auxiliary connection variable for disconnect modal
	 */
	private connectionModal: NetworkConnection|null = null;

	/**
	 * @constant {Array<DataTableHeader>} headers Data table headers
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
	 * Retrieves interfaces and connections
	 */
	mounted(): void {
		this.hostname = window.location.hostname;
		this.getConnections();
	}

	/**
	 * Retrieves ethernet connections
	 */
	private getConnections(): void {
		this.loading = true;
		NetworkConnectionService.list(ConnectionType.Ethernet)
			.then((connections: NetworkConnection[]) => {
				this.connections = connections;
				this.loading = false;
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'network.connection.messages.connectionFetchFailed'));
	}

	/**
	 * Establishes a connection using the specified configuration
	 * @param {NetworkConnection} connection Network connection configuration
	 */
	private connect(connection: NetworkConnection): void {
		this.$store.commit('spinner/SHOW');
		NetworkConnectionService.connect(connection.uuid, connection.interfaceName)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'network.connection.messages.connect.success',
						{connection: connection.name}
					).toString());
				this.getConnections();
				this.interfaces.getInterfaces();
			})
			.catch((error: AxiosError) => extendedErrorToast(
				error,
				'network.connection.messages.connect.failed',
				{connection: connection.name}
			));
	}

	/**
	 * Terminates a connection
	 * @param {NetworkConnection} connection Network connection configuration
	 */
	private disconnect(connection: NetworkConnection): void {
		this.connectionModal = null;
		this.$store.commit('spinner/SHOW');
		NetworkConnectionService.disconnect(connection.uuid)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'network.connection.messages.disconnect.success',
						{interface: connection.interfaceName, connection: connection.name}
					).toString());
				this.getConnections();
				this.interfaces.getInterfaces();
			})
			.catch((error: AxiosError) => {
				if (this.hostname === 'localhost') {
					extendedErrorToast(
						error,
						'network.connection.messages.disconnect.failed',
						{connection: connection.name}
					);
				} else {
					this.tryRest(error, connection.name);
				}
			});
	}

	/**
	 * Attempts a REST API request to check availability of this address
	 * @param {AxiosError} disconnectError Axios error from disconnect request
	 * @param {string} name Connection name
	 */
	private tryRest(disconnectError: AxiosError, name: string): void {
		useApiClient().getVersionService().getWebapp()
			.then(() => {
				extendedErrorToast(
					disconnectError,
					'network.connection.messages.disconnect.failed',
					{connection: name}
				);
			})
			.catch((error: AxiosError) => {
				if (error.response === undefined) {
					this.$store.commit('spinner/HIDE');
					this.$store.commit('blocking/SHOW', this.$t('network.ethernet.messages.disconnectUnavailable').toString());
				} else {
					extendedErrorToast(
						disconnectError,
						'network.connection.messages.disconnect.failed',
						{connection: name}
					);
				}
			});

	}
}
</script>
