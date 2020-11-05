<template>
	<div>
		<CCard>
			<CCardHeader>
				<h3 class='float-left'>
					{{ $t('config.udp.title') }}
				</h3>
				<CButton
					v-if='instances.length < 1'
					color='success'
					to='/config/daemon/udp/add'
					size='sm'
					class='float-right'
				>
					<CIcon :content='icons.add' size='sm' />
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
						{{ $t('table.messages.noRecords') }}
					</template>
					<template #actions='{item}'>
						<td class='col-actions'>
							<CButton
								color='info'
								:to='"/config/daemon/udp/edit/" + item.instance'
								size='sm'
							>
								<CIcon :content='icons.edit' size='sm' />
								{{ $t('table.actions.edit') }}
							</CButton> <CButton
								color='danger'
								size='sm'
								@click='confirmDelete(item)'
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
					{{ $t('config.udp.messages.delete.confirmTitle') }}
				</h5>
				<CButtonClose
					class='text-white'
					@click='deleteInstance = ""'
				/>
			</template>
			<span v-if='deleteInstance !== ""'>
				{{ $t('config.udp.messages.delete.confirm', {instance: deleteInstance}) }}
			</span>
			<template #footer>
				<CButton 
					color='danger' 
					@click='deleteInstance = ""'
				>
					{{ $t('forms.no') }}
				</CButton> <CButton
					color='success'
					@click='performDelete'
				>
					{{ $t('forms.yes') }}
				</CButton>
			</template>
		</CModal>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
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
import { IField } from '../../interfaces/coreui';
import { Dictionary } from 'vue-router/types/router';
import { AxiosError, AxiosResponse } from 'axios';
import { UdpInstance } from '../../interfaces/messagingInterfaces';
import FormErrorHandler from '../../helpers/FormErrorHandler';

@Component({
	components: {
		CButton,
		CButtonClose,
		CCard,
		CCardBody,
		CCardHeader,
		CDataTable,
		CIcon,
		CModal,
	}
})

/**
 * List of Daemon UDP messaging component instances
 */
export default class UdpMessagingTable extends Vue {
	/**
	 * @constant {string} componentName UDP messaging component name
	 */
	private componentName = 'iqrf::UdpMessaging'

	/**
	 * @var {string} deleteInstance UDP messaging instance name used in remove modal
	 */
	private deleteInstance = ''
	
	/**
	 * @constant {Array<IField>} fields Array of CoreUI data table columns
	 */
	private fields: Array<IField> = [
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
	 * @var {Array<UdpInstance>} instances Array of UDP messaging component instances
	 */
	private instances: Array<UdpInstance> = []

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		this.$store.commit('spinner/SHOW');
	}

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		this.getInstances();
	}

	/**
	 * Assigns name of UDP messaging instances selected to remove to the remove modal
	 * @param {UdpInstance} instance UDP messaging instance
	 */
	private confirmDelete(instance: UdpInstance): void {
		this.deleteInstance = instance.instance;
	}

	/**
	 * Retrieves instances of UDP messaging component
	 * @returns {Promise<void>} Empty promise for response chaining
	 */
	private getInstances(): Promise<void> {
		return DaemonConfigurationService.getComponent(this.componentName)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.instances = response.data.instances;
			})
			.catch((error: AxiosError) => FormErrorHandler.configError(error));
	}

	/**
	 * Removes instance of UDP messaging component
	 */
	private performDelete(): void {
		this.$store.commit('spinner/SHOW');
		const instance = this.deleteInstance;
		this.deleteInstance = '';
		DaemonConfigurationService.deleteInstance(this.componentName, instance)
			.then(() => {
				this.getInstances().then(() => {
					this.$toast.success(
						this.$t('config.udp.messages.delete.success', {instance: instance})
							.toString()
					);
				});
			})
			.catch((error: AxiosError) => FormErrorHandler.configError(error));
	}
}
</script>
