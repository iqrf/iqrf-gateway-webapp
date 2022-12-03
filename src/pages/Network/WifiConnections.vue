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
		<CCard>
			<div v-if='interfacesLoaded && ifNameOptions.length === 0'>
				<CCardBody>
					{{ $t('network.wireless.messages.noInterfaces') }}
				</CCardBody>
			</div>
			<div v-else>
				<CCardHeader class='border-0'>
					{{ $t('network.wireless.table.accessPoints') }}
					<CButton
						style='float: right;'
						color='primary'
						size='sm'
						@click='getAccessPoints'
					>
						<CIcon :content='cilReload' size='sm' />
						{{ $t('forms.refresh') }}
					</CButton>
				</CCardHeader>
				<CCardBody>
					<CDataTable
						:fields='tableFields'
						:items='accessPoints'
						:column-filter='true'
						:items-per-page='20'
						:pagination='true'
						:sorter='{external: false, resetable: true}'
					>
						<template #no-items-view='{}'>
							{{ $t('network.wireless.table.noAccessPoints') }}
						</template>
						<template #ssid='{item}'>
							<td class='table-ssid'>
								<div>
									<CBadge
										v-if='item.aps[0].inUse'
										color='success'
									>
										{{ $t('network.connection.states.connected') }}
									</CBadge>
									{{ item.ssid }}
								</div>
							</td>
						</template>
						<template #security='{item}'>
							<td>
								{{ item.aps[0].security }}
							</td>
						</template>
						<template #signal='{item}'>
							<td>
								<CProgress
									:value='item.aps[0].signal'
									:color='signalColor(item.aps[0].signal)'
								/>
							</td>
						</template>
						<template #interfaceName='{item}'>
							<td>
								{{ item.aps[0].interfaceName }}
							</td>
						</template>
						<template #actions='{item}'>
							<td class='col-actions'>
								<CButton
									size='sm'
									:color='item.aps[0].inUse ? "danger" : "success"'
									@click='item.aps[0].inUse ? hostname !== "localhost" ? disconnectAp = item.aps[0] : disconnect(item.aps[0].uuid, item.aps[0].ssid, item.aps[0].interfaceName):
										item.aps[0].uuid !== undefined ? connect(item.aps[0].uuid, item.aps[0].ssid, item.aps[0].interfaceName):
										addConnection(item.aps[0])'
								>
									<CIcon :content='item.aps[0].inUse ? cilLinkBroken : cilLink' size='sm' />
									{{ $t(`network.table.${item.aps[0].inUse ? 'dis' : ''}connect`) }}
								</CButton> <CButton
									v-if='item.aps[0].uuid'
									size='sm'
									color='primary'
									:to='"/ip-network/wireless/edit/" + item.aps[0].uuid'
								>
									<CIcon :content='cilPencil' size='sm' />
									{{ $t('table.actions.edit') }}
								</CButton> <CButton
									v-if='item.aps[0].uuid'
									size='sm'
									color='danger'
									@click='hostname === "localhost" ? removeConnection(item.aps[0].uuid, item.aps[0].ssid) : deleteAp = item.aps[0]'
								>
									<CIcon :content='cilTrash' size='sm' />
									{{ $t('table.actions.delete') }}
								</CButton>
							</td>
						</template>
						<template #show_details='{item}'>
							<td class='py-2'>
								<CButton
									color='info'
									size='sm'
									@click='item.aps[0].showDetails = !item.aps[0].showDetails'
								>
									<CIcon :content='cilInfo' />
								</CButton>
							</td>
						</template>
						<template #details='{item}'>
							<CCollapse :show='item.aps[0].showDetails'>
								<CCardBody>
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
								</CCardBody>
							</CCollapse>
						</template>
					</CDataTable>
				</CCardBody>
			</div>
		</CCard>
		<CModal
			:show='disconnectAp !== null'
			color='warning'
			size='lg'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('network.wireless.modal.titleDisconnect') }}
				</h5>
			</template>
			{{ $t('network.wireless.modal.promptDisconnect') }}
			<template #footer>
				<CButton
					color='warning'
					@click='disconnect(disconnectAp.uuid, disconnectAp.ssid, disconnectAp.interfaceName)'
				>
					{{ $t('network.table.disconnect') }}
				</CButton> <CButton
					color='secondary'
					@click='disconnectAp = null'
				>
					{{ $t('forms.cancel') }}
				</CButton>
			</template>
		</CModal>
		<CModal
			:show='deleteAp !== null'
			color='warning'
			size='lg'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('network.wireless.modal.titleDelete') }}
				</h5>
			</template>
			{{ $t('network.wireless.modal.promptDelete') }}
			<template #footer>
				<CButton
					color='warning'
					@click='removeConnection(deleteAp.uuid, deleteAp.ssid)'
				>
					{{ $t('table.actions.delete') }}
				</CButton> <CButton
					color='secondary'
					@click='deleteAp = null'
				>
					{{ $t('forms.cancel') }}
				</CButton>
			</template>
		</CModal>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CBadge, CCard, CCardBody, CCardHeader, CDataTable, CIcon, CModal, CProgress, CSelect} from '@coreui/vue/src';

import {cilInfo, cilLink, cilLinkBroken, cilPencil, cilReload, cilTrash} from '@coreui/icons';
import {extendedErrorToast} from '@/helpers/errorToast';

import NetworkConnectionService, {ConnectionType} from '@/services/NetworkConnectionService';
import NetworkInterfaceService, {InterfaceType} from '@/services/NetworkInterfaceService';
import VersionService from '@/services/VersionService';

import {AxiosError, AxiosResponse} from 'axios';
import {IField, IOption} from '@/interfaces/Coreui';
import {IAccessPoint, IAccessPointArray} from '@/interfaces/Network/Wifi';
import {NetworkConnection, NetworkInterface} from '@/interfaces/Network/Connection';

@Component({
	components: {
		CBadge,
		CCard,
		CCardBody,
		CCardHeader,
		CDataTable,
		CIcon,
		CModal,
		CProgress,
		CSelect,
	},
	data: () => ({
		cilInfo,
		cilLink,
		cilLinkBroken,
		cilPencil,
		cilReload,
		cilTrash,
	}),
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
	 * @var {Array<IOption>} ifnameOptions Array of CoreUI interface select options
	 */
	private ifNameOptions: Array<IOption> = [];

	/**
	 * @var {boolean} interfacesLoaded Indicates whether interfaces have been loaded
	 */
	private interfacesLoaded = false;

	/**
	 * @constant {Array<IField>} tableFields Array of CoreUI data table columns
	 */
	private tableFields: Array<IField> = [
		{
			key: 'ssid',
			label: this.$t('network.wireless.table.ssid'),
		},
		{
			key: 'signal',
			label: this.$t('network.wireless.table.signal'),
			filter: false,
		},
		{
			key: 'security',
			label: this.$t('network.wireless.table.security'),
			filter: false,
			sorter: false,
		},
		{
			key: 'interfaceName',
			label: this.$t('network.connection.interface'),
			filter: false,
			sorter: false,
		},
		{
			key: 'actions',
			label: this.$t('table.actions.title'),
			filter: false,
			sorter: false,
		},
		{
			key: 'show_details',
			label: '',
			_style: 'width: 1%',
			sorter: false,
			filter: false,
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
			return 'danger';
		}
	}

	/**
	 * Retrieves Wi-Fi interfaces
	 */
	private getInterfaces(): void {
		this.$store.commit('spinner/SHOW');
		NetworkInterfaceService.list(InterfaceType.WIFI)
			.then((interfaces: Array<NetworkInterface>) => {
				interfaces.forEach((item: NetworkInterface) => {
					this.ifNameOptions.push({label: item.name, value: item.name});
				});
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
		return NetworkConnectionService.list(ConnectionType.WIFI)
			.then((response: AxiosResponse) => {
				const connections: Array<NetworkConnection> = response.data;
				const apArray: Array<IAccessPointArray> = [];
				for (const ap of accessPoints) {
					ap['showDetails'] = false;
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
			ifname = this.ifNameOptions[0].label.toString();
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
