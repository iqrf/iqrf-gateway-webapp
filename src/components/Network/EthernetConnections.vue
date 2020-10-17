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
					:to='"/network/edit/" + item.uuid'
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

export default class EthernetConnections extends Vue {
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
	private icons: Dictionary<Array<string>> = {
		add: cilPlus,
		connect: cilLink,
		delete: cilTrash,
		disconnect: cilLinkBroken,
		edit: cilPencil,
	}

	@Prop({required: true}) connections!: Array<NetworkConnection>
	@Prop({required: false, default: null}) interfaceName!: string

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
