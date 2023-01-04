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
		<h1>{{ $t('network.wireguard.title') }}</h1>
		<CCard>
			<CCardHeader class='datatable-header'>
				{{ $t('network.wireguard.tunnels.title') }}
				<CButton
					color='success'
					size='sm'
					to='/ip-network/vpn/add'
				>
					<CIcon :content='cilPlus' size='sm' />
					{{ $t('forms.add') }}
				</CButton>
			</CCardHeader>
			<CCardBody>
				<CDataTable
					:fields='tableFields'
					:items='tunnels'
					:column-filter='true'
					:items-per-page='20'
					:pagination='true'
					:sorter='{external: false, resetable: true}'
					:loading='loading'
				>
					<template #no-items-view='{}'>
						{{ $t('network.wireguard.tunnels.table.noTunnels') }}
					</template>
					<template #state='{item}'>
						<td>
							<CBadge
								:color='item.active ? "success" : "danger"'
							>
								{{ $t(`network.wireguard.tunnels.table.states.${item.active ? '' : 'in'}active`) }}
							</CBadge>
						</td>
					</template>
					<template #actions='{item}'>
						<td class='col-actions'>
							<CButton
								size='sm'
								:color='item.active ? "danger" : "success"'
								@click='changeActiveState(item.id, item.name, !item.active)'
							>
								<CIcon
									:content='item.active ? cilLinkBroken : cilLink'
									size='sm'
								/>
								{{ $t(`network.wireguard.tunnels.table.action.${item.active ? 'deactivate' : 'activate'}`) }}
							</CButton> <CButton
								size='sm'
								:color='item.enabled ? "danger" : "success"'
								@click='changeEnabledState(item.id, item.name, !item.enabled)'
							>
								<CIcon
									:content='item.enabled ? cilXCircle : cilCheckCircle'
									size='sm'
								/>
								{{ $t(`table.actions.${item.enabled ? 'disable' : 'enable'}`) }}
							</CButton> <CButton
								size='sm'
								color='primary'
								:to='"/ip-network/vpn/edit/" + item.id'
							>
								<CIcon :content='cilPencil' size='sm' />
								{{ $t('table.actions.edit') }}
							</CButton> <CButton
								size='sm'
								color='danger'
								@click='tunnelToDelete = item'
							>
								<CIcon :content='cilTrash' size='sm' />
								{{ $t('table.actions.delete') }}
							</CButton>
						</td>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
		<WireGuardDeleteModal v-model='tunnelToDelete' @deleted='removeTunnel()' />
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {cilCheckCircle, cilLink, cilLinkBroken, cilPlus, cilPencil, cilTrash, cilXCircle} from '@coreui/icons';
import {CBadge, CButton, CCard, CCardBody, CCardHeader, CDataTable, CIcon, CInput} from '@coreui/vue/src';
import {AxiosError, AxiosResponse} from 'axios';

import WireGuardDeleteModal from '@/components/Network/WireGuardDeleteModal.vue';
import {extendedErrorToast} from '@/helpers/errorToast';
import {IField} from '@/interfaces/Coreui';
import {IWG} from '@/interfaces/Network/Wireguard';
import WireguardService from '@/services/WireguardService';

@Component({
	components: {
		CBadge,
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CDataTable,
		CIcon,
		CInput,
		WireGuardDeleteModal,
	},
	data: () => ({
		cilCheckCircle,
		cilLink,
		cilLinkBroken,
		cilPencil,
		cilPlus,
		cilTrash,
		cilXCircle,
	}),
	metaInfo: {
		title: 'network.wireguard.title'
	}
})

/**
 * WireGuard connections component
 */
export default class WireguardTunnels extends Vue {

	/**
	 * @property {boolean} loading - Is the tunnels loading?
	 */
	private loading = false;

	/**
	 * @var {Array<IWG>} tunnels Array of existing tunnels
	 */
	private tunnels: Array<IWG> = [];

	/**
	 * @constant {Array<IField>} tableField Array of CoreUI data table fields
	 */
	private tableFields: Array<IField> = [
		{
			key: 'name',
			label: this.$t('network.wireguard.tunnels.table.name'),
		},
		{
			key: 'state',
			label: this.$t('network.wireguard.tunnels.table.state'),
			sorter: false,
			filter: false,
		},
		{
			key: 'actions',
			label: this.$t('table.actions.title'),
			filter: false,
			sorter: false,
		},
	];

	/**
	 * @var {IWG} tunnelToDelete Tunnel information used in delete modal window
	 */
	private tunnelToDelete: IWG|null = null;

	/**
	 * Retrieves existing WireGuard tunnels
	 */
	mounted(): void {
		this.getTunnels();
	}

	/**
	 * Retrieves existing WireGuard tunnels and stores data into table
	 */
	private getTunnels(): Promise<void> {
		this.loading = true;
		return WireguardService.listTunnels()
			.then((response: AxiosResponse) => {
				this.tunnels = response.data;
				this.loading = false;
			})
			.catch((error: AxiosError) => {
				this.loading = false;
				extendedErrorToast(error, 'network.wireguard.tunnels.messages.listFailed');
			});
	}

	/**
	 * Changes active state of WireGuard tunnel
	 * @param {number} id WireGuard tunnel ID
	 * @param {string} name WireGuard tunnel name
	 * @param {boolean} state WireGuard tunnel state
	 */
	private changeActiveState(id: number, name: string, state: boolean): void {
		this.loading = true;
		if (state) {
			WireguardService.activateTunnel(id)
				.then(() => this.handleActiveSuccess(name, state))
				.catch((error: AxiosError) => {
					this.loading = false;
					extendedErrorToast(
						error,
						'network.wireguard.tunnels.messages.activateFailed',
						{tunnel: name}
					);
				});
		} else {
			WireguardService.deactivateTunnel(id)
				.then(() => this.handleActiveSuccess(name, state))
				.catch((error: AxiosError) => {
					this.loading = false;
					extendedErrorToast(
						error,
						'network.wireguard.tunnels.messages.deactivateFailed',
						{tunnel: name}
					);
				});
		}
	}

	/**
	 * Handles tunnel activation success
	 * @param {string} name WireGuard tunnel name
	 * @param {boolean} state WireGuard tunnel state
	 */
	private handleActiveSuccess(name: string, state: boolean): void {
		this.getTunnels().then(() => this.$toast.success(
			this.$t(
				`network.wireguard.tunnels.messages.${state ? '' : 'de'}activateSuccess`,
				{tunnel: name}
			).toString()
		));
	}

	/**
	 * Changes enabled state of WireGuard tunnel
	 * @param {number} id WireGuard tunnel ID
	 * @param {string} name WireGuard tunnel name
	 * @param {boolean} state WireGuard tunnel state
	 */
	private changeEnabledState(id: number, name: string, state: boolean): void {
		this.loading = true;
		if (state) {
			WireguardService.enableTunnel(id)
				.then(() => this.handleEnableSuccess(name, state))
				.catch((error: AxiosError) => {
					this.loading = false;
					extendedErrorToast(
						error,
						'network.wireguard.tunnels.messages.enableFailed',
						{tunnel: name}
					);
				});
		} else {
			WireguardService.disableTunnel(id)
				.then(() => this.handleEnableSuccess(name, state))
				.catch((error: AxiosError) => {
					this.loading = false;
					extendedErrorToast(
						error,
						'network.wireguard.tunnels.messages.disableFailed',
						{tunnel: name}
					);
				});
		}
	}

	/**
	 * Handles tunnel enable success
	 * @param {string} name WireGuard tunnel name
	 * @param {boolean} state WireGuard tunnel state
	 */
	private handleEnableSuccess(name: string, state: boolean): void {
		this.getTunnels().then(() => this.$toast.success(
			this.$t(`network.wireguard.tunnels.messages.${state ? 'enable' : 'disable'}Success`,
				{tunnel: name}
			).toString()
		));
	}

	/**
	 * Removes an existing WireGuard tunnel
	 */
	private removeTunnel(): void {
		this.tunnelToDelete = null;
		this.getTunnels();
	}

}
</script>
