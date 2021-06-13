<template>
	<div>
		<CCard class='border-0 card-margin-bottom'>
			<CCardHeader class='border-0'>
				<CButton
					color='success'
					size='sm'
					class='float-right'
					to='/config/daemon/misc/tracer/add'
					:title='$t("table.actions.add")'
				>
					<CIcon :content='icons.add' size='sm' />
					<span class='d-none d-lg-inline'>
						{{ $t('table.actions.add') }}
					</span>
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
					<template #no-items-view='{}'>
						{{ $t('table.messages.noRecords') }}
					</template>
					<template #actions='{item}'>
						<td class='col-actions'>
							<CButton
								color='info'
								size='sm'
								:to='"/config/daemon/misc/tracer/edit/" + item.instance'
								:title='$t("table.actions.edit")'
							>
								<CIcon :content='icons.edit' size='sm' />
								<span class='d-none d-lg-inline'>
									{{ $t('table.actions.edit') }}
								</span>
							</CButton> <CButton
								color='danger'
								size='sm'
								:title='$t("table.actions.delete")'
								@click='deleteInstance = item.instance'
							>
								<CIcon :content='icons.delete' size='sm' />
								<span class='d-none d-lg-inline'>
									{{ $t('table.actions.delete') }}
								</span>
							</CButton>
						</td>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
		<CModal
			color='danger'
			:show='deleteInstance !== ""'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('config.daemon.misc.tracer.modal.title') }}
				</h5>
			</template>
			{{ $t('config.daemon.misc.tracer.modal.prompt', {instance: deleteInstance}) }}
			<template #footer>
				<CButton
					color='danger'
					@click='removeInstance'
				>
					{{ $t('forms.delete') }}
				</CButton> <CButton
					color='secondary'
					@click='deleteInstance = ""'
				>
					{{ $t('forms.cancel') }}
				</CButton>
			</template>
		</CModal>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CDataTable, CIcon, CModal} from '@coreui/vue/src';
import {cilPencil, cilPlus, cilTrash} from '@coreui/icons';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import {IField} from '../../interfaces/coreui';
import { AxiosError, AxiosResponse } from 'axios';
import { Dictionary } from 'vue-router/types/router';
import { extendedErrorToast } from '../../helpers/errorToast';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CDataTable,
		CIcon,
		CModal,
	}
})

/**
 * List of Daemon logging service component instances
 */
export default class TracerList extends Vue {
	/**
	 * @constant {string} componentName Logging service component name
	 */
	private componentName = 'shape::TraceFileService'

	/**
	 * @constant {Array<IField>} fields Array of CoreUI data table columns
	 */
	private fields: Array<IField> = [
		{
			key: 'instance',
			label: this.$t('forms.fields.instanceName')
		},
		{
			key: 'path',
			label: this.$t('config.daemon.misc.tracer.form.path')
		},
		{
			key: 'filename',
			label: this.$t('config.daemon.misc.tracer.form.filename')
		},
		{
			key: 'actions',
			label: this.$t('table.actions.title'),
			filter: false,
			sorter: false,
		},
	]

	/**
	 * @constant {Dictionary<Array<string>>} icons Dictionary of CoreUI Icons
	 */
	private icons: Dictionary<Array<string>> = {
		add: cilPlus,
		delete: cilTrash,
		edit: cilPencil,
	}

	/**
	 * @var {Array<unknown>} instances Array of logging service component instances
	 */
	private instances: Array<unknown> = []

	/**
	 * @var {string} deleteInstance Name of logging service component instance used in remove modal
	 */
	private deleteInstance = ''

	/**
	 * Vue lifecycle hook created
	 */
	mounted(): void {
		this.getConfig();
	}

	/**
	 * Retrieves configuration of logging service component
	 */
	private getConfig(): Promise<void> {
		return DaemonConfigurationService.getComponent(this.componentName)
			.then((response: AxiosResponse) => {
				this.instances = response.data.instances;
				this.$emit('fetched', {name: 'tracer', success: true});
			})
			.catch(() => {
				this.$emit('fetched', {name: 'tracer', success: false});
			});
	}
	
	/**
	 * Removes instance of logging service component
	 */
	private removeInstance(): void {
		const instance = this.deleteInstance;
		this.deleteInstance = '';
		this.$store.commit('spinner/SHOW');
		DaemonConfigurationService.deleteInstance(this.componentName, instance)
			.then(() => {
				this.getConfig().then(() => {
					this.$toast.success(
						this.$t('config.daemon.misc.tracer.messages.deleteSuccess', {instance: instance})
							.toString()
					);
				});
			})
			.catch((error: AxiosError) => extendedErrorToast(
				error,
				'config.daemon.misc.tracer.messages.deleteFailed',
				{instance: instance}
			));
	}
}
</script>

<style scoped>

.card-header {
	padding-bottom: 0;
}

</style>
