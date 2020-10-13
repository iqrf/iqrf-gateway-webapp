<template>
	<div>
		<h1>{{ $t('config.mq.title') }}</h1>
		<CCard>
			<CCardHeader>
				<CButton
					color='success'
					to='/config/mq/add'
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
					<template #no-items-view='{}'>
						No records have been found.
					</template>
					<template #acceptAsyncMsg='{item}'>
						<td>
							<CDropdown
								:color='item.acceptAsyncMsg ? "success" : "danger"'
								:toggler-text='$t("table.enabled." + item.acceptAsyncMsg)'
								placement='top-start'
								size='sm'
							>
								<CDropdownItem @click='changeAcceptAsyncMsg(item, true)'>
									{{ $t('table.enabled.true') }}
								</CDropdownItem>
								<CDropdownItem @click='changeAcceptAsyncMsg(item, false)'>
									{{ $t('table.enabled.false') }}
								</CDropdownItem>
							</CDropdown>
						</td>
					</template>
					<template #actions='{item}'>
						<td class='col-actions'>
							<CButton
								color='primary'
								:to='"/config/mq/edit/" + item.instance'
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
			:show='modals.delete.instance !== null'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('config.mq.messages.delete.confirmTitle') }}
				</h5>
				<CButtonClose class='text-white' @click='modals.delete.instance = null' />
			</template>
			<span v-if='modals.delete.instance !== null'>
				{{ $t('config.mq.messages.delete.confirm', {instance: modals.delete.instance}) }}
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
	CDropdown,
	CDropdownItem,
	CIcon,
	CModal
} from '@coreui/vue/src';
import {cilPencil, cilPlus, cilTrash} from '@coreui/icons';
import DaemonConfigurationService
	from '../../services/DaemonConfigurationService';

export default {
	name: 'MqMessagingTable',
	components: {
		CButton,
		CButtonClose,
		CCard,
		CCardBody,
		CCardHeader,
		CDataTable,
		CDropdown,
		CDropdownItem,
		CIcon,
		CModal,
	},
	data() {
		return {
			componentName: 'iqrf::MqMessaging',
			fields: [
				{
					key: 'instance',
					label: this.$t('config.mq.form.instance'),
				},
				{
					key: 'LocalMqName',
					label: this.$t('config.mq.form.LocalMqName'),
				},
				{
					key: 'RemoteMqName',
					label: this.$t('config.mq.form.RemoteMqName'),
				},
				{
					key: 'acceptAsyncMsg',
					label: this.$t('config.mq.form.acceptAsyncMsg'),
					filter: false,
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
							this.$t('config.mq.messages.edit.success', {instance: instance.instance})
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
							this.$t('config.mq.messages.delete.success', {instance: instance})
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
		title: 'config.mq.title',
	},
};
</script>
