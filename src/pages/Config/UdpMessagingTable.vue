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
	<div>
		<h1>
			{{ $t('config.daemon.messagings.udp.title') }}
		</h1>
		<CCard>
			<CCardHeader class='border-0'>
				<CButton
					v-if='instances.length < 1'
					color='success'
					to='/config/daemon/messagings/udp/add'
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
								:to='"/config/daemon/messagings/udp/edit/" + item.instance'
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
					{{ $t('config.daemon.messagings.udp.modal.title') }}
				</h5>
				<CButtonClose
					class='text-white'
					@click='deleteInstance = ""'
				/>
			</template>
			<span v-if='deleteInstance !== ""'>
				{{ $t('config.daemon.messagings.udp.modal.prompt', {instance: deleteInstance}) }}
			</span>
			<template #footer>
				<CButton
					color='danger'
					@click='performDelete'
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
import {Options, Vue} from 'vue-property-decorator';
import {CButton, CButtonClose, CCard, CCardBody, CCardHeader, CDataTable, CIcon, CModal} from '@coreui/vue/src';
import {cilPencil, cilPlus, cilTrash} from '@coreui/icons';

import {extendedErrorToast} from '../../helpers/errorToast';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {IField} from '../../interfaces/coreui';
import {IUdpInstance} from '../../interfaces/messagingInterfaces';

@Options({
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
	metaInfo: {
		title: 'config.daemon.messagings.udp.title'
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
			label: this.$t('forms.fields.instanceName'),
		},
		{
			key: 'RemotePort',
			label: this.$t('config.daemon.messagings.udp.form.RemotePort'),
		},
		{
			key: 'LocalPort',
			label: this.$t('config.daemon.messagings.udp.form.LocalPort'),
		},
		{
			key: 'actions',
			label: this.$t('table.actions.title'),
			sorter: false,
			filter: false,
		},
	]

	/**
	 * @constant {Record<string, Array<string>>} icons Dictionary of CoreUI Icons
	 */
	private icons: Record<string, Array<string>> = {
		add: cilPlus,
		delete: cilTrash,
		edit: cilPencil,
	}

	/**
	 * @var {Array<IUdpInstance>} instances Array of UDP messaging component instances
	 */
	private instances: Array<IUdpInstance> = []

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
	 * @param {IUdpInstance} instance UDP messaging instance
	 */
	private confirmDelete(instance: IUdpInstance): void {
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
			.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.messagings.udp.messages.listFailed'));
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
						this.$t('config.daemon.messagings.udp.messages.deleteSuccess', {instance: instance})
							.toString()
					);
				});
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.messagings.udp.messages.deleteFailed', {instance: instance}));
	}
}
</script>

<style scoped>

.card-header {
	padding-bottom: 0;
}

</style>
