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
		<h1>{{ $t('network.wireless.title') }}</h1>
		<NetworkInterfaces ref='interfaces' :type='InterfaceType.WIFI' />
		<CCard>
			<CCardHeader class='datatable-header'>
				{{ $t('network.wireless.table.accessPoints') }}
				<CButton
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
					:loading='loading'
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
								<CBadge
									v-if='item.ssid.length === 0'
									color='secondary'
								>
									{{ $t('network.wireless.table.hidden') }}
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
							<SignalIndicator :signal='item.aps[0].signal' />
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
								@click='item.aps[0].inUse ? hostname !== "localhost" ? disconnectAp = item.aps[0] : disconnect(item.aps[0]):
									item.aps[0].uuid !== undefined ? connect(item.aps[0]):
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
								@click='hostname === "localhost" ? removeConnection(item.aps[0]) : deleteAp = item.aps[0]'
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
								@click='item.showDetails = !item.showDetails'
							>
								<CIcon :content='cilInfo' />
							</CButton>
						</td>
					</template>
					<template #details='{item}'>
						<CCollapse :show='item.showDetails'>
							<CCardBody>
								<div class='datatable-expansion-table'>
									<table>
										<caption>
											<strong>{{ $t('network.wireless.table.details') }}</strong>
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
							</CCardBody>
						</CCollapse>
					</template>
				</CDataTable>
			</CCardBody>
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
					@click='disconnect(disconnectAp)'
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
					@click='removeConnection(deleteAp)'
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
import {AxiosError} from 'axios';
import {Component, Ref, Vue} from 'vue-property-decorator';
import {
	CBadge,
	CButton,
	CCard,
	CCardBody,
	CCardHeader,
	CCollapse,
	CDataTable,
	CIcon,
	CModal,
	CSelect
} from '@coreui/vue/src';
import {cilInfo, cilLink, cilLinkBroken, cilPencil, cilReload, cilTrash} from '@coreui/icons';

import NetworkInterfaces from '@/components/Network/NetworkInterfaces.vue';
import SignalIndicator from '@/components/Network/SignalIndicator.vue';

import {ConnectionType} from '@/enums/Network/ConnectionType';

import {extendedErrorToast} from '@/helpers/errorToast';

import {IField} from '@/interfaces/Coreui';
import {NetworkConnection} from '@/interfaces/Network/Connection';
import {IAccessPoint, IAccessPoints} from '@/interfaces/Network/Wifi';

import NetworkConnectionService from '@/services/NetworkConnectionService';
import VersionService from '@/services/VersionService';
import {InterfaceType} from '@/enums/Network/InterfaceType';

@Component({
	components: {
		CBadge,
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CCollapse,
		CDataTable,
		CIcon,
		CModal,
		CSelect,
		NetworkInterfaces,
		SignalIndicator,
	},
	data: () => ({
		cilInfo,
		cilLink,
		cilLinkBroken,
		cilPencil,
		cilReload,
		cilTrash,
		InterfaceType,
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
	 * @property {NetworkInterfaces} interfaces Network interfaces component
	 */
	@Ref('interfaces') interfaces!: NetworkInterfaces;

	/**
	 * @var {Array<IAccessPointArray>} accessPoints Array of available access points
	 */
	private accessPoints: Array<IAccessPoints> = [];

	/**
	 * @property {boolean} loading Loading state
	 */
	private loading = true;

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
		NetworkConnectionService.connect(ap.uuid)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'network.connection.messages.connect.success',
						{interface: ap.interfaceName, connection: ap.ssid}
					).toString());
				this.getAccessPoints();
				this.interfaces.getData();
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
				this.interfaces.getData();
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
				this.interfaces.getData();
			})
			.catch((error: AxiosError) => extendedErrorToast(
				error,
				'network.connection.messages.removeFailed',
				{connection: ap.ssid}
			));
	}
}
</script>
