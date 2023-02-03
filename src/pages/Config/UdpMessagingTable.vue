<!--
Copyright 2017-2023 IQRF Tech s.r.o.
Copyright 2019-2023 MICRORISC s.r.o.

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
					color='success'
					to='/config/daemon/messagings/udp/add'
					size='sm'
					class='float-right'
				>
					<CIcon :content='cilPlus' size='sm' />
					{{ $t('table.actions.add') }}
				</CButton>
			</CCardHeader>
			<CCardBody>
				<CDataTable
					:loading='loading'
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
								class='mr-1'
								color='info'
								:to='"/config/daemon/messagings/udp/edit/" + item.instance'
								size='sm'
							>
								<CIcon :content='cilPencil' size='sm' />
								{{ $t('table.actions.edit') }}
							</CButton>
							<CButton
								color='danger'
								size='sm'
								@click='removeInstance(item.instance)'
							>
								<CIcon :content='cilTrash' size='sm' />
								{{ $t('table.actions.delete') }}
							</CButton>
						</td>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
		<MessagingDeleteModal ref='deleteModal' @deleted='getInstances' />
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CButtonClose, CCard, CCardBody, CCardHeader, CDataTable, CIcon} from '@coreui/vue/src';
import MessagingDeleteModal from '@/components/Config/Messagings/MessagingDeleteModal.vue';

import {cilPencil, cilPlus, cilTrash} from '@coreui/icons';
import {extendedErrorToast} from '@/helpers/errorToast';
import {MessagingTypes} from '@/enums/Config/Messagings';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {IField} from '@/interfaces/Coreui';
import {IUdpInstance} from '@/interfaces/Config/Messaging';

@Component({
	components: {
		CButton,
		CButtonClose,
		CCard,
		CCardBody,
		CCardHeader,
		CDataTable,
		CIcon,
		MessagingDeleteModal,
	},
	data: () => ({
		cilPencil,
		cilPlus,
		cilTrash,
		MessagingTypes,
	}),
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
	private componentName = 'iqrf::UdpMessaging';

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
	];

	/**
	 * @var {boolean} loading Indicates that request is in progress
	 */
	private loading = false;

	/**
	 * @var {Array<IUdpInstance>} instances Array of UDP messaging component instances
	 */
	private instances: Array<IUdpInstance> = [];

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		this.getInstances();
	}

	/**
	 * Retrieves instances of UDP messaging component
	 * @returns {Promise<void>} Empty promise for response chaining
	 */
	private getInstances(): Promise<void> {
		this.loading = true;
		return DaemonConfigurationService.getComponent(this.componentName)
			.then((response: AxiosResponse) => {
				this.instances = response.data.instances;
				this.loading = false;
			})
			.catch((error: AxiosError) => {
				this.loading = false;
				extendedErrorToast(error, 'config.daemon.messagings.udp.messages.listFailed');
			});
	}

	/**
	 * Removes component instance
	 * @param {string} instance Component instance
	 */
	private removeInstance(instance: string): void {
		(this.$refs.deleteModal as MessagingDeleteModal).showModal(MessagingTypes.UDP, instance);
	}
}
</script>
