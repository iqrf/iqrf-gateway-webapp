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
								:color='item.inUse ? "danger" : "success"'
								@click='item.uuid ? connect(item.uuid) : modalAccessPoint = item'
							>
								<CIcon :content='item.inUse ? icons.disconnect : icons.connect' />
								{{ $t('network.table.' + (item.inUse ? 'disconnect' : 'connect')) }}
							</CButton> <CButton
								v-if='item.uuid'
								color='primary'
								:to='"/network/edit/" + item.uuid'
							>
								<CIcon :content='icons.edit' size='sm' />
								{{ $t('table.actions.edit') }}
							</CButton>
						</td>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
		<CModal
			color='primary'
			:show='modalAccessPoint !== null'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('network.wireless.modal.title', {accessPoint: modalAccessPoint.name}) }}
				</h5>
			</template>
			<template #footer>
				<CButton
					color='secondary'
					@click='modalAccessPoint = null'
				>
					{{ $t('forms.cancel') }}
				</CButton> <CButton
					color='primary'
					@click='connect(modalAccessPoint.uuid)'
				>
					{{ $t('network.table.connect') }}
				</CButton>
			</template>
		</CModal>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CCard, CCardBody, CCardHeader, CDataTable, CIcon, CModal, CProgress} from '@coreui/vue/src';
import {cilPencil, cilLink, cilLinkBroken} from '@coreui/icons';

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
		CModal,
		CProgress,
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

	private connect(uuid: string): void {
		this.modalAccessPoint = null;
	}
}
</script>
