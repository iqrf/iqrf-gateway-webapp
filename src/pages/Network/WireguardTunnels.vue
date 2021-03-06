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
			<CCardHeader class='border-0'>
				{{ $t('network.wireguard.tunnels.title') }}
				<CButton
					style='float: right;'
					color='success'
					size='sm'
					to='/network/vpn/add'
				>
					<CIcon :content='icons.add' size='sm' />
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
				>
					<template #no-items-view='{}'>
						{{ $t('network.wireguard.tunnels.table.noTunnels') }}
					</template>
					<template #state='{item}'>
						<td>
							<CBadge
								:color='item.active ? "success" : "danger"'
							>
								{{ $t('network.wireguard.tunnels.table.states.' + (item.active ? 'active' : 'inactive')) }}
							</CBadge>
						</td>
					</template>
					<template #actions='{item}'>
						<td class='col-actions'>
							<CButton
								size='sm'
								:color='item.active ? "danger" : "success"'
								@click='changeActiveState(item.id, item.name, (item.active ? false : true))'
							>
								<CIcon 
									:content='item.active ? icons.deactivate : icons.activate'
									size='sm'
								/>
								{{ $t('network.wireguard.tunnels.table.action.' + (item.active ? "deactivate" : "activate")) }}
							</CButton> <CButton
								size='sm'
								:color='item.enabled ? "danger" : "success"'
								@click='changeEnabledState(item.id, item.name, (item.enabled ? false : true))'
							>
								<CIcon
									:content='item.enabled ? icons.disable : icons.enable'
									size='sm'
								/>
								{{ $t('table.actions.' + (item.enabled ? "disable" : "enable")) }}
							</CButton> <CButton
								size='sm'
								color='primary'
								:to='"/network/vpn/edit/" + item.id'
							>
								<CIcon :content='icons.edit' size='sm' />
								{{ $t('table.actions.edit') }}
							</CButton> <CButton
								size='sm'
								color='danger'
								@click='tunnelToDelete = item'
							>
								<CIcon :content='icons.remove' size='sm' />
								{{ $t('table.actions.delete') }}
							</CButton>
						</td>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
		<CModal
			color='danger'
			:show='tunnelToDelete !== null'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('network.wireguard.tunnels.modal.title') }}
				</h5>
			</template>
			<span v-if='tunnelToDelete !== null'>
				{{ $t('network.wireguard.tunnels.modal.prompt', {tunnel: tunnelToDelete.name}) }}
			</span>
			<template #footer>
				<CButton
					color='danger'
					@click='removeTunnel(tunnelToDelete.id, tunnelToDelete.name)'
				>
					{{ $t('forms.delete') }}
				</CButton> <CButton
					color='secondary'
					@click='tunnelToDelete = null'
				>
					{{ $t('forms.cancel') }}
				</CButton>
			</template>
		</CModal>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CBadge, CButton, CCard, CCardBody, CCardHeader, CInput} from '@coreui/vue/src';

import {cilCheckCircle, cilLink, cilLinkBroken, cilPlus, cilPencil, cilTrash, cilXCircle} from '@coreui/icons';
import {extendedErrorToast} from '../../helpers/errorToast';
import WireguardService from '../../services/WireguardService';

import {AxiosError, AxiosResponse} from 'axios';
import {Dictionary} from 'vue-router/types/router';
import {IField} from '../../interfaces/coreui';
import {IWG} from '../../interfaces/network';

@Component({
	components: {
		CBadge,
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CInput,
	},
	metaInfo: {
		title: 'network.wireguard.title'
	}
})

/**
 * Wireguard connections component
 */
export default class WireguardTunnels extends Vue {

	/**
	 * @constant {Dictionary<Array<string>>} icons Dictionary of CoreUI icons
	 */
	private icons: Dictionary<Array<string>> = {
		add: cilPlus,
		edit: cilPencil,
		remove: cilTrash,
		activate: cilLink,
		deactivate: cilLinkBroken,
		enable: cilCheckCircle,
		disable: cilXCircle,
	}

	/**
	 * @var {Array<IWG>} tunnels Array of existing tunnels
	 */
	private tunnels: Array<IWG> = []

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
	]

	/**
	 * @var {IWG} tunnelToDelete Tunnel information used in delete modal window
	 */
	private tunnelToDelete: IWG|null = null

	/**
	 * Retrieves existing Wireguard tunnels
	 */
	mounted(): void {
		this.getTunnels();
	}

	/**
	 * Retrieves existing Wireguard tunnels and stores data into table
	 */
	private getTunnels(): Promise<void> {
		this.$store.commit('spinner/SHOW');
		return WireguardService.listTunnels()
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.tunnels = response.data;
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'network.wireguard.tunnels.messages.listFailed'));
	}

	/**
	 * Changes active state of Wireguard tunnel
	 * @param {number} id Wireguard tunnel ID
	 * @param {string} name Wireguard tunnel name
	 * @param {boolean} state Wireguard tunnel state
	 */
	private changeActiveState(id: number, name: string, state: boolean): void {
		this.$store.commit('spinner/SHOW');
		if (state) {
			WireguardService.activateTunnel(id)
				.then(() => this.handleActiveSuccess(name, state))
				.catch((error: AxiosError) => extendedErrorToast(
					error,
					'network.wireguard.tunnels.messages.activateFailed',
					{tunnel: name}
				));
		} else {
			WireguardService.deactivateTunnel(id)
				.then(() => this.handleActiveSuccess(name, state))
				.catch((error: AxiosError) => extendedErrorToast(
					error,
					'network.wireguard.tunnels.messages.deactivateFailed',
					{tunnel: name}
				));
		}
	}

	/**
	 * Handles tunnel activation success
	 * @param {string} name Wireguard tunnel name
	 * @param {boolean} state Wireguard tunnel state
	 */
	private handleActiveSuccess(name: string, state: boolean): void {
		this.getTunnels().then(() => this.$toast.success(
			this.$t(
				'network.wireguard.tunnels.messages.' + (state ? '' : 'de') + 'activateSuccess',
				{tunnel: name}
			).toString()
		));
	}

	/**
	 * Changes enabled state of Wireguard tunnel
	 * @param {number} id Wireguard tunnel ID
	 * @param {string} name Wireguard tunnel name
	 * @param {boolean} state Wireguard tunnel state
	 */
	private changeEnabledState(id: number, name: string, state: boolean): void {
		this.$store.commit('spinner/SHOW');
		if (state) {
			WireguardService.enableTunnel(id)
				.then(() => this.handleEnableSuccess(name, state))
				.catch((error: AxiosError) => extendedErrorToast(
					error,
					'network.wireguard.tunnels.messages.enableFailed',
					{tunnel: name}
				));
		} else {
			WireguardService.disableTunnel(id)
				.then(() => this.handleEnableSuccess(name, state))
				.catch((error: AxiosError) => extendedErrorToast(
					error,
					'network.wireguard.tunnels.messages.disableFailed',
					{tunnel: name}
				));
		}
	}

	/**
	 * Handles tunnel enable success
	 * @param {string} name Wireguard tunnel name
	 * @param {boolean} state Wireguard tunnel state
	 */
	private handleEnableSuccess(name: string, state: boolean): void {
		this.getTunnels().then(() => this.$toast.success(
			this.$t(
				'network.wireguard.tunnels.messages.' + (state ? 'enableSuccess' : 'disableSuccess'),
				{tunnel: name}
			).toString()
		));
	}

	/**
	 * Removes an existing Wireguard tunnel
	 * @param {number} id Wireguard tunnel id
	 */
	private removeTunnel(id: number, name: string): void {
		this.tunnelToDelete = null;
		this.$store.commit('spinner/SHOW');
		WireguardService.removeTunnel(id)
			.then(() => {
				this.getTunnels().then(() => this.$toast.success(
					this.$t(
						'network.wireguard.tunnels.messages.deleteSuccess',
						{tunnel: name}
					).toString()
				));
			})
			.catch((error: AxiosError) => extendedErrorToast(
				error,
				'network.wireguard.tunnels.messages.deleteFailed',
				{tunnel: name}
			));
	}
}
</script>
