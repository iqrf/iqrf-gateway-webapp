<!--
Copyright 2017-2022 IQRF Tech s.r.o.
Copyright 2019-2022 MICRORISC s.r.o.

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
		<h1>{{ $t('network.ethernet.title') }}</h1>
		<CCard>
			<div v-if='interfacesLoaded && ifNameOptions.length === 0'>
				<CCardBody>
					{{ $t('network.ethernet.messages.noInterfaces') }}
				</CCardBody>
			</div>
			<div v-else>
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
								{{ item.interfaceName }}
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
									@click='hostname === "localhost" ? disconnect(item) : connectionModal = item'
								>
									<CIcon :content='icons.disconnect' size='sm' />
									{{ $t('network.table.disconnect') }}
								</CButton> <CButton
									color='primary'
									:to='"/ip-network/ethernet/edit/" + item.uuid'
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
		<CModal
			:show='connectionModal !== null'
			color='warning'
			size='lg'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('network.ethernet.modal.title') }}
				</h5>
			</template>
			{{ $t('network.ethernet.modal.prompt') }}
			<template #footer>
				<CButton
					color='warning'
					@click='disconnect(connectionModal)'
				>
					{{ $t('network.table.disconnect') }}
				</CButton> <CButton
					color='secondary'
					@click='connectionModal = null'
				>
					{{ $t('forms.cancel') }}
				</CButton>
			</template>
		</CModal>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CBadge, CCard, CCardBody, CCardHeader, CModal} from '@coreui/vue/src';
import EthernetConnection from '@/components/Network/EthernetConnection.vue';

import {cilLink, cilLinkBroken, cilPencil, cilPlus, cilTrash} from '@coreui/icons';
import {extendedErrorToast} from '@/helpers/errorToast';
import NetworkConnectionService, {ConnectionType} from '@/services/NetworkConnectionService';
import NetworkInterfaceService, {InterfaceState, InterfaceType} from '@/services/NetworkInterfaceService';
import VersionService from '@/services/VersionService';

import {AxiosError, AxiosResponse} from 'axios';
import {IField, IOption} from '@/interfaces/coreui';
import {NetworkConnection, NetworkInterface} from '@/interfaces/network';

@Component({
	components: {
		CBadge,
		CCard,
		CCardBody,
		CCardHeader,
		CModal,
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
	private connections: Array<NetworkConnection> = [];

	/**
	 * @var {boolean} connectionsLoaded Indicates whether connections have been loaded
	 */
	private connectionsLoaded = false;

	/**
	 * @var {NetworkConnection|null} connectionModal Auxiliary connection variable for disconnect modal
	 */
	private connectionModal: NetworkConnection|null = null;

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
	];

	/**
	 * @constant {Record<string, Array<string>>} icons Array fo CoreUI icons
	 */
	private icons: Record<string, Array<string>> = {
		add: cilPlus,
		connect: cilLink,
		delete: cilTrash,
		disconnect: cilLinkBroken,
		edit: cilPencil,
	};

	/**
	 * @var {string} hostname Window hostname
	 */
	private hostname = '';

	/**
	 * Retrieves interfaces and connections
	 */
	mounted(): void {
		this.hostname = window.location.hostname;
		this.getInterfaces();
	}

	/**
	 * Retrieves ethernet interfaces
	 */
	private getInterfaces(): void {
		this.$store.commit('spinner/SHOW');
		NetworkInterfaceService.list(InterfaceType.ETHERNET)
			.then((response: AxiosResponse) => {
				const interfaces: Array<IOption> = [];
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
			.catch((error: AxiosError) => extendedErrorToast(error, 'network.connection.messages.interfacesFetchFailed'));
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
			.catch((error: AxiosError) => extendedErrorToast(error, 'network.connection.messages.connectionFetchFailed'));
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
						{connection: connection.name}
					).toString());
				this.getConnections();
			})
			.catch((error: AxiosError) => extendedErrorToast(
				error,
				'network.connection.messages.connect.failed',
				{connection: connection.name}
			));
	}

	/**
	 * Terminates a connection
	 * @param {NetworkConnection} connection Network connection configuration
	 */
	private disconnect(connection: NetworkConnection): void {
		this.connectionModal = null;
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
			.catch((error: AxiosError) => {
				if (this.hostname === 'localhost') {
					extendedErrorToast(
						error,
						'network.connection.messages.disconnect.failed',
						{connection: connection.name}
					);
				} else {
					this.tryRest(error, connection.name);
				}
			});
	}

	/**
	 * Attempts a REST API request to check availability of this address
	 * @param {AxiosError} disconnectError Axios error from disconnect request
	 * @param {string} name Connection name
	 */
	private tryRest(disconnectError: AxiosError, name: string): void {
		VersionService.getWebappVersionRest()
			.then(() => {
				extendedErrorToast(
					disconnectError,
					'network.connection.messages.disconnect.failed',
					{connection: name}
				);
			})
			.catch((error: AxiosError) => {
				if (error.response === undefined) {
					this.$store.commit('spinner/HIDE');
					this.$store.commit('blocking/SHOW', this.$t('network.ethernet.messages.disconnectUnavailable').toString());
				} else {
					extendedErrorToast(
						disconnectError,
						'network.connection.messages.disconnect.failed',
						{connection: name}
					);
				}
			});

	}
}
</script>
