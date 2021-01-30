<template>
	<div>
		<h1>{{ $t('network.wireless.title') }}</h1>
		<CCard>
			<CCardHeader>
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
								v-if='item.inUse'
								color='success'
							>
								{{ $t('network.connection.states.connected') }}
							</CBadge>
							{{ item.ssid }}
						</td>
					</template>
					<template #signal='{item}'>
						<td>
							<CProgress
								:value='item.signal'
								:color='signalColor(item.signal)'
							/>
						</td>
					</template>
					<template #actions='{item}'>
						<td class='col-actions'>
							<CButton
								size='sm'
								:color='item.inUse ? "danger" : "success"'
								@click='item.inUse ? disconnect(item.uuid, item.ssid, false) : connectAction(item)'
							>
								<CIcon :content='item.inUse ? icons.disconnect : icons.connect' size='sm' />
								{{ $t('network.table.' + (item.inUse ? 'disconnect' : 'connect')) }}
							</CButton> <CButton
								v-if='item.uuid'
								size='sm'
								color='primary'
								:to='"/network/wireless/edit/" + item.uuid'
							>
								<CIcon :content='icons.edit' size='sm' />
								{{ $t('table.actions.edit') }}
							</CButton> <CButton
								v-if='item.uuid'
								size='sm'
								color='danger'
								@click='removeConnection(item.uuid, item.ssid)'
							>
								<CIcon :content='icons.remove' size='sm' />
								{{ $t('table.actions.delete') }}
							</CButton>
						</td>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
		<WifiForm
			v-if='modalAccessPoint !== null'
			:ap='modalAccessPoint'
			:ifname='ifname'
			@hide-modal='hideModal'
			@connection-created='connectAction'
		/>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CBadge, CCard, CCardBody, CCardHeader, CDataTable, CIcon, CProgress} from '@coreui/vue/src';
import {cilPencil, cilLink, cilLinkBroken, cilReload, cilTrash} from '@coreui/icons';
import WifiForm from '../../components/Network/WifiForm.vue';

import NetworkConnectionService, {ConnectionType} from '../../services/NetworkConnectionService';
import NetworkInterfaceService, {InterfaceType} from '../../services/NetworkInterfaceService';

import {AxiosResponse} from 'axios';
import {Dictionary} from 'vue-router/types/router';
import {IField} from '../../interfaces/coreui';
import {IAccessPoint} from '../../interfaces/network';

@Component({
	components: {
		CBadge,
		CCard,
		CCardBody,
		CCardHeader,
		CDataTable,
		CIcon,
		CProgress,
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
	 * @var {Array<IAccessPoint>} accessPoints Array of available access points
	 */
	private accessPoints: Array<IAccessPoint> = []

	/**
	 * @var {string} ifname Interface name
	 */
	private ifname = ''

	/**
	 * @constant {InterfaceType} iftype Interface type
	 */
	private iftype = InterfaceType.WIFI

	/**
	 * @var {IAccessPoint} modalAccessPoint Access point for modal window
	 */
	private modalAccessPoint: IAccessPoint|null = null

	/**
	 * @constant {Dictionary<Array<string>>} icons Dictionary of CoreUI icons
	 */
	private icons: Dictionary<Array<string>> = {
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
	 * Show wifi access point connection modal window
	 * @param {IAccessPoint} accesPoint Access point
	 */
	private showModal(accessPoint: IAccessPoint): void {
		this.modalAccessPoint = accessPoint;
	}

	/**
	 * Hides wifi access point connection modal window
	 */
	private hideModal(): void {
		this.modalAccessPoint = null;
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
				this.ifname = response.data[0].name;
				this.getAccessPoints();
			})
			.catch(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t('network.wireless.messages.interfacesFailed').toString()
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
			.catch(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t('network.wireless.messages.listFailed').toString()
				);
			});
	}

	/**
	 * Retrieves list of existing wifi connections and adds UUID to matching access points
	 * @param {Array<IAccessPoint>} accessPoints Array of available access points
	 */
	private findConnections(accessPoints: Array<IAccessPoint>): Promise<void> {
		return NetworkConnectionService.list(ConnectionType.WIFI)
			.then((response: AxiosResponse) => {
				for (const connection of response.data) {
					const index = accessPoints.findIndex(ap => ap.ssid === connection.name);
					if (index !== - 1) {
						accessPoints[index].uuid = connection.uuid;
					}
				}
				this.accessPoints = accessPoints;
				this.$store.commit('spinner/HIDE');
			})
			.catch(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t('network.wireless.messages.connectionsFailed').toString()
				);
			});
	}

	/**
	 * Performs a connect button action depending on the state of connection
	 * @param {IAccessPoint} accessPoint Access point
	 */
	private connectAction(accessPoint: IAccessPoint): void {
		const activeAP = this.accessPoints.find((ap: IAccessPoint) => {
			return ap.inUse === true && ap.uuid;
		});
		if (accessPoint.uuid) { // connection for AP exists
			if (activeAP) {
				this.disconnect(activeAP.uuid!, activeAP.ssid, true).then(() => 
					this.connect(accessPoint.uuid!, accessPoint.ssid)
				);
			} else {
				this.connect(accessPoint.uuid, accessPoint.ssid);
			}
		} else { // connection for AP does not exist
			this.showModal(accessPoint);			
		}
	}

	/**
	 * Connects to wifi access point
	 * @param {string} uuid Network connection UUID
	 * @param {string} name Network connection name
	 */
	private connect(uuid: string, name: string): void {
		this.$store.commit('spinner/SHOW');
		NetworkConnectionService.connect(uuid, this.ifname)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'network.connection.messages.connect.success',
						{interface: this.ifname, connection: name}
					).toString());
				this.getAccessPoints();
			})
			.catch(() => this.$store.commit('spinner/HIDE'));
	}

	/**
	 * Disconnects from wifi access point
	 * @param {string} uuid Network connection UUID
	 * @param {string} name Network connection name
	 * @param {boolean} inChain Disconnect request in connect chain
	 * @returns {Promise<void>} Promise for request chaining
	 */
	private disconnect(uuid: string, name: string, inChain: boolean): Promise<void> {
		this.$store.commit('spinner/SHOW');
		return NetworkConnectionService.disconnect(uuid)
			.then(() => {
				if (!inChain) {
					this.$store.commit('spinner/HIDE');
					this.$toast.success(
						this.$t(
							'network.connection.messages.disconnect.success',
							{interface: this.ifname, connection: name}
						).toString());
					this.getAccessPoints();
				}
			})
			.catch(() => this.$store.commit('spinner/HIDE'));
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
			.catch(() => this.$store.commit('spinner/HIDE'));
	}
}
</script>
