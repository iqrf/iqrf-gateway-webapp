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
		<h1>{{ $t('network.wireless.title') }}</h1>
		<v-card>
			<v-card-text>
				<span v-if='interfacesLoaded && ifNameOptions.length === 0'>
					{{ $t('network.wireless.messages.noInterfaces') }}
				</span>
				<v-data-table
					v-else
					:headers='headers'
					:items='accessPoints'
					item-key='ssid'
					:no-data-text='$t("network.wireless.table.noAccessPoints")'
					show-expand
					:expanded.sync='expanded'
				>
					<template #top>
						<v-toolbar dense flat>
							<v-spacer />
							<v-btn
								color='primary'
								small
								@click='getAccessPoints'
							>
								<v-icon small>
									mdi-refresh
								</v-icon>
								{{ $t('forms.refresh') }}
							</v-btn>
						</v-toolbar>
					</template>
					<template #[`item.ssid`]='{item}'>
						<v-chip
							v-if='item.aps[0].inUse'
							color='success'
							small
							label
						>
							{{ $t('network.connection.states.connected') }}
						</v-chip>
						{{ item.ssid }}
					</template>
					<template #[`item.signal`]='{item}'>
						<v-progress-linear
							:value='item.aps[0].signal'
							:color='signalColor(item.aps[0].signal)'
							height='20'
						/>
					</template>
					<template #[`item.security`]='{item}'>
						{{ item.aps[0].security }}
					</template>
					<template #[`item.interfaceName`]='{item}'>
						{{ item.aps[0].interfaceName }}
					</template>
					<template #[`item.actions`]='{item}'>
						<v-btn
							v-if='item.aps[0].uuid'
							small
							color='primary'
							:to='"/ip-network/wireless/edit/" + item.aps[0].uuid'
						>
							<v-icon small>
								mdi-pencil
							</v-icon>
							{{ $t('table.actions.edit') }}
						</v-btn> <v-btn
							small
							:color='item.aps[0].inUse ? "error" : "success"'
							@click='item.aps[0].inUse ? hostname !== "localhost" ? disconnectAp = item.aps[0] : disconnect(item.aps[0].uuid, item.aps[0].ssid, item.aps[0].interfaceName):
								item.aps[0].uuid !== undefined ? connect(item.aps[0].uuid, item.aps[0].ssid, item.aps[0].interfaceName):
								addConnection(item.aps[0])'
						>
							<v-icon small>
								{{ item.aps[0].inUse ? 'mdi-link-off' : 'mdi-link-plus' }}
							</v-icon>
							{{ $t(`network.table.${item.aps[0].inUse ? 'dis' : ''}connect`) }}
						</v-btn> <v-btn
							v-if='item.aps[0].uuid'
							small
							color='error'
							@click='hostname === "localhost" ? removeConnection(item.aps[0].uuid, item.aps[0].ssid) : deleteAp = item.aps[0]'
						>
							<v-icon small>
								mdi-delete
							</v-icon>
							{{ $t('table.actions.delete') }}
						</v-btn>
						<v-dialog
							v-model='disconnectDialog'
							width='50%'
							persistent
							no-click-animation
						>
							<v-card>
								<v-card-title class='text-h5 warning'>
									{{ $t('network.wireless.modal.titleDisconnect') }}
								</v-card-title>
								<v-card-text>
									{{ $t('network.wireless.modal.promptDisconnect') }}
								</v-card-text>
								<v-card-actions>
									<v-spacer />
									<v-btn
										color='warning'
										@click='disconnect(disconnectAp.uuid, disconnectAp.ssid, disconnectAp.interfaceName)'
									>
										{{ $t('network.table.disconnect') }}
									</v-btn> <v-btn
										color='secondary'
										@click='disconnectAp = null'
									>
										{{ $t('forms.cancel') }}
									</v-btn>
								</v-card-actions>
							</v-card>
						</v-dialog>
						<v-dialog
							v-model='deleteDialog'
							width='50%'
							persistent
							no-click-animation
						>
							<v-card>
								<v-card-title class='text-h5 warning'>
									<v-icon>mdi-alert</v-icon>
									{{ $t('network.wireless.modal.titleDelete') }}
								</v-card-title>
								<v-card-text>
									{{ $t('network.wireless.modal.promptDelete') }}
								</v-card-text>
								<v-card-actions>
									<v-spacer />
									<v-btn
										color='warning'
										@click='removeConnection(deleteAp.uuid, deleteAp.ssid)'
									>
										{{ $t('table.actions.delete') }}
									</v-btn> <v-btn
										color='secondary'
										@click='deleteAp = null'
									>
										{{ $t('forms.cancel') }}
									</v-btn>
								</v-card-actions>
							</v-card>
						</v-dialog>
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
													<td>
														{{ item.aps[0].bssid }}
													</td>
												</tr>
												<tr>
													<th>{{ $t('network.wireless.table.mode') }}</th>
													<td>
														{{ item.aps[0].mode }}
													</td>
												</tr>
												<tr>
													<th>{{ $t('network.wireless.table.channel') }}</th>
													<td>
														{{ item.aps[0].channel }}
													</td>
												</tr>
												<tr>
													<th>{{ $t('network.wireless.table.rate') }}</th>
													<td>
														{{ item.aps[0].rate }}
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
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';

import {extendedErrorToast} from '@/helpers/errorToast';
import NetworkConnectionService, {ConnectionType} from '@/services/NetworkConnectionService';
import NetworkInterfaceService, {InterfaceType} from '@/services/NetworkInterfaceService';
import VersionService from '@/services/VersionService';

import {AxiosError, AxiosResponse} from 'axios';
import {DataTableHeader} from 'vuetify';
import {IOption} from '@/interfaces/coreui';
import {IAccessPoint, IAccessPointArray, NetworkConnection, NetworkInterface} from '@/interfaces/network';

@Component({
	components: {},
	metaInfo: {
		title: 'network.wireless.title'
	}
})

/**
 * Wifi connections list component
 */
export default class WifiConnections extends Vue {

	/**
	 * @var {Array<IAccessPointArray>} accessPoints Array of available access points
	 */
	private accessPoints: Array<IAccessPointArray> = [];

	/**
	 * @var {Array<IAccessPointArray>} expanded Expanded table rows
	 */
	private expanded: Array<IAccessPointArray> = [];

	/**
	 * @var {Array<IOption>} ifnameOptions Array of vuetify interface select options
	 */
	private ifNameOptions: Array<IOption> = [];

	/**
	 * @var {boolean} interfacesLoaded Indicates whether interfaces have been loaded
	 */
	private interfacesLoaded = false;

	/**
	 * @constant {Array<DataTableHeader>} headers Array of vuetify data table columns
	 */
	private headers: Array<DataTableHeader> = [
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
	 * @var {bolean} disconnectDialog Disconnect dialog visibility
	 */
	get disconnectDialog(): boolean {
		return this.disconnectAp !== null;
	}

	/**
	 * @var {boolean} deleteDialog Delete dialog visibility
	 */
	get deleteDialog(): boolean {
		return this.deleteAp !== null;
	}

	/**
	 * Retrieves data for table
	 */
	mounted(): void {
		this.hostname = window.location.hostname;
		this.getInterfaces();
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
	 * Retrieves Wi-Fi interfaces
	 */
	private getInterfaces(): void {
		this.$store.commit('spinner/SHOW');
		NetworkInterfaceService.list(InterfaceType.WIFI)
			.then((response: AxiosResponse) => {
				const interfaces: Array<IOption> = [];
				response.data.forEach((item: NetworkInterface) => {
					interfaces.push({text: item.name, value: item.name});
				});
				this.ifNameOptions = interfaces;
				this.interfacesLoaded = true;
				this.$store.commit('spinner/HIDE');
				if (this.ifNameOptions.length > 0) {
					this.getAccessPoints();
				}
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(
					error,
					'network.connection.messages.interfacesFetchFailed'
				);
			});
	}

	/**
	 * Retrieves list of available access points
	 */
	private getAccessPoints(): void {
		this.$store.commit('spinner/SHOW');
		NetworkConnectionService.listWifiAccessPoints()
			.then((response: AxiosResponse) => this.findConnections(response.data))
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
		this.accessPoints = [];
		return NetworkConnectionService.list(ConnectionType.WIFI)
			.then((response: AxiosResponse) => {
				const connections: Array<NetworkConnection> = response.data;
				const apArray: Array<IAccessPointArray> = [];
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
							ssid: ap.ssid,
							aps: [ap]
						});
					}
				}
				for (const i in apArray) {
					apArray[i].aps.splice(1);
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
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'network.wireless.messages.connectionsFailed'));
	}

	/**
	 * Connects to Wi-Fi access point
	 * @param {string} uuid Network connection UUID
	 * @param {string} name Network connection name
	 * @param {string} ifname Network interface name
	 */
	private connect(uuid: string, name: string, ifname: string|null): void {
		if (ifname === undefined && this.ifNameOptions.length === 1) {
			ifname = this.ifNameOptions[0].text.toString();
		}
		this.$store.commit('spinner/SHOW');
		NetworkConnectionService.connect(uuid, ifname)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'network.connection.messages.connect.success',
						{interface: ifname, connection: name}
					).toString());
				this.getAccessPoints();
			})
			.catch((error: AxiosError) => extendedErrorToast(
				error,
				'network.connection.messages.connect.failed',
				{connection: name}
			));
	}

	/**
	 * Disconnects from Wi-Fi access point
	 * @param {string} uuid Network connection UUID
	 * @param {string} name Network connection name
	 * @param {string} ifname Network interface name
	 */
	private disconnect(uuid: string, name: string, ifname: string): void {
		this.disconnectAp = null;
		this.$store.commit('spinner/SHOW');
		NetworkConnectionService.disconnect(uuid)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'network.connection.messages.disconnect.success',
						{interface: ifname, connection: name}
					).toString());
				this.getAccessPoints();
			})
			.catch((error: AxiosError) => {
				if (this.hostname === 'localhost') {
					extendedErrorToast(
						error,
						'network.connection.messages.disconnect.failed',
						{connection: name}
					);
				} else {
					this.tryRest(error, name, 'network.connection.messages.disconnect.failed', 'network.wireless.messages.disconnectUnavailable');
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
		VersionService.getWebappVersionRest()
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
			params: {
				ssid: ap.ssid,
				interfaceName: ap.interfaceName!,
				wifiMode: ap.mode,
				wifiSecurity: this.getSecurityType(ap.security)
			}
		});
	}

	/**
	 * Returns security type code from type string
	 * @param {string} type security type string
	 * @returns {string} security type code
	 */
	private getSecurityType(type: string): string {
		if (type === 'Open') {
			return 'open';
		} else if (type === 'WEP') {
			return 'wep';
		} else if (['WPA-Personal', 'WPA2-Personal', 'WPA3-Personal'].includes(type)) {
			return 'wpa-psk';
		} else if (['WPA-Enterprise', 'WPA2-Enterprise', 'WPA3-Enterprise'].includes(type)) {
			return 'wpa-eap';
		}
		return '';
	}


	/**
	 * Removes Wi-Fi access point connection
	 * @param {string} uuid Network connection UUID
	 * @param {string} name Network connection name
	 */
	private removeConnection(uuid: string, name: string): void {
		this.deleteAp = null;
		this.$store.commit('spinner/SHOW');
		NetworkConnectionService.remove(uuid)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'network.connection.messages.removeSuccess',
						{connection: name}
					).toString()
				);
				this.deletedAp = true;
				this.getAccessPoints();
			})
			.catch((error: AxiosError) => extendedErrorToast(
				error,
				'network.connection.messages.removeFailed',
				{connection: name}
			));
	}
}
</script>
