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
			{{ $t('config.daemon.messagings.mq.title') }}
		</h1>
		<CCard>
			<CCardHeader class='border-0'>
				<CButton
					color='success'
					to='/config/daemon/messagings/mq/add'
					size='sm'
					class='float-right'
				>
					<CIcon :content='cilPlus' size='sm' />
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
					:sorter='{external: false, resetable: true}'
				>
					<template #no-items-view='{}'>
						{{ $t('table.messages.noRecords') }}
					</template>
					<template #acceptAsyncMsg='{item}'>
						<td>
							<CDropdown
								:color='item.acceptAsyncMsg ? "success" : "danger"'
								:toggler-text='$t(`states.${item.acceptAsyncMsg ? "enabled": "disabled"}`)'
								placement='top-start'
								size='sm'
							>
								<CDropdownItem @click='changeAcceptAsyncMsg(item, true)'>
									{{ $t('states.enabled') }}
								</CDropdownItem>
								<CDropdownItem @click='changeAcceptAsyncMsg(item, false)'>
									{{ $t('states.disabled') }}
								</CDropdownItem>
							</CDropdown>
						</td>
					</template>
					<template #actions='{item}'>
						<td class='col-actions'>
							<CButton
								class='mr-1'
								color='info'
								:to='"/config/daemon/messagings/mq/edit/" + item.instance'
								size='sm'
							>
								<CIcon :content='cilPencil' size='sm' />
								{{ $t('table.actions.edit') }}
							</CButton>
							<MessagingDeleteModal
								:messaging-type='MessagingTypes.MQ'
								:instance='item.instance'
								@deleted='getInstances'
							/>
						</td>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
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
	CDropdown,
	CDropdownItem,
	CIcon,
	CModal
} from '@coreui/vue/src';
import MessagingDeleteModal from '@/components/Config/Messagings/MessagingDeleteModal.vue';

import {cilPencil, cilPlus} from '@coreui/icons';
import {extendedErrorToast} from '@/helpers/errorToast';
import {MessagingTypes} from '@/enums/Config/Messagings';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {IField} from '@/interfaces/coreui';
import {IMqInstance} from '@/interfaces/Config/Messaging';

@Component({
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
		MessagingDeleteModal,
	},
	data: () => ({
		cilPencil,
		cilPlus,
		MessagingTypes,
	}),
	metaInfo: {
		title: 'config.daemon.messagings.mq.title'
	}
})

/**
 * List of Daemon MQ messaging component instances
 */
export default class MqMessagingTable extends Vue {
	/**
	 * @constant {string} componentName MQ messaging component name
	 */
	private componentName = 'iqrf::MqMessaging';

	/**
	 * @constant {Array<IField>} fields Array of CoreUI data table columns
	 */
	private fields: Array<IField> = [
		{
			key: 'instance',
			label: this.$t('forms.fields.instanceName'),
		},
		{
			key: 'LocalMqName',
			label: this.$t('config.daemon.messagings.mq.form.LocalMqName'),
		},
		{
			key: 'RemoteMqName',
			label: this.$t('config.daemon.messagings.mq.form.RemoteMqName'),
		},
		{
			key: 'acceptAsyncMsg',
			label: this.$t('config.daemon.messagings.acceptAsyncMsg'),
			filter: false,
		},
		{
			key: 'actions',
			label: this.$t('table.actions.title'),
			sorter: false,
			filter: false,
		},
	];

	/**
	 * @var {Array<IMqInstance>} instances Array of MQ messaging component instances
	 */
	private instances: Array<IMqInstance> = [];

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		this.$store.commit('spinner/SHOW');
		this.getInstances();
	}

	/**
	 * Updates configuration of MQ messaging component instance
	 * @param {IMqInstance} instance MQ messaging instance
	 * @param {boolean} acceptAsyncMsg Message accepting policy setting
	 */
	private changeAcceptAsyncMsg(instance: IMqInstance, acceptAsyncMsg: boolean): void {
		if (instance.acceptAsyncMsg === acceptAsyncMsg) {
			return;
		}
		const settings = {
			...instance
		};
		settings.acceptAsyncMsg = acceptAsyncMsg;
		this.$store.commit('spinner/SHOW');
		DaemonConfigurationService.updateInstance(this.componentName, settings.instance, settings)
			.then(() => {
				this.getInstances().then(() => {
					this.$toast.success(
						this.$t('config.daemon.messagings.mq.messages.editSuccess', {instance: settings.instance})
							.toString()
					);
				});
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.messagings.mq.messages.editFailed', {instance: settings.instance}));
	}

	/**
	 * Retrieves instances of MQ messaging component
	 * @returns {Promise<void>} Empty promise for request chaining
	 */
	private getInstances(): Promise<void> {
		return DaemonConfigurationService.getComponent(this.componentName)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.instances = response.data.instances;
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.messagings.mq.messages.listFailed'));
	}
}
</script>
