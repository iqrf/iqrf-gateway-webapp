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
		<h1>{{ $t('network.wireless.title') }}</h1>
		<NetworkInterfaces
			ref='interfaces'
			class='mb-5'
			:type='InterfaceType.WIFI'
		/>
		<v-card>
			<v-card-text>
				<v-data-table
					:loading='loading'
					:headers='headers'
					:items='accessPoints'
					item-key='ssid'
					:no-data-text='$t("network.wireless.table.noAccessPoints")'
					show-expand
					single-expand
					:expanded.sync='expanded'
				>
					<template #top>
						<v-toolbar dense flat>
							<h5>{{ $t('network.wireless.table.accessPoints') }}</h5>
							<v-spacer />
							<v-btn
								color='primary'
								small
								@click='getAccessPoints'
							>
								<v-icon small>
									mdi-refresh
								</v-icon>
							</v-btn>
						</v-toolbar>
					</template>
					<template #[`item.ssid`]='{item}'>
						<v-chip
							v-if='item.aps[0].inUse'
							color='success'
							label
							small
						>
							{{ $t('network.connection.states.connected') }}
						</v-chip>
						<v-chip
							v-if='item.ssid.length === 0'
							color='secondary'
							label
							small
						>
							{{ $t('network.wireless.table.hidden') }}
						</v-chip>
						{{ item.ssid }}
					</template>
					<template #[`item.security`]='{item}'>
						{{ item.aps[0].security }}
					</template>
					<template #[`item.signal`]='{item}'>
						<SignalIndicator :signal='item.aps[0].signal' />
					</template>
					<template #[`item.interfaceName`]='{item}'>
						{{ item.aps[0].interfaceName }}
					</template>
					<template #[`item.actions`]='{item}'>
						<v-btn
							:color='item.aps[0].inUse ? "error" : "success"'
							small
							@click='item.aps[0].inUse ? hostname !== "localhost" ? disconnectAp = item.aps[0] : disconnect(item.aps[0]):
								item.aps[0].uuid !== undefined ? connect(item.aps[0]):
								addConnection(item.aps[0])'
						>
							<v-icon small>
								{{ item.aps[0].inUse ? 'mdi-link-off' : 'mdi-link-plus' }}
							</v-icon>
							{{ $t(`network.table.${item.aps[0].inUse ? 'dis' : ''}connect`) }}
						</v-btn>
						<v-btn
							v-if='item.aps[0].uuid'
							class='ml-1'
							color='primary'
							small
							:to='"/ip-network/wireless/edit/" + item.aps[0].uuid'
						>
							<v-icon small>
								mdi-pencil
							</v-icon>
							{{ $t('table.actions.edit') }}
						</v-btn>
						<v-btn
							v-if='item.aps[0].uuid'
							class='ml-1'
							color='error'
							small
							@click='hostname === "localhost" ? removeConnection(item.aps[0]) : deleteAp = item.aps[0]'
						>
							<v-icon small>
								mdi-delete
							</v-icon>
							{{ $t('table.actions.delete') }}
						</v-btn>
					</template>
					<template #expanded-item='{headers, item}'>
						<td :colspan='headers.length'>
							<v-container fluid>
								<v-row>
									<v-col>
										<div class='datatable-expansion-table'>
											<table>
												<caption>
													<b>{{ $t('network.wireless.table.details') }}</b>
												</caption>
												<tr>
													<th>{{ $t('network.wireless.table.bssid') }}</th>
													<td v-for='ap in item.aps' :key='ap.bssid'>
														{{ ap.bssid }}
													</td>
												</tr>
												<tr>
													<th>{{ $t('network.wireless.table.mode') }}</th>
													<td v-for='ap in item.aps' :key='ap.bssid'>
														{{ ap.mode }}
													</td>
												</tr>
												<tr>
													<th>{{ $t('network.wireless.table.channel') }}</th>
													<td v-for='ap in item.aps' :key='ap.bssid'>
														{{ ap.channel }}
													</td>
												</tr>
												<tr>
													<th>{{ $t('network.wireless.table.rate') }}</th>
													<td v-for='ap in item.aps' :key='ap.bssid'>
														{{ ap.rate }}
													</td>
												</tr>
											</table>
										</div>
									</v-col>
								</v-row>
							</v-container>
						</td>
					</template>
				</v-data-table>
			</v-card-text>
		</v-card>
		<v-dialog
			v-model='disconnectAp'
			width='50%'
			persistent
			no-click-animation
		>
			<v-card>
				<v-card-title>
					{{ $t('network.wireless.modal.titleDisconnect') }}
				</v-card-title>
				<v-card-text>
					{{ $t('network.wireless.modal.promptDisconnect') }}
				</v-card-text>
				<v-card-actions>
					<v-spacer />
					<v-btn
						@click='disconnectAp = null'
					>
						{{ $t('forms.cancel') }}
					</v-btn>
					<v-btn
						color='warning'
						@click='disconnect(disconnectAp)'
					>
						{{ $t('network.table.disconnect') }}
					</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>
		<v-dialog
			v-model='deleteAp'
			width='50%'
			persistent
			no-click-animation
		>
			<v-card>
				<v-card-title>
					{{ $t('network.wireless.modal.titleDelete') }}
				</v-card-title>
				<v-card-text>
					{{ $t('network.wireless.modal.promptDelete') }}
				</v-card-text>
				<v-card-actions>
					<v-spacer />
					<v-btn
						@click='deleteAp = null'
					>
						{{ $t('forms.cancel') }}
					</v-btn>
					<v-btn
						color='warning'
						@click='removeConnection(deleteAp)'
					>
						{{ $t('table.actions.delete') }}
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
import SignalIndicator from '@/components/Network/SignalIndicator.vue';

import {extendedErrorToast} from '@/helpers/errorToast';

import {ConnectionType} from '@/enums/Network/ConnectionType';
import {InterfaceType} from '@/enums/Network/InterfaceType';

import NetworkConnectionService from '@/services/NetworkConnectionService';

import {IAccessPoint, IAccessPoints} from '@/interfaces/Network/Wifi';
import {NetworkConnection} from '@/interfaces/Network/Connection';
import {useApiClient} from '@/services/ApiClient';

@Component({
	components: {
		NetworkInterfaces,
		SignalIndicator,
	},
	data: () => ({
		InterfaceType,
	}),
	metaInfo: {
		title: 'network.wireless.title',
	},
})

/**
 * Wifi connections list component
 */
export default class WifiConnections extends Vue {

	/**
	 * @property {NetworkInterfaces} interfaces Network interfaces component
	 */
	@Ref('interfaces') readonly interfaces!: NetworkInterfaces;

	/**
	 * @property {boolean} loading Loading state
	 */
	private loading = true;

	/**
	 * @var {Array<IAccessPoint>} accessPoints Array of available access points
	 */
	private accessPoints: Array<IAccessPoints> = [];

	/**
	 * @var {Array<IAccessPointArray>} expanded Expanded table rows
	 */
	private expanded: Array<IAccessPoints> = [];

	/**
	 * @constant {Array<DataTableHeader>} headers Data table headers
	 */
	private readonly headers: Array<DataTableHeader> = [
		{
			value: 'ssid',
			text: this.$t('network.wireless.table.ssid').toString(),
		},
		{
			value: 'signal',
			text: this.$t('network.wireless.table.signal').toString(),
			filterable: false,
		},
		{
			value: 'security',
			text: this.$t('network.wireless.table.security').toString(),
			filterable: false,
			sortable: false,
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
		{
			value: 'data-table-expand',
			text: '',
		},
	];

	/**
	 * @var {IAccessPoint|null} disconnectAp Access point used in disconnect modal
	 */
	private disconnectAp: IAccessPoint|null = null;

	/**
	 * @var {IAccessPoint|null} deleteAp Access point used in delete modal
	 */
	private deleteAp: IAccessPoint|null = null;

	/**
	 * @var {boolean} deletedAP Indicates that AP has just been deleted
	 */
	private deletedAp = false;

	/**
	 * @var {string} Hostname Window hostname
	 */
	private hostname = '';

	/**
	 * Retrieves data for table
	 */
	mounted(): void {
		this.hostname = window.location.hostname;
		this.getAccessPoints();
	}

	/**
	 * Retrieves list of available access points
	 */
	private getAccessPoints(): void {
		this.loading = true;
		NetworkConnectionService.listWifiAccessPoints()
			.then((response: IAccessPoint[]) => this.findConnections(response))
			.catch((error: AxiosError) => {
				if (!this.deletedAp) {
					extendedErrorToast(
						error,
						'network.wireless.messages.listFailed'
					);
				} else {
					if (this.hostname === 'localhost') {
						extendedErrorToast(
							error,
							'network.wireless.messages.listFailed'
						);
					} else {
						this.tryRest(error, 'unknown', 'network.connection.messages.removeFailed', 'network.wireless.messages.removeUnavailable');
					}
					this.deletedAp = false;
				}
			});
	}

	/**
	 * Retrieves list of existing Wi-Fi connections and adds UUID to matching access points
	 * @param {Array<IAccessPoint>} accessPoints Array of available access points
	 */
	private findConnections(accessPoints: Array<IAccessPoint>): Promise<void> {
		return NetworkConnectionService.list(ConnectionType.WiFi)
			.then((connections: NetworkConnection[]) => {
				const apArray: Array<IAccessPoints> = [];
				for (const ap of accessPoints) {
					const idx = apArray.findIndex(item => item.ssid === ap.ssid);
					if (idx !== -1) {
						if (ap.inUse) {
							apArray[idx].aps.unshift(ap);
						} else {
							apArray[idx].aps.push(ap);
						}
					} else {
						apArray.push({
							aps: [ap],
							ssid: ap.ssid,
							showDetails: false,
						});
					}
				}
				for (const i in apArray) {
					const re = new RegExp('^' + apArray[i].ssid + '(\\s\\d+)?$');
					const filteredConnections = connections.filter((item: NetworkConnection) => re.test(item.name));
					if (filteredConnections.length === 0) {
						continue;
					}
					for (const con of filteredConnections) {
						if (con.interfaceName !== null) {
							apArray[i].aps[0].interfaceName = con.interfaceName;
							apArray[i].aps[0].uuid = con.uuid;
							break;
						}
						if (apArray[i].aps[0].uuid === undefined) {
							apArray[i].aps[0].uuid = con.uuid;
						}
					}
				}
				this.accessPoints = apArray;
				this.loading = false;
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'network.wireless.messages.connectionsFailed'));
	}

	/**
	 * Connects to Wi-Fi access point
	 * @param {IAccessPoint} ap Access point to connect to
	 */
	private connect(ap: IAccessPoint): void {
		if (ap.uuid === undefined) {
			return;
		}
		this.$store.commit('spinner/SHOW');
		NetworkConnectionService.connect(ap.uuid, ap.interfaceName)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'network.connection.messages.connect.success',
						{interface: ap.interfaceName, connection: ap.ssid}
					).toString());
				this.getAccessPoints();
				this.interfaces.getInterfaces();
			})
			.catch((error: AxiosError) => extendedErrorToast(
				error,
				'network.connection.messages.connect.failed',
				{connection: ap.ssid}
			));
	}

	/**
	 * Disconnects from Wi-Fi access point
	 * @param {IAccessPoint} ap Access point to disconnect from
	 */
	private disconnect(ap: IAccessPoint): void {
		if (ap.uuid === undefined) {
			return;
		}
		this.disconnectAp = null;
		this.$store.commit('spinner/SHOW');
		NetworkConnectionService.disconnect(ap.uuid)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'network.connection.messages.disconnect.success',
						{interface: ap.interfaceName, connection: ap.ssid}
					).toString());
				this.getAccessPoints();
				this.interfaces.getInterfaces();
			})
			.catch((error: AxiosError) => {
				if (this.hostname === 'localhost') {
					extendedErrorToast(
						error,
						'network.connection.messages.disconnect.failed',
						{connection: ap.ssid}
					);
				} else {
					this.tryRest(error, ap.ssid, 'network.connection.messages.disconnect.failed', 'network.wireless.messages.disconnectUnavailable');
				}
			});
	}

	/**
	 * Attempts a REST API request to check if web interface is available
	 * @param {AxiosError} triggerError Axios error from previous request
	 * @param {string} name Connection name
	 * @param {string} message Toast message from previous request
	 */
	private tryRest(triggerError: AxiosError, name: string, errorMessage: string, divMessage: string): void {
		useApiClient().getVersionService().getWebapp()
			.then(() => {
				extendedErrorToast(
					triggerError,
					errorMessage,
					{connection: name}
				);
			})
			.catch((error: AxiosError) => {
				if (error.response === undefined) {
					this.$store.commit('spinner/HIDE');
					this.$store.commit('blocking/SHOW', this.$t(divMessage).toString());
				} else {
					extendedErrorToast(
						triggerError,
						errorMessage,
						{connection: name}
					);
				}
			});
	}

	/**
	 * Redirects to connection form with required properties
	 * @param {IAccessPoint} ap Access point meta
	 */
	private addConnection(ap: IAccessPoint) {
		this.$router.push({
			name: 'add-wireless-connection',
			params: {ap: JSON.stringify(ap)},
		});
	}


	/**
	 * Removes Wi-Fi access point connection
	 * @param {IAccessPoint} ap Access point to remove
	 */
	private removeConnection(ap: IAccessPoint): void {
		if (ap.uuid === undefined) {
			return;
		}
		this.deleteAp = null;
		this.$store.commit('spinner/SHOW');
		NetworkConnectionService.remove(ap.uuid)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'network.connection.messages.removeSuccess',
						{connection: ap.ssid}
					).toString()
				);
				this.deletedAp = true;
				this.getAccessPoints();
				this.interfaces.getInterfaces();
			})
			.catch((error: AxiosError) => extendedErrorToast(
				error,
				'network.connection.messages.removeFailed',
				{connection: ap.ssid}
			));
	}
}
</script>
