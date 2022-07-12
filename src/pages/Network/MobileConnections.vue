<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

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
		<v-card>
			<div v-if='interfacesLoaded && noInterfaces'>
				<v-card-text>
					{{ $t('network.mobile.messages.noInterfaces') }}
				</v-card-text>
			</div>
			<div v-else>
				<v-card-text>
					<v-data-table
						:headers='headers'
						:items='connections'
						:no-data-text='$t("network.mobile.messages.noConnections")'
					>
						<template #top>
							<v-toolbar dense flat>
								{{ $t('network.connection.title') }}
								<v-spacer />
								<v-btn
									color='success'
									small
									to='/ip-network/mobile/add'
								>
									<v-icon>
										mdi-plus
									</v-icon>
									Add connection
								</v-btn>
							</v-toolbar>
						</template>
						<template #[`item.interfaceName`]='{item}'>
							{{ item.interfaceName }}
						</template>
						<template #[`item.signal`]='{item}'>
							<v-progress-linear
								v-if='item.signal !== undefined'
								:value='item.signal'
								:color='signalColor(item.signal)'
								height='20'
							/>
						</template>
						<template #[`item.rssi`]='{item}'>
							{{ item.rssi }} dBm
						</template>
						<template #actions='{item}'>
							<v-btn
								small
								:color='item.interfaceName === null ? "success" : "danger"'
								@click='item.interfaceName === null ? connect(item) : disconnect(item, false)'
							>
								<v-icon small>
									{{ item.interfaceName === null ? 'mdi-link-plus' : 'mdi-link-off' }}
								</v-icon>
								{{ $t(`network.table.${item.interfaceName === null ? '' : 'dis'}connect`) }}
							</v-btn> <v-btn
								small
								color='primary'
								:to='"/ip-network/mobile/edit/" + item.uuid'
							>
								<v-icon small>
									mdi-pencil
								</v-icon>
								{{ $t('table.actions.edit') }}
							</v-btn> <v-btn
								size='sm'
								color='error'
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
			</div>
		</v-card>
		<v-card>
			<NetworkOperators />
		</v-card>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import NetworkOperators from '@/components/Network/NetworkOperators.vue';

import {extendedErrorToast} from '@/helpers/errorToast';
import NetworkConnectionService, {ConnectionType} from '@/services/NetworkConnectionService';
import NetworkInterfaceService from '@/services/NetworkInterfaceService';

import {AxiosError, AxiosResponse} from 'axios';
import {DataTableHeader} from 'vuetify';
import {IModem, NetworkConnection} from '@/interfaces/network';

@Component({
	components: {
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
	 * @var {boolean} interfacesLoaded Indicates that interfaces have been loaded
	 */
	private interfacesLoaded = false;

	/**
	 * @var {boolean} noInterfaces Indicates that no GSM interfaces were found
	 */
	private noInterfaces = false;

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
	private headers: Array<DataTableHeader> = [
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
			value: 'signal',
			text: this.$t('network.mobile.table.signal').toString(),
			filterable: false,
			sortable: false,
		},
		{
			value: 'rssi',
			text: this.$t('network.mobile.table.rssi').toString(),
			filterable: false,
			sortable: false,
		},
		{
			value: 'actions',
			text: this.$t('table.actions.title').toString(),
			filterable: false,
			sortable: false,
		},
	];

	/**
	 * Builds connections table
	 */
	mounted(): void {
		this.getModems();
	}

	/**
	 * Returns color for progress bar depending on signal strength
	 */
	private signalColor(signal: number): string {
		if (signal >= 67) {
			return 'success';
		} else if (signal >= 34) {
			return 'warning';
		} else {
			return 'error';
		}
	}

	/**
	 * Retrieves GSM interfaces
	 */
	private getModems(): void {
		this.noInterfaces = false;
		this.$store.commit('spinner/SHOW');
		NetworkInterfaceService.listModems()
			.then((response: AxiosResponse) => {
				this.interfacesLoaded = true;
				this.$store.commit('spinner/HIDE');
				if (response.data.length > 0) {
					this.modems = response.data;
					this.getConnections();
				} else {
					this.noInterfaces = true;
				}
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'network.connection.messages.interfacesFetchFailed'));
	}

	/**
	 * Retrieves GSM connections
	 */
	private getConnections(): void {
		this.$store.commit('spinner/SHOW');
		NetworkConnectionService.list(ConnectionType.GSM)
			.then((response: AxiosResponse) => {
				const connections: Array<NetworkConnection> = response.data;
				for (const i in connections) {
					const idx = this.modems.findIndex((modem: IModem) => connections[i].interfaceName === modem.interface);
					if (idx !== -1) {
						connections[i]['signal'] = this.modems[idx].signal;
						connections[i]['rssi'] = this.modems[idx].rssi;
					}
				}
				this.connections = connections;
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'network.connection.messages.connectionFetchFailed'));
	}

	/**
	 * Establishes a GSM connection
	 * @param {NetworkConnection} connection GSM connection
	 */
	private connect(connection: NetworkConnection): void {
		this.$store.commit('spinner/SHOW');
		NetworkConnectionService.connect(connection.uuid)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'network.connection.messages.connect.success',
						{connection: connection.name}
					).toString()
				);
				this.getModems();
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
					this.getModems();
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
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'network.connection.messages.removeSuccess',
						{connection: connection.name},
					).toString()
				);
				this.getModems();
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'network.connection.messages.removeFailed');
			});
	}
}
</script>
