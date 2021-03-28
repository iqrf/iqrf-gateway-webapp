<template>
	<div>
		<h1>{{ $t('network.wireless.title') }}</h1>
		<CCard>
			<div v-if='interfacesLoaded && ifNameOptions.length === 0'>
				<CCardBody>
					{{ $t('network.wireless.messages.noInterfaces') }}
				</CCardBody>
			</div>
			<div v-if='interfacesLoaded && ifNameOptions.length > 0'>
				<CCardHeader class='border-0'>
					{{ $t('network.wireless.table.accessPoints') }}
					<CButton
						style='float: right;'
						color='primary'
						size='sm'
						@click='getAccessPoints'
					>
						<CIcon :content='icons.refresh' size='sm' />
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
							<td>
								<CBadge 
									v-if='item.aps[0].inUse'
									color='success'
								>
									{{ $t('network.connection.states.connected') }}
								</CBadge>
								{{ item.ssid }}
								<CIcon
									v-c-tooltip='{
										content: "BSSID: " + item.aps[0].bssid + " Channel: " + item.aps[0].channel + " Rate: " + item.aps[0].rate,
										placement: "left"
									}'
									style='float: right;'
									:content='icons.details'
									size='xl'
								/>
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
									@click='item.aps[0].inUse ? disconnect(item.aps[0].uuid, item.aps[0].ssid, item.aps[0].interfaceName):
										item.aps[0].uuid !== undefined ? connect(item.aps[0].uuid, item.aps[0].ssid, item.aps[0].interfaceName):
										addConnection(item.aps[0])'
								>
									<CIcon :content='item.aps[0].inUse ? icons.disconnect : icons.connect' size='sm' />
									{{ $t('network.table.' + (item.aps[0].inUse ? 'disconnect' : 'connect')) }}
								</CButton> <CButton
									v-if='item.aps[0].uuid'
									size='sm'
									color='primary'
									:to='"/network/wireless/edit/" + item.aps[0].uuid'
								>
									<CIcon :content='icons.edit' size='sm' />
									{{ $t('table.actions.edit') }}
								</CButton> <CButton
									v-if='item.aps[0].uuid'
									size='sm'
									color='danger'
									@click='removeConnection(item.aps[0].uuid, item.aps[0].ssid)'
								>
									<CIcon :content='icons.remove' size='sm' />
									{{ $t('table.actions.delete') }}
								</CButton>
							</td>
						</template>
					</CDataTable>
				</CCardBody>
			</div>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CBadge, CCard, CCardBody, CCardHeader, CDataTable, CIcon, CProgress, CSelect} from '@coreui/vue/src';
import WifiForm from '../../components/Network/WifiForm.vue';

import {cilInfo, cilPencil, cilLink, cilLinkBroken, cilReload, cilTrash} from '@coreui/icons';
import {extendedErrorToast} from '../../helpers/errorToast';
import NetworkConnectionService, {ConnectionType} from '../../services/NetworkConnectionService';
import NetworkInterfaceService, {InterfaceType} from '../../services/NetworkInterfaceService';

import {AxiosError, AxiosResponse} from 'axios';
import {Dictionary} from 'vue-router/types/router';
import {IField, IOption} from '../../interfaces/coreui';
import {IAccessPoint, IAccessPointArray, NetworkInterface} from '../../interfaces/network';

@Component({
	components: {
		CBadge,
		CCard,
		CCardBody,
		CCardHeader,
		CDataTable,
		CIcon,
		CProgress,
		CSelect,
		WifiForm,
	},
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
	private accessPoints: Array<IAccessPointArray> = []

	/**
	 * @var {Array<IOption>} ifnameOptions Array of CoreUI interface select options
	 */
	private ifNameOptions: Array<IOption> = []

	/**
	 * @var {boolean} interfacesLoaded Indicates whether interfaces have been loaded
	 */
	private interfacesLoaded = false

	/**
	 * @constant {Dictionary<Array<string>>} icons Dictionary of CoreUI icons
	 */
	private icons: Dictionary<Array<string>> = {
		details: cilInfo,
		connect: cilLink,
		disconnect: cilLinkBroken,
		edit: cilPencil,
		refresh: cilReload,
		remove: cilTrash,
	}

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
	]

	/**
	 * Retrieves data for table
	 */
	mounted(): void {
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
	 * Retrieves wifi interfaces
	 */
	private getInterfaces(): void {
		this.$store.commit('spinner/SHOW');
		NetworkInterfaceService.list(InterfaceType.WIFI)
			.then((response: AxiosResponse) => {
				let interfaces: Array<IOption> = [];
				response.data.forEach((item: NetworkInterface) => {
					interfaces.push({label: item.name, value: item.name});
				});
				this.ifNameOptions = interfaces;
				this.interfacesLoaded = true;
				this.$store.commit('spinner/HIDE');
				if (this.ifNameOptions.length > 0) {
					this.getAccessPoints();
				}
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'network.connection.messages.interfacesFetchFailed'));
	}

	/**
	 * Retrieves list of available access points
	 */
	private getAccessPoints(): void {
		this.$store.commit('spinner/SHOW');
		NetworkConnectionService.listWifiAccessPoints()
			.then((response: AxiosResponse) => this.findConnections(response.data))
			.catch((error: AxiosError) => extendedErrorToast(error, 'network.wireless.messages.listFailed'));
	}

	/**
	 * Retrieves list of existing wifi connections and adds UUID to matching access points
	 * @param {Array<IAccessPoint>} accessPoints Array of available access points
	 */
	private findConnections(accessPoints: Array<IAccessPoint>): Promise<void> {
		return NetworkConnectionService.list(ConnectionType.WIFI)
			.then((response: AxiosResponse) => {
				let apArray: Array<IAccessPointArray> = [];
				for (const ap of accessPoints) {
					let index = response.data.findIndex(con => con.name === ap.ssid);
					if (index !== -1) {
						ap.uuid = response.data[index].uuid;
						if (response.data[index].interfaceName !== null) {
							ap.interfaceName = response.data[index].interfaceName;
						}
					}
					index = apArray.findIndex(arrAp => arrAp.ssid === ap.ssid);
					if (index !== -1) {
						if (ap.inUse) {
							apArray[index].aps.unshift(ap);
						} else {
							apArray[index].aps.push(ap);
						}
					} else {
						apArray.push({
							ssid: ap.ssid,
							aps: [ap]
						});
					}
				}
				this.accessPoints = apArray;
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'network.wireless.messages.connectionsFailed'));
	}

	/**
	 * Connects to wifi access point
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
	 * Disconnects from wifi access point
	 * @param {string} uuid Network connection UUID
	 * @param {string} name Network connection name
	 * @param {string} ifname Network interface name
	 */
	private disconnect(uuid: string, name: string, ifname: string): void {
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
			.catch((error: AxiosError) => extendedErrorToast(
				error,
				'network.connection.messages.disconnect.failed',
				{connection: name}
			));
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
	 * Removes wifi access point connection
	 * @param {string} uuid Network connection UUID
	 * @param {string} name Network connection name
	 */
	private removeConnection(uuid: string, name: string): void {
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
