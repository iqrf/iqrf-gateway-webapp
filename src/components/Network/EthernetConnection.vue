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
	<CDataTable
		:items='connections'
		:fields='fields'
		:column-filter='true'
		:items-per-page='20'
		:pagination='true'
		:striped='true'
		:sorter='{ external: false, resetable: true }'
	>
		<template #no-items-view='{}'>
			{{ $t('table.messages.noRecords') }}
		</template>
		<template #actions='{item}'>
			<td class='col-actions'>
				<CButton
					v-if='item.interfaceName !== interfaceName'
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
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {CButton, CDataTable, CIcon} from '@coreui/vue/src';
import {cilLink, cilLinkBroken, cilPencil, cilPlus, cilTrash} from '@coreui/icons';
import NetworkConnectionService from '../../services/NetworkConnectionService';
import { Dictionary } from 'vue-router/types/router';
import { IField } from '../../interfaces/coreui';
import {NetworkConnection} from '../../interfaces/network';

@Component({
	components: {
		CButton,
		CDataTable,
		CIcon,
	}
})

/**
 * Ethernet connections card for Network Manager component
 */
export default class EthernetConnection extends Vue {
	/**
	 * @constant {Array<IField>} fields CoreUI data table columns
	 */
	private fields: Array<IField> = [
		{
			key: 'name',
			label: this.$t('network.connection.name'),
		},
		{
			key: 'actions',
			label: this.$t('table.actions.title'),
			sorter: false,
			filter: false,
		}
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
	 * @property {Array<NetworkConnection>} connections Array of existing network connection configurations
	 */
	@Prop({required: true}) connections!: Array<NetworkConnection>

	/**
	 * @property {string} interfaceName Name of network interface
	 */
	@Prop({required: false, default: null}) interfaceName!: string

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		this.fields[0].label = this.$t('network.connection.name', {interface: this.interfaceName});
	}

	/**
	 * Establishes a connection using the specified configuration
	 * @param {NetworkConnection} connection Network connection configuration
	 */
	private connect(connection: NetworkConnection): void {
		this.$store.commit('spinner/SHOW');
		NetworkConnectionService.connect(connection.uuid, this.interfaceName)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'network.connection.messages.connect.success',
						{interface: this.interfaceName, connection: connection.name}
					).toString());
				this.$emit('update-connection');
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
						{interface: this.interfaceName, connection: connection.name}
					).toString());
				this.$emit('update-connection');
			})
			.catch(() => this.$store.commit('spinner/HIDE'));
	}

}
</script>
