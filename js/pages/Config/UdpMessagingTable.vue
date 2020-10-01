<template>
	<div>
		<CCard>
			<CCardHeader v-if='instances.length < 1'>
				<CButton
					color='success'
					to='/config/udp/add'
					size='sm'
					class='float-right'
				>
					<CIcon :content='$options.icons.add' />
					{{ $t('table.actions.add') }}
				</CButton>
			</CCardHeader>
			<CCardBody>
				<CDataTable
					:items='instances'
					:fields='fields'
					:column-filter='true'
					:items-per-page='20'
					:pagination='true'
					:striped='true'
					:sorter='{ external: false, resetable: true }'
				>
					<template #actions='{item}'>
						<td class='col-actions'>
							<CButton
								color='primary'
								:to='"/config/udp/edit/" + item.instance'
								size='sm'
							>
								<CIcon :content='$options.icons.edit' />
								{{ $t('table.actions.edit') }}
							</CButton>
							<CButton color='danger' size='sm' @click='confirmDelete(item)'>
								<CIcon :content='$options.icons.delete' />
								{{ $t('table.actions.delete') }}
							</CButton>
						</td>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
		<CModal
			color='danger'
			:show.sync='modals.delete.instance !== null'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('config.udp.messages.delete.confirmTitle') }}
				</h5>
				<CButtonClose
					class='text-white'
					@click='modals.delete.instance = null'
				/>
			</template>
			<span v-if='modals.delete.instance !== null'>
				{{ $t('config.udp.messages.delete.confirm', {instance: modals.delete.instance}) }}
			</span>
			<template #footer>
				<CButton color='danger' @click='modals.delete.instance = null'>
					{{ $t('forms.no') }}
				</CButton>
				<CButton color='success' @click='performDelete'>
					{{ $t('forms.yes') }}
				</CButton>
			</template>
		</CModal>
	</div>
</template>

<script>
import {
	CButton,
	CButtonClose,
	CCard,
	CCardBody,
	CCardHeader,
	CDataTable,
	CIcon,
	CModal
} from '@coreui/vue/src';
import {cilPencil, cilPlus, cilTrash} from '@coreui/icons';
import DaemonConfigurationService
	from '../../services/DaemonConfigurationService';

export default {
	name: 'UdpMessagingTable',
	components: {
		CButton,
		CButtonClose,
		CCard,
		CCardBody,
		CCardHeader,
		CDataTable,
		CIcon,
		CModal,
	},
	data() {
		return {
			componentName: 'iqrf::UdpMessaging',
			fields: [
				{
					key: 'instance',
					label: this.$t('config.udp.form.instance'),
				},
				{
					key: 'RemotePort',
					label: this.$t('config.udp.form.RemotePort'),
				},
				{
					key: 'LocalPort',
					label: this.$t('config.udp.form.LocalPort'),
				},
				{
					key: 'actions',
					label: this.$t('table.actions.title'),
					sorter: false,
					filter: false,
				},
			],
			instances: [],
			modals: {
				delete: {
					instance: null,
				}
			},
		};
	},
	created() {
		this.$store.commit('spinner/SHOW');
		this.getInstances();
	},
	methods: {
		confirmDelete(instance) {
			this.modals.delete.instance = instance.instance;
		},
		changeAcceptAsyncMsg(instance, acceptAsyncMsg) {
			this.$store.commit('spinner/SHOW');
			instance.acceptAsyncMsg = acceptAsyncMsg;
			return DaemonConfigurationService.updateInstance(this.componentName, instance.instance, instance)
				.then(() => {
					this.getInstances().then(() => {
						this.$toast.success(
							this.$t('config.udp.messages.edit.success', {instance: instance.instance})
								.toString()
						);
					});
				});
		},
		getInstances() {
			return DaemonConfigurationService.getComponent(this.componentName)
				.then((response) => {
					this.$store.commit('spinner/HIDE');
					this.instances = response.data.instances;
				})
				.catch(() => this.$store.commit('spinner/HIDE'));
		},
		performDelete() {
			this.$store.commit('spinner/SHOW');
			const instance = this.modals.delete.instance;
			this.modals.delete.instance = null;
			DaemonConfigurationService.deleteInstance(this.componentName, instance)
				.then(() => {
					this.getInstances().then(() => {
						this.$toast.success(
							this.$t('config.udp.messages.delete.success', {instance: instance})
								.toString()
						);
					});
				})
				.catch(() => this.$store.commit('spinner/HIDE'));
		},
	},
	icons: {
		add: cilPlus,
		delete: cilTrash,
		edit: cilPencil,
	},
	metaInfo: {
		title: 'config.udp.title',
	},
};
</script>
