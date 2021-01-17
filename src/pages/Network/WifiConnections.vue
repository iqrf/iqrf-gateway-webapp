<template>
	<div>
		<h1>{{ $t('network.wireless.title') }}</h1>
		<CCard>
			<CCardHeader>
				{{ $t('network.wireless.table.accessPoints') }}
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
								@click='item.inUse ? disconnect(item.uuid) : item.uuid ? connect(item.uuid) : showModal(item)'
							>
								<CIcon :content='item.inUse ? icons.disconnect : icons.connect' size='sm' />
								{{ $t('network.table.' + (item.inUse ? 'disconnect' : 'connect')) }}
							</CButton> <CButton
								v-if='item.uuid'
								size='sm'
								color='primary'
								:to='"/network/edit/" + item.uuid'
							>
								<CIcon :content='icons.edit' size='sm' />
								{{ $t('table.actions.edit') }}
							</CButton> <CButton
								v-if='item.uuid'
								size='sm'
								color='danger'
								@click='removeConnection(item.uuid)'
							>
								<CIcon :content='icons.remove' size='sm' />
								{{ $t('table.actions.delete') }}
							</CButton>
						</td>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
		<WifiForm v-if='modalAccessPoint !== null' :ap='modalAccessPoint' @hide-modal='hideModal' />
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CCard, CCardBody, CCardHeader, CDataTable, CIcon, CProgress} from '@coreui/vue/src';
import {cilPencil, cilLink, cilLinkBroken, cilTrash} from '@coreui/icons';
import WifiForm from '../../components/Network/WifiForm.vue';

import NetworkConnectionService, {ConnectionType} from '../../services/NetworkConnectionService';

import {AxiosResponse} from 'axios';
import {Dictionary} from 'vue-router/types/router';
import {IField} from '../../interfaces/coreui';
import {IAccessPoint, NetworkConnection} from '../../interfaces/network';

@Component({
	components: {
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
	 * @var {Array<NetworkConnection>} connections Array of existing connections
	 */
	private connections: Array<NetworkConnection> = []

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
		this.getAccessPoints();
	}

	/**
	 * Show wifi access point connection modal window
	 */
	private showModal(item: IAccessPoint): void {
		this.modalAccessPoint = item;
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
	private findConnections(accessPoints: Array<IAccessPoint>): void {
		NetworkConnectionService.list(ConnectionType.WIFI)
			.then((response: AxiosResponse) => {
				for (const connection of response.data) {
					const index = accessPoints.findIndex(ap => ap.ssid === connection.name);
					if (index !== - 1) {
						accessPoints[index].uuid = connection.uuid;
					}
				}
				this.$store.commit('spinner/HIDE');
				this.connections = response.data;
				this.accessPoints = accessPoints;
			})
			.catch(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t('network.wireless.messages.connectionsFailed').toString()
				);
			});
	}

	/**
	 * Connects to wifi access point
	 * @param {string} uuid Network connection UUID
	 */
	private connect(uuid: string): void {
		let connection = this.connections.find((item: NetworkConnection) => {
			return item.uuid === uuid;
		});
		if (!connection) {
			return;
		}
		this.$store.commit('spinner/SHOW');
		NetworkConnectionService.connect(connection.uuid, connection.interfaceName)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'network.connection.messages.connect.success',
						{interface: connection?.interfaceName, connection: connection?.interfaceName}
					).toString());
				this.getAccessPoints();
			})
			.catch(() => this.$store.commit('spinner/HIDE'));
	}

	/**
	 * Disconnects from wifi access point
	 * @param {string} uuid Network connection UUID
	 */
	private disconnect(uuid: string): void {
		let connection = this.connections.find((item: NetworkConnection) => {
			return item.uuid === uuid;
		});
		if (!connection) {
			return;
		}
		this.$store.commit('spinner/SHOW');
		NetworkConnectionService.disconnect(connection.uuid)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'network.connection.messages.disconnect.success',
						{interface: connection?.interfaceName, connection: connection?.name}
					).toString());
				this.getAccessPoints();
			})
			.catch(() => this.$store.commit('spinner/HIDE'));
	}

	/**
	 * Removes wifi access point connection
	 * @param {string} uuid Network connection UUID
	 */
	private removeConnection(uuid: string): void {
		let connection = this.connections.find((item: NetworkConnection) => {
			return item.uuid === uuid;
		});
		if (!connection) {
			return;
		}
		this.$store.commit('spinner/SHOW');
		NetworkConnectionService.remove(uuid)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('network.connection.messages.removeSuccess', {connection: connection?.name}).toString()
				);
				this.getAccessPoints();
			})
			.catch(() => this.$store.commit('spinner/HIDE'));
	}
}
</script>
