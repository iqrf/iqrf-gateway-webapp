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
					<CIcon :content='$options.icons.connect' size='sm' />
					{{ $t('network.table.connect') }}
				</CButton>
				<CButton
					v-else
					color='danger'
					size='sm'
					@click='disconnect(item)'
				>
					<CIcon :content='$options.icons.disconnect' size='sm' />
					{{ $t('network.table.disconnect') }}
				</CButton>
				<CButton
					color='primary'
					:to='"/network/edit/" + item.uuid'
					size='sm'
				>
					<CIcon :content='$options.icons.edit' size='sm' />
					{{ $t('table.actions.edit') }}
				</CButton>
			</td>
		</template>
	</CDataTable>
</template>

<script>
import {CButton, CDataTable, CIcon} from '@coreui/vue/src';
import {cilLink, cilLinkBroken, cilPencil, cilPlus, cilTrash} from '@coreui/icons';
import NetworkConnectionService from '../../services/NetworkConnectionService';

export default {
	name: 'EthernetConnections',
	components: {
		CButton,
		CDataTable,
		CIcon,
	},
	props: {
		interfaceName: {
			type: [String, null],
			required: false,
			default: null,
		},
		connections: {
			type: [Array, null,],
			required: true,
		}
	},
	data() {
		return {
			fields: [
				{
					key: 'name',
					label: this.$t('network.connection.name'),
				},
				{
					key: 'actions',
					label: this.$t('table.actions.title'),
					sorter: false,
					filter: false,
				},
			],
		};
	},
	methods: {
		connect(connection) {
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
		},
		disconnect(connection) {
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
	},
	icons: {
		add: cilPlus,
		connect: cilLink,
		delete: cilTrash,
		disconnect: cilLinkBroken,
		edit: cilPencil,
	},
};
</script>
