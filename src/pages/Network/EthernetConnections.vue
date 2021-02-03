<template>
	<div>
		<h1>{{ $t('network.ethernet.title') }}</h1>
		<CCard>
			<div v-if='interfacesLoaded && ifNameOptions.length === 0'>
				<CCardBody>
					{{ $t('network.ethernet.messages.noInterfaces') }}
				</CCardBody>
			</div>
			<div v-if='interfacesLoaded && connectionsLoaded && ifNameOptions.length > 0'>
				<CCardHeader class='border-0'>
					{{ $t('network.connection.title') }}
				</CCardHeader>
				<CCardBody>
					<CDataTable
						:fields='tableFields'
						:items='connections'
						:column-filter='true'
						:items-per-page='20'
						:pagination='true'
						:sorter='{external: false, resetable: true}'
					>
						<template #no-items-view='{}'>
							{{ $t('network.wireless.table.noAccessPoints') }}
						</template>
						<template #name='{item}'>
							<td>
								<CBadge
									v-if='item.interfaceName !== null'
									color='success'
								>
									{{ $t('network.connection.states.connected') }}
								</CBadge>
								{{ item.name }}
							</td>
						</template>
						<template #interfaceName='{item}'>
							<td>
								<CSelect
									v-if='ifNameOptions.length > 1'
									:value.sync='item.interfaceName'
									:options='ifNameOptions'
								/>
								<span v-else>{{ item.interfaceName }}</span>
							</td>
						</template>
						<template #actions='{item}'>
							<td class='col-actions'>
								<CButton
									v-if='item.interfaceName === null'
									color='success'
									size='sm'
									@click='connect(item)'
								>
									<CIcon :content='icons.connect' size='sm' />
									{{ $t('network.table.connect') }}
								</CButton>
								<CButton
									v-else
									color='danger'
									size='sm'
									@click='disconnect(item)'
								>
									<CIcon :content='icons.disconnect' size='sm' />
									{{ $t('network.table.disconnect') }}
								</CButton> <CButton
									color='primary'
									:to='"/network/ethernet/edit/" + item.uuid'
									size='sm'
								>
									<CIcon :content='icons.edit' size='sm' />
									{{ $t('table.actions.edit') }}
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
import {CBadge, CCard, CCardBody, CCardHeader} from '@coreui/vue/src';
import {cilLink, cilLinkBroken, cilPencil, cilPlus, cilTrash} from '@coreui/icons';
import EthernetConnection from '../../components/Network/EthernetConnection.vue';

import NetworkConnectionService, {ConnectionType} from '../../services/NetworkConnectionService';
import NetworkInterfaceService, {InterfaceState, InterfaceType} from '../../services/NetworkInterfaceService';

import {AxiosResponse} from 'axios';
import {Dictionary} from 'vue-router/types/router';
import {IField, IOption} from '../../interfaces/coreui';
import {NetworkConnection, NetworkInterface} from '../../interfaces/network';

@Component({
	components: {
		CBadge,
		CCard,
		CCardBody,
		CCardHeader,
		EthernetConnection
	},
	metaInfo: {
		title: 'network.ethernet.title',
	},
})

export default class EthernetConnections extends Vue {

	/**
	 * @var {Array<NetworkConnection>} connections Array of existing network connections
	 */
	private connections: Array<NetworkConnection> = []

	/**
	 * @var {boolean} connectionsLoaded Indicates whether connections have been loaded
	 */
	private connectionsLoaded = false

	/**
	 * @var {Array<IOption>} ifnameOptions Array of CoreUI interface select options
	 */
	private ifNameOptions: Array<IOption> = []

	/**
	 * @var {boolean} interfacesLoaded Indicates whether interfaces have been loaded
	 */
	private interfacesLoaded = false

	/**
	 * @constant {Array<IField>} tableFields Array of CoreUI data table columns
	 */
	private tableFields: Array<IField> = [
		{
			key: 'name',
			label: this.$t('network.connection.name'),
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
	 * @constant {Dictionary<Array<string>>} icons Array fo CoreUI icons
	 */
	private icons: Dictionary<Array<string>> = {
		add: cilPlus,
		connect: cilLink,
		delete: cilTrash,
		disconnect: cilLinkBroken,
		edit: cilPencil,
	}

	/**
	 * Retrieves interfaces and connections
	 */
	mounted(): void {
		this.getInterfaces();
	}

	/**
	 * Retrieves ethernet interfaces
	 */
	private getInterfaces(): void {
		this.$store.commit('spinner/SHOW');
		NetworkInterfaceService.list(InterfaceType.ETHERNET)
			.then((response: AxiosResponse) => {
				let interfaces: Array<IOption> = [];
				response.data.forEach((item: NetworkInterface) => {
					if (item.state !== InterfaceState.UNAVAILABLE) {
						interfaces.push({label: item.name, value: item.name});
					}
				});
				this.ifNameOptions = interfaces;
				this.interfacesLoaded = true;
				this.$store.commit('spinner/HIDE');
				if (this.ifNameOptions.length > 0) {
					this.getConnections();
				}
			})
			.catch(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t('network.connection.messages.interfaceFetchFailed').toString()
				);
			});
	}

	/**
	 * Retrieves ethernet connections
	 */
	private getConnections(): void {
		this.$store.commit('spinner/SHOW');
		NetworkConnectionService.list(ConnectionType.ETHERNET)
			.then((response: AxiosResponse) => {
				this.connections = response.data;
				this.connectionsLoaded = true;
				this.$store.commit('spinner/HIDE');
			})
			.catch(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t('network.connection.messages.connectionFetchFailed').toString()
				);
			});
	}

	/**
	 * Establishes a connection using the specified configuration
	 * @param {NetworkConnection} connection Network connection configuration
	 */
	private connect(connection: NetworkConnection): void {
		this.$store.commit('spinner/SHOW');
		NetworkConnectionService.connect(connection.uuid, connection.interfaceName)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'network.connection.messages.connect.success',
						{interface: connection.interfaceName, connection: connection.name}
					).toString());
				this.getConnections();
			})
			.catch(() => this.$store.commit('spinner/HIDE'));
	}

	/**
	 * Terminates a connection
	 * @param {NetworkConnection} connection Network connection configuration
	 */
	private disconnect(connection: NetworkConnection): void {
		this.$store.commit('spinner/SHOW');
		NetworkConnectionService.disconnect(connection.uuid)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'network.connection.messages.disconnect.success',
						{interface: connection.interfaceName, connection: connection.name}
					).toString());
				this.getConnections();
			})
			.catch(() => this.$store.commit('spinner/HIDE'));
	}
}
</script>
