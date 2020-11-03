<template>
	<div>
		<CCard class='border-0'>
			<CCardHeader>
				<CButton
					color='success'
					size='sm'
					to='/config/daemon/tracer/add'
					class='float-right'
				>
					<CIcon :content='icons.add' size='sm' />
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
					<template #no-items-view='{}'>
						{{ $t('table.messages.noRecords') }}
					</template>
					<template #actions='{item}'>
						<td class='col-actions'>
							<CButton
								color='info'
								size='sm'
								:to='"/config/daemon/tracer/edit/" + item.instance'
							>
								<CIcon :content='icons.edit' size='sm' />
								{{ $t('table.actions.edit') }}
							</CButton> <CButton
								color='danger'
								size='sm'
								@click='deleteInstance = item.instance'
							>
								<CIcon :content='icons.delete' size='sm' />
								{{ $t('table.actions.delete') }}
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
					{{ $t('config.tracer.messages.removeTitle') }}
				</h5>
			</template>
			{{ $t('config.tracer.messages.removeItem', {instance: deleteInstance}) }}
			<template #footer>
				<CButton
					color='danger'
					@click='deleteInstance = ""'
				>
					{{ $t('forms.no') }}
				</CButton> <CButton
					color='success'
					@click='removeInstance'
				>
					{{ $t('forms.yes') }}
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
import FormErrorHandler from '../../helpers/FormErrorHandler';
import {IField} from '../../interfaces/coreui';
import { AxiosError, AxiosResponse } from 'axios';
import { Dictionary } from 'vue-router/types/router';

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
			label: this.$t('config.tracer.form.instance')
		},
		{
			key: 'path',
			label: this.$t('config.tracer.form.path')
		},
		{
			key: 'filename',
			label: this.$t('config.tracer.form.filename')
		},
		{
			key: 'actions',
			label: this.$t('table.actions.title'),
			filter: false,
			sorter: false,
		},
	]

	/**
	 * @constant {Dictionary<Array<string>>} icons Array of CoreUI icons
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
		this.$store.commit('spinner/SHOW');
		return DaemonConfigurationService.getComponent(this.componentName)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.instances = response.data.instances;
			})
			.catch((error: AxiosError) => FormErrorHandler.configError(error));
	}
	
	/**
	 * Removes instance of logging service component
	 */
	private removeInstance(): void {
		this.$store.commit('spinner/SHOW');
		const instance = this.deleteInstance;
		this.deleteInstance = '';
		DaemonConfigurationService.deleteInstance(this.componentName, instance)
			.then(() => {
				this.getConfig().then(() => {
					this.$toast.success(
						this.$t('config.tracer.messages.removeSuccess', {instance: instance})
							.toString()
					);
				});
			})
			.catch((error: AxiosError) => FormErrorHandler.configError(error));
	}
}
</script>
