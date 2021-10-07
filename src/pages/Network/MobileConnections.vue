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
		<CCard class='card-margin-bottom'>
			<!--<div v-if='interfacesLoaded && noInterfaces'>
				<CCardBody>
					{{ $t('network.mobile.messages.noInterfaces') }}
				</CCardBody>
			</div>-->
			<div>
				<CCardHeader class='border-0 datatable-header'>
					{{ $t('network.connection.title') }}
					<CButton
						color='success'
						size='sm'
					>
						<CIcon :content='icons.add' size='sm' />
						Add connection
					</CButton>
				</CCardHeader>
				<CCardBody>
					<CDataTable
						:fields='fields'
						:items='connections'
						:column-filter='true'
						:items-per-page='20'
						:pagination='true'
						:sorter='{external: false, resetable: true}'
					>
						<template #no-items-view='{}'>
							{{ $t('network.mobile.messages.noConnections') }}
						</template>
						<template #actions='{item}'>
							<td class='col-actions'>
								<CButton
									size='sm'
									:color='item.interfaceName === null ? "success" : "danger"'
									@click='item.interfaceName === null ? connect(item) : disconnect(item)'
								>
									<CIcon :content='item.interfaceName === null ? icons.connect : icons.disconnect' size='sm' />
									{{ $t('network.table.' + (item.interfaceName === null ? 'connect' : 'disconnect')) }}
								</CButton> <CButton
									size='sm'
									color='primary'
									:to='"/network/mobile/edit" + item.uuid'
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
import {CBadge, CCard, CCardBody, CCardHeader, CDataTable, CIcon} from '@coreui/vue/src';

import {cilLink, cilLinkBroken, cilPencil, cilPlus, cilTrash} from '@coreui/icons';
import {extendedErrorToast} from '../../helpers/errorToast';
import NetworkConnectionService, {ConnectionType} from '../../services/NetworkConnectionService';
import NetworkInterfaceService, {InterfaceType} from '../../services/NetworkInterfaceService';

import {AxiosError, AxiosResponse} from 'axios';
import {IField} from '../../interfaces/coreui';
import {NetworkConnection} from '../../interfaces/network';

@Component({
	components: {
		CBadge,
		CCard,
		CCardBody,
		CCardHeader,
		CDataTable,
		CIcon,
	},
	metaInfo: {
		title: 'network.mobile.title',
	},
})

/**
 * Mobile connections page
 */
export default class MobileConnections extends Vue {

	/**
	 * @var {boolean} interfacesLoaded Indicates that interfaces have been loaded
	 */
	private interfacesLoaded = false

	/**
	 * @var {boolean} noInterfaces Indicates that no GSM interfaces were found
	 */
	private noInterfaces = false;

	/**
	 * @var {Array<NetworkConnections>} connections Existing mobile connections
	 */
	private connections: Array<NetworkConnection> = []

	/**
	 * @constant {Record<string, Array<string>>} icons Table icons
	 */
	private icons: Record<string, Array<string>> = {
		add: cilPlus,
		edit: cilPencil,
		remove: cilTrash,
		connect: cilLink,
		disconnect: cilLinkBroken,
	}

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
	]

	/**
	 * Builds connections table
	 */
	mounted(): void {
		this.getInterfaces();
	}

	/**
	 * Retrieves GSM interfaces
	 */
	private getInterfaces(): void {
		this.noInterfaces = false;
		this.$store.commit('spinner/SHOW');
		NetworkInterfaceService.list(InterfaceType.GSM)
			.then((response: AxiosResponse) => {
				this.interfacesLoaded = true;
				this.$store.commit('spinner/HIDE');
				if (response.data.length > 0) {
					this.getConnections();
				} else {
					this.noInterfaces = true;
				}
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'network.connection.messages.interfacesFetchFailed'));
	}

	/**
	 * Retrieves GSM connections
	 */
	private getConnections(): void {
		this.$store.commit('spinner/SHOW');
		NetworkConnectionService.list(ConnectionType.GSM)
			.then((response: AxiosResponse) => {
				this.connections = response.data;
				this.$store.commit('spinner/HIDE');
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
					).toString()
				);
			})
			.catch((error: AxiosError) => extendedErrorToast(
				error,
				'network.connection.messages.disconnect.failed',
				{connection: connection.name}
			));
	}
}
</script>
