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
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CBadge, CButton, CCard, CCardBody, CCardHeader, CInput} from '@coreui/vue/src';
import {cilPlus, cilPencil, cilTrash} from '@coreui/icons';

import {AxiosResponse} from 'axios';
import {IField} from '../../interfaces/coreui';
import {IConnection} from '../../interfaces/network';
import {Dictionary} from 'vue-router/types/router';
import NetworkConnectionService, { ConnectionType } from '../../services/NetworkConnectionService';

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
	}

	/**
	 * @var {Array<IConnection>} tunnels Array of existing interfaces
	 */
	private tunnels: Array<IConnection> = []

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
	 * Retrieves existing wireguard interfaces and wireguard configuration
	 */
	mounted(): void {
		this.getConnections();
	}

	/**
	 * Retrieves existing wireguard interfaces
	 */
	private getConnections(): void {
		this.$store.commit('spinner/SHOW');
		NetworkConnectionService.list(ConnectionType.WIREGUARD)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
			});
	}

}
</script>
