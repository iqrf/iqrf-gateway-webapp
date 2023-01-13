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
		<h1>{{ $t('network.mobile.title') }}</h1>
		<GsmInterfaces
			ref='interfaces'
			class='mb-5'
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
							@click='item.interfaceName === null ? remove(item) : disconnect(item, true)'
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
	</div>
</template>

<script lang='ts'>
import {Component, Ref, Vue} from 'vue-property-decorator';
import GsmInterfaces from '@/components/Network/GsmInterfaces.vue';
import NetworkOperators from '@/components/Network/NetworkOperators.vue';

import {ConnectionType} from '@/enums/Network/ConnectionType';
import {extendedErrorToast} from '@/helpers/errorToast';

import NetworkConnectionService from '@/services/NetworkConnectionService';

import {AxiosError} from 'axios';
import {DataTableHeader} from 'vuetify';
import {IModem} from '@/interfaces/Network/Mobile';
import {NetworkConnection} from '@/interfaces/Network/Connection';

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
	 * @var {Array<NetworkConnections>} connections Existing mobile connections
	 */
	private connections: Array<NetworkConnection> = [];

	/**
	 * @var {Array<IModem>} modems Available modems
	 */
	private modems: Array<IModem> = [];

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
	 * Builds connections table
	 */
	mounted(): void {
		this.getConnections();
	}

	/**
	 * Retrieves GSM connections
	 */
	private getConnections(): void {
		this.loading = true;
		NetworkConnectionService.list(ConnectionType.GSM)
			.then((connections: NetworkConnection[]) => {
				for (const i in connections) {
					const idx = this.modems.findIndex((modem: IModem) => connections[i].interfaceName === modem.interface);
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

	/**
	 * Establishes a GSM connection
	 * @param {NetworkConnection} connection GSM connection
	 */
	private connect(connection: NetworkConnection): void {
		this.$store.commit('spinner/SHOW');
		NetworkConnectionService.connect(connection.uuid, connection.interfaceName)
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
	 * @param {NetworkConnection} connection GSM connection
	 * @param {boolean} remove Remove connection
	 */
	private disconnect(connection: NetworkConnection, remove: boolean): Promise<void> {
		this.$store.commit('spinner/SHOW');
		return NetworkConnectionService.disconnect(connection.uuid)
			.then(() => {
				this.getConnections();
				this.interfaces.getData();
				this.$store.commit('spinner/HIDE');
				if (remove) {
					this.remove(connection);
				} else {
					this.$toast.success(
						this.$t(
							'network.connection.messages.disconnect.success',
							{interface: connection.interfaceName, connection: connection.name}
						).toString()
					);
				}
			})
			.catch((error: AxiosError) => extendedErrorToast(
				error,
				'network.connection.messages.disconnect.failed',
				{connection: connection.name}
			));
	}

	/**
	 * Removes a GSM connection
	 * @param {NetworkConnection} connection GSM connection
	 */
	private remove(connection: NetworkConnection): void {
		this.$store.commit('spinner/SHOW');
		NetworkConnectionService.remove(connection.uuid)
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
