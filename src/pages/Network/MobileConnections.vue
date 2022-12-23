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
		<h1>{{ $t('network.mobile.title') }}</h1>
		<GsmInterfaces />
		<CCard>
			<CCardHeader class='border-0 datatable-header'>
				{{ $t('network.connection.title') }}
				<CButton
					color='success'
					size='sm'
					to='/ip-network/mobile/add'
				>
					<CIcon :content='cilPlus' size='sm' />
					{{ $t('table.actions.add') }}
				</CButton>
			</CCardHeader>
			<CCardBody>
				<CDataTable
					:fields='fields'
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
					<template #interfaceName='{item}'>
						<td>
							{{ item.interfaceName }}
						</td>
					</template>
					<template #actions='{item}'>
						<td class='col-actions'>
							<CButton
								size='sm'
								:color='item.interfaceName === null ? "success" : "danger"'
								@click='item.interfaceName === null ? connect(item) : disconnect(item, false)'
							>
								<CIcon :content='item.interfaceName === null ? cilLink : cilLinkBroken' size='sm' />
								{{ $t(`network.table.${item.interfaceName === null ? '' : 'dis'}connect`) }}
							</CButton> <CButton
								size='sm'
								color='primary'
								:to='"/ip-network/mobile/edit/" + item.uuid'
							>
								<CIcon :content='cilPencil' size='sm' />
								{{ $t('table.actions.edit') }}
							</CButton> <CButton
								size='sm'
								color='danger'
								@click='item.interfaceName === null ? remove(item) : disconnect(item, true)'
							>
								<CIcon :content='cilTrash' size='sm' />
								{{ $t('table.actions.delete') }}
							</CButton>
						</td>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
		<CCard body-wrapper>
			<NetworkOperators />
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {
	CBadge,
	CButton,
	CCard,
	CCardBody,
	CCardHeader,
	CDataTable,
	CIcon,
	CProgress
} from '@coreui/vue/src';
import NetworkOperators from '@/components/Network/NetworkOperators.vue';

import {cilLink, cilLinkBroken, cilPencil, cilPlus, cilTrash} from '@coreui/icons';
import {extendedErrorToast} from '@/helpers/errorToast';
import NetworkConnectionService, {ConnectionType} from '@/services/NetworkConnectionService';

import {AxiosError} from 'axios';
import {IField} from '@/interfaces/Coreui';
import {IModem} from '@/interfaces/Network/Mobile';
import {NetworkConnection} from '@/interfaces/Network/Connection';
import GsmInterfaces from '@/components/Network/GsmInterfaces.vue';

@Component({
	components: {
		CBadge,
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CDataTable,
		CIcon,
		CProgress,
		GsmInterfaces,
		NetworkOperators,
	},
	data: () => ({
		cilLink,
		cilLinkBroken,
		cilPencil,
		cilPlus,
		cilTrash,
	}),
	metaInfo: {
		title: 'network.mobile.title',
	},
})

/**
 * Mobile connections page
 */
export default class MobileConnections extends Vue {

	/**
	 * @var {Array<NetworkConnections>} connections Existing mobile connections
	 */
	private connections: Array<NetworkConnection> = [];

	/**
	 * @var {Array<IModem>} modems Available modems
	 */
	private modems: Array<IModem> = [];

	/**
	 * @constant {Array<IField>} fields GSM connections table fields
	 */
	private fields: Array<IField> = [
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
	 * @property {boolean} loading Loading state
	 */
	private loading = true;

	/**
	 * Builds connections table
	 */
	mounted(): void {
		this.getConnections();
	}

	/**
	 * Retrieves GSM connections
	 */
	private getConnections(): void {
		this.loading = true;
		NetworkConnectionService.list(ConnectionType.GSM)
			.then((connections: NetworkConnection[]) => {
				for (const i in connections) {
					const idx = this.modems.findIndex((modem: IModem) => connections[i].interfaceName === modem.interface);
					if (idx !== -1) {
						connections[i]['signal'] = this.modems[idx].signal;
						connections[i]['rssi'] = this.modems[idx].rssi;
					}
				}
				this.connections = connections;
				this.loading = false;
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'network.connection.messages.connectionFetchFailed'));
	}

	/**
	 * Establishes a GSM connection
	 * @param {NetworkConnection} connection GSM connection
	 */
	private connect(connection: NetworkConnection): void {
		this.$store.commit('spinner/SHOW');
		NetworkConnectionService.connect(connection.uuid)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'network.connection.messages.connect.success',
						{connection: connection.name}
					).toString()
				);
				this.getConnections();
			})
			.catch((error: AxiosError) => extendedErrorToast(
				error,
				'network.connection.messages.connect.failed',
				{connection: connection.name}
			));
	}

	/**
	 * Terminates a GSM connection
	 * @param {NetworkConnection} connection GSM connection
	 * @param {boolean} remove Remove connection
	 */
	private disconnect(connection: NetworkConnection, remove: boolean): Promise<void> {
		this.$store.commit('spinner/SHOW');
		return NetworkConnectionService.disconnect(connection.uuid)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				if (remove) {
					this.remove(connection);
				} else {
					this.$toast.success(
						this.$t(
							'network.connection.messages.disconnect.success',
							{interface: connection.interfaceName, connection: connection.name}
						).toString()
					);
					this.getConnections();
				}
			})
			.catch((error: AxiosError) => extendedErrorToast(
				error,
				'network.connection.messages.disconnect.failed',
				{connection: connection.name}
			));
	}

	/**
	 * Removes a GSM connection
	 * @param {NetworkConnection} connection GSM connection
	 */
	private remove(connection: NetworkConnection): void {
		this.$store.commit('spinner/SHOW');
		NetworkConnectionService.remove(connection.uuid)
			.then(() => {
				this.$toast.success(
					this.$t(
						'network.connection.messages.removeSuccess',
						{connection: connection.name},
					).toString()
				);
				this.getConnections();
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'network.connection.messages.removeFailed');
			});
	}
}
</script>
