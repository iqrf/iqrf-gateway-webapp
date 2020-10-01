<template>
	<div>
		<CCard>
			<CCardHeader>
				<CButton
					color='success'
					size='sm'
					to='/config/tracer/add'
					class='float-right'
				>
					<CIcon :content='$options.icons.add' />
					{{ $t('table.actions.add') }}
				</CButton>
			</CCardHeader>
			<CCardBody>
				<CDataTable
					:fields='fields'
					:items='instances'
					:column-filter='true'
					:items-per-page='20'
					:pagination='true'
					:striped='true'
					:sorter='{ external: false, resetable: true }'
				>
					<template #actions='{item}'>
						<td class='col-actions'>
							<CButton
								color='info'
								size='sm'
								:to='"/config/tracer/edit/" + item.instance'
							>
								<CIcon :content='$options.icons.edit' />
								{{ $t('table.actions.edit') }}
							</CButton>
							<CButton
								color='danger'
								size='sm'
								@click='modals.currentInstance = item.instance'
							>
								<CIcon :content='$options.icons.remove' />
								{{ $t('table.actions.delete') }}
							</CButton>
						</td>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
		<CModal
			color='danger'
			:show.sync='modals.currentInstance !== null'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('config.tracer.messages.removeTitle') }}
				</h5>
			</template>
			{{ $t('config.tracer.messages.removeItem', {instance: modals.currentInstance}) }}
			<template #footer>
				<CButton color='danger' @click='modals.currentInstance = null'>
					{{ $t('forms.no') }}
				</CButton>
				<CButton color='success' @click='removeInstance'>
					{{ $t('forms.yes') }}
				</CButton>
			</template>
		</CModal>
	</div>
</template>

<script>
import {CButton, CCard, CCardBody, CCardHeader, CDataTable, CIcon, CModal} from '@coreui/vue/src';
import {cilPencil, cilPlus, cilTrash} from '@coreui/icons';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import FormErrorHandler from '../../helpers/FormErrorHandler';

export default {
	name: 'TracerList',
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CDataTable,
		CIcon,
		CModal,
	},
	data() {
		return {
			componentName: 'shape::TraceFileService',
			fields: [
				{key: 'instance', label: this.$t('config.tracer.form.instance')},
				{key: 'path', label: this.$t('config.tracer.form.path')},
				{key: 'filename', label: this.$t('config.tracer.form.filename')},
				{
					key: 'actions',
					label: this.$t('table.actions.title'),
					filter: false,
					sorter: false,
				},
			],
			instances: [],
			modals: {
				currentInstance: null,
			},
			addModal: false,
			editModal: false,
		};
	},
	created() {
		this.getConfig();
	},
	methods: {
		getConfig() {
			this.$store.commit('spinner/SHOW');
			return DaemonConfigurationService.getComponent(this.componentName)
				.then((response) => {
					this.$store.commit('spinner/HIDE');
					this.instances = response.data.instances;
				})
				.catch((error) => FormErrorHandler.configError(error));
		},
		removeInstance() {
			this.$store.commit('spinner/SHOW');
			const instance = this.modals.currentInstance;
			this.modals.currentInstance = null;
			DaemonConfigurationService.deleteInstance(this.componentName, instance)
				.then(() => {
					this.getConfig().then(() => {
						this.$toast.success(
							this.$t('config.tracer.messages.removeSuccess', {instance: instance})
								.toString()
						);
					});
				})
				.catch((error) => {
					FormErrorHandler.configError(error);
				});
		},
	},
	icons: {
		add: cilPlus,
		edit: cilPencil,
		remove: cilTrash,
	},
	metaInfo: {
		title: 'config.tracer.title',
	},
};
</script>
