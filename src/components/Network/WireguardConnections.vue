<template>
	<div>
		<CCard>
			<CCardHeader class='border-0'>
				{{ $t('network.wireguard.interfaces.title') }}
				<CButton
					v-if='interfaces.length === 0'
					style='float: right;'
					color='success'
					size='sm'
					@click='modal.show = true'
				>
					<CIcon :content='icons.add' size='sm' />
					{{ $t('forms.add') }}
				</CButton>
			</CCardHeader>
			<CCardBody>
				<CDataTable
					:fields='tableFields'
					:items='interfaces'
					:column-filter='true'
					:items-per-page='20'
					:pagination='true'
					:sorter='{external: false, resetable: true}'
				>
					<template #no-items-view='{}'>
						{{ $t('network.wireguard.interfaces.table.noInterfaces') }}
					</template>
					<template #state='{item}'>
						<td>
							<CBadge
								:color='item.state === "connected" ? "success" : "danger"'
							>
								{{ $t('network.connection.states.' + (item.state === 'connected' ? 'connected' : 'notConnected')) }}
							</CBadge>
						</td>
					</template>
					<template #actions='{item}'>
						<td class='col-actions'>
							<CButton
								size='sm'
								color='danger'
								@click='removeInterface(item.name)'
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
			:show.sync='modal.show'
			color='success'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('network.wireguard.interfaces.modal.title') }}
				</h5>
			</template>
			<div class='form-group'>
				<CInput
					v-model='modal.name'
					:label='$t("network.wireguard.interfaces.modal.interfaceName")'
				/>
				<p v-if='modal.name === ""' style='color: red;'>
					{{ $t('network.wireguard.interfaces.modal.errors.interfaceName') }}
				</p>
			</div>
			<template #footer>
				<CButton
					color='secondary'
					@click='hideModal'
				>
					{{ $t('forms.cancel') }}
				</CButton> <CButton
					color='success'
					:disabled='modal.name == ""'
					@click='createInterface'
				>
					{{ $t('forms.add') }}
				</CButton>
			</template>
		</CModal>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CBadge, CButton, CCard, CCardBody, CCardHeader, CInput} from '@coreui/vue/src';
import {cilPlus, cilPencil, cilTrash} from '@coreui/icons';

import NetworkInterfaceService, {InterfaceType} from '../../services/NetworkInterfaceService';
import WireguardService from '../../services/WireguardService';

import {AxiosResponse} from 'axios';
import {IField} from '../../interfaces/coreui';
import {NetworkInterface} from '../../interfaces/network';
import {Dictionary} from 'vue-router/types/router';

@Component({
	components: {
		CBadge,
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CInput,
	},
})

/**
 * Wireguard connections component
 */
export default class WireguardConnections extends Vue {

	/**
	 * @constant {Dictionary<Array<string>>} icons Dictionary of CoreUI icons
	 */
	private icons: Dictionary<Array<string>> = {
		add: cilPlus,
		edit: cilPencil,
		remove: cilTrash,
	}

	/**
	 * @var {Dictionary<string|boolean>} modal Controls whether wireguard interface creation modal is shown
	 */
	private modal: Dictionary<string|boolean> = {
		name: '',
		show: false,
	}

	/**
	 * @var {Array<NetworkInterface>} interfaces Array of existing interfaces
	 */
	private interfaces: Array<NetworkInterface> = []

	/**
	 * @constant {Array<IField>} tableField Array of CoreUI data table fields
	 */
	private tableFields: Array<IField> = [
		{
			key: 'name',
			label: this.$t('network.wireguard.interfaces.table.name'),
		},
		{
			key: 'state',
			label: this.$t('network.wireguard.interfaces.table.state'),
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
	 * Retrieves existing wireguard interfaces and wireguard configuration
	 */
	mounted(): void {
		this.getInterfaces();
	}

	/**
	 * Retrieves existing wireguard interfaces
	 */
	private getInterfaces(): void {
		this.$store.commit('spinner/SHOW');
		NetworkInterfaceService.list(InterfaceType.WIREGUARD)
			.then((response: AxiosResponse) => {
				this.interfaces = (response.data as Array<NetworkInterface>);
				if (this.interfaces.length > 0) {
					//
				}
				this.$store.commit('spinner/HIDE');
			});
	}

	/**
	 * Hides wireguard interface modal and clears data structure
	 */
	private hideModal(): void {
		this.modal.name = '';
		this.modal.show = false;
	}

	/**
	 * Creates a new Wireguard interface
	 */
	private createInterface(): void {
		let name = (this.modal.name as string);
		this.hideModal();
		this.$store.commit('spinner/SHOW');
		WireguardService.createInterface(name)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.getInterfaces();
			});
	}

	/**
	 * Removes an existing Wireguard interface
	 * @param {string} name Wireguard interface name
	 */
	private removeInterface(name: string): void {
		this.$store.commit('spinner/SHOW');
		WireguardService.removeInterface(name)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.getInterfaces();
			});
	}

}
</script>
