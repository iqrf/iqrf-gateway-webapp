<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

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
		<GsmInterfaces
			ref='interfaces'
			:connections='connections'
			@restart='loading = true'
			@refresh='getConnections'
		/>
		<CCard>
			<CCardHeader class='datatable-header'>
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
						{{ $t('network.mobile.messages.noConnections') }}
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
								@click='connectionToDelete = item'
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
		<CModal
			:show='connectionToDisconnect !== null'
			color='warning'
			size='lg'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('network.connection.modals.disconnectWithWatchdog.title') }}
				</h5>
			</template>
			<span v-if='connectionToDisconnect !== null'>
				{{ $t('network.connection.modals.disconnectWithWatchdog.body', {connection: connectionToDisconnect.name}) }}
			</span>
			<template #footer>
				<CButton
					color='warning'
					@click='disconnect(connectionToDisconnect, true)'
				>
					{{ $t('network.table.disconnect') }}
				</CButton> <CButton
					color='secondary'
					class='ml-auto'
					@click='connectionToDisconnect = null'
				>
					{{ $t('forms.cancel') }}
				</CButton>
			</template>
		</CModal>
		<CModal
			:show='connectionToDelete !== null'
			color='danger'
			size='lg'
		>
			<template #header>
				<h5 v-if='monit !== null' class='modal-title'>
					{{ $t('network.connection.modals.deleteWithWatchdog.title') }}
				</h5>
				<h5 v-else class='modal-title'>
					{{ $t('network.connection.modals.delete.title') }}
				</h5>
			</template>
			<span v-if='connectionToDelete !== null'>
				<span v-if='monit !== null'>
					{{ $t('network.connection.modals.deleteWithWatchdog.body', {connection: connectionToDelete.name}) }}
				</span>
				<span v-else>
					{{ $t('network.connection.modals.delete.body', {connection: connectionToDelete.name}) }}
				</span>
			</span>
			<template #footer>
				<CButton
					color='danger'
					@click='remove(connectionToDelete)'
				>
					{{ $t('table.actions.delete') }}
				</CButton> <CButton
					color='secondary'
					class='ml-auto'
					@click='connectionToDelete = null'
				>
					{{ $t('forms.cancel') }}
				</CButton>
			</template>
		</CModal>
	</div>
</template>

<script lang='ts'>
import {Component, Ref, Vue, Watch} from 'vue-property-decorator';
import {
	CBadge,
	CButton,
	CCard,
	CCardBody,
	CCardHeader,
	CDataTable,
	CIcon,
	CProgress,
	CModal,
} from '@coreui/vue/src';
import NetworkOperators from '@/components/Network/NetworkOperators.vue';

import {
	cilLink,
	cilLinkBroken,
	cilPencil,
	cilPlus,
	cilTrash
} from '@coreui/icons';
import {extendedErrorToast} from '@/helpers/errorToast';
import NetworkConnectionService from '@/services/NetworkConnectionService';

import {AxiosError, AxiosResponse} from 'axios';
import {IField} from '@/interfaces/Coreui';
import {IModem} from '@/interfaces/Network/Mobile';
import {NetworkConnection} from '@/interfaces/Network/Connection';
import GsmInterfaces from '@/components/Network/GsmInterfaces.vue';
import {ConnectionType} from '@/enums/Network/ConnectionType';
import MonitService from '@/services/MonitService';
import {MonitCheck} from '@/interfaces/Maintenance/Monit';

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
		CModal,
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
	 * @property {GsmInterfaces} interfaces - GSM interfaces component
	 */
	@Ref('interfaces') readonly interfaces!: GsmInterfaces;

	/**
	 * @var {Array<NetworkConnections>} connections Existing mobile connections
	 */
	private connections: Array<NetworkConnection> = [];

	/**
	 * @property {NetworkConnection|null} connectionToDisconnect Connection to disconnect
	 */
	private connectionToDisconnect: NetworkConnection|null = null;

	/**
	 * @property {NetworkConnection|null} connectionToDelete Connection to delete
	 */
	private connectionToDelete: NetworkConnection|null = null;

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
	 * @property {MonitCheck | null} monit Monit check
	 */
	private monit: MonitCheck | null = null;

	/**
	 * @property {string} monitCheckName Monit check name
	 */
	private monitCheckName = 'network_ppp0';

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

	@Watch('gateway.info')
	public async getMonitCheck(): Promise<void> {
		if (!this.$store.getters['features/isEnabled']('monit') || this.$store.getters['gateway/board'] !== 'MICRORISC s.r.o. IQD-GW04') {
			return;
		}
		await MonitService.getCheck(this.monitCheckName)
			.then((response: AxiosResponse<MonitCheck>) => {
				this.monit = response.data;
			})
			.catch(() => {
				this.monit = null;
			});
	}

	/**
	 * Establishes a GSM connection
	 * @param {NetworkConnection} connection GSM connection
	 */
	private connect(connection: NetworkConnection): void {
		this.$store.commit('spinner/SHOW');
		NetworkConnectionService.connect(connection.uuid, connection.interfaceName)
			.then(() => {
				this.getConnections();
				this.interfaces.getData();
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'network.connection.messages.connect.success',
						{connection: connection.name}
					).toString()
				);
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
	 * @param {boolean} confirmed Confirmed termination
	 */
	private async disconnect(connection: NetworkConnection, confirmed: boolean): Promise<void> {
		if (this.$store.getters['features/isEnabled']('monit') &&
        this.$store.getters['gateway/board'] === 'MICRORISC s.r.o. IQD-GW04' &&
        connection.interfaceName === 'ttyAMA2'
		) {
			if (confirmed) {
				await MonitService.disableCheck(this.monitCheckName);
			} else {
				this.connectionToDisconnect = connection;
				return Promise.resolve();
			}
		}
		this.connectionToDisconnect = null;
		this.$store.commit('spinner/SHOW');
		return NetworkConnectionService.disconnect(connection.uuid)
			.then(() => {
				this.getConnections();
				this.interfaces.getData();
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

	/**
	 * Removes a GSM connection
	 * @param {NetworkConnection} connection GSM connection
	 */
	private async remove(connection: NetworkConnection): Promise<void> {
		this.$store.commit('spinner/SHOW');
		if (this.$store.getters['features/isEnabled']('monit') &&
        this.$store.getters['gateway/board'] === 'MICRORISC s.r.o. IQD-GW04' &&
        connection.interfaceName === 'ttyAMA2'
		) {
			await MonitService.disableCheck(this.monitCheckName);
		}
		this.connectionToDelete = null;
		if (connection.interfaceName !== null) {
			const result = await NetworkConnectionService.disconnect(connection.uuid)
				.then(() => true)
				.catch(() => false);
			if (!result) {
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t(
						'network.connection.messages.removeFailed',
						{connection: connection.name},
					).toString()
				);
				return;
			}
		}
		NetworkConnectionService.remove(connection.uuid)
			.then(() => {
				this.getConnections();
				this.interfaces.getData();
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'network.connection.messages.removeSuccess',
						{connection: connection.name},
					).toString()
				);
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'network.connection.messages.removeFailed');
			});
	}

}
</script>
