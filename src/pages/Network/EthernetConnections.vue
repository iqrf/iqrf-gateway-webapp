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
		<h1>{{ $t('network.ethernet.title') }}</h1>
		<NetworkInterfaces :type='InterfaceType.ETHERNET' />
		<CCard>
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
					:loading='loading'
					:sorter='{external: false, resetable: true}'
				>
					<template #no-items-view='{}'>
						{{ $t('network.connection.messages.noConnections') }}
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
								<CIcon :content='cilLink' size='sm' />
								{{ $t('network.table.connect') }}
							</CButton>
							<CButton
								v-else
								color='danger'
								size='sm'
								@click='hostname === "localhost" ? disconnect(item) : connectionModal = item'
							>
								<CIcon :content='cilLinkBroken' size='sm' />
								{{ $t('network.table.disconnect') }}
							</CButton> <CButton
								color='primary'
								:to='"/ip-network/ethernet/edit/" + item.uuid'
								size='sm'
							>
								<CIcon :content='cilPencil' size='sm' />
								{{ $t('table.actions.edit') }}
							</CButton>
						</td>
					</template>
				</CDataTable>
			</CCardBody>
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
import {CBadge, CButton, CCard, CCardBody, CCardHeader, CDataTable, CIcon, CModal} from '@coreui/vue/src';

import {cilLink, cilLinkBroken, cilPencil} from '@coreui/icons';
import {extendedErrorToast} from '@/helpers/errorToast';
import NetworkConnectionService, {ConnectionType} from '@/services/NetworkConnectionService';
import {InterfaceType} from '@/services/NetworkInterfaceService';
import VersionService from '@/services/VersionService';

import {AxiosError} from 'axios';
import {IField} from '@/interfaces/Coreui';
import {NetworkConnection} from '@/interfaces/Network/Connection';
import NetworkInterfaces from '@/components/Network/NetworkInterfaces.vue';

@Component({
	components: {
		CBadge,
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CDataTable,
		CIcon,
		CModal,
		NetworkInterfaces,
	},
	data: () => ({
		cilLink,
		cilLinkBroken,
		cilPencil,
		InterfaceType,
	}),
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
	 * @var {NetworkConnection|null} connectionModal Auxiliary connection variable for disconnect modal
	 */
	private connectionModal: NetworkConnection|null = null;

	/**
	 * @property {boolean} loading Loading state
	 */
	private loading = true;

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
	 * @var {string} hostname Window hostname
	 */
	private hostname = '';

	/**
	 * Retrieves interfaces and connections
	 */
	mounted(): void {
		this.hostname = window.location.hostname;
		this.getConnections();
	}

	/**
	 * Retrieves ethernet connections
	 */
	private getConnections(): void {
		this.loading = true;
		NetworkConnectionService.list(ConnectionType.ETHERNET)
			.then((connections: NetworkConnection[]) => {
				this.connections = connections;
				this.loading = false;
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
