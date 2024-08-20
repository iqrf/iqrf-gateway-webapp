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
			{{ $t('config.daemon.messagings.mqtt.title') }}
		</h1>
		<CCard>
			<CCardHeader class='border-0'>
				<CButton
					color='success'
					to='/config/daemon/messagings/mqtt/add'
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
					:sorter='{external: false, resetable: true}'
				>
					<template #no-items-view='{}'>
						{{ $t('table.messages.noRecords') }}
					</template>
					<template #EnabledSSL='{item}'>
						<td>
							<CButton :color='hasTls(item) ? "success" : "danger"' size='sm'>
								{{ hasTls(item) ? $t('states.enabled') : $t('states.disabled') }}
							</CButton>
						</td>
					</template>
					<template #acceptAsyncMsg='{item}'>
						<td>
							<CDropdown
								:color='item.acceptAsyncMsg ? "success" : "danger"'
								:toggler-text='$t(`states.${item.acceptAsyncMsg ? "enabled" : "disabled"}`)'
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
								:to='"/config/daemon/messagings/mqtt/edit/" + item.instance'
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
import {
	CButton,
	CButtonClose,
	CCard,
	CCardBody,
	CCardHeader,
	CDataTable,
	CDropdown,
	CDropdownItem,
	CIcon
} from '@coreui/vue/src';
import MessagingDeleteModal from '@/components/Config/Messagings/MessagingDeleteModal.vue';

import {
	cilCheckCircle,
	cilPencil,
	cilPlus,
	cilTrash,
	cilXCircle
} from '@coreui/icons';
import {extendedErrorToast} from '@/helpers/errorToast';
import {MessagingTypes} from '@/enums/Config/Messagings';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {IField} from '@/interfaces/Coreui';
import {IMqttInstance} from '@/interfaces/Config/Messaging';

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
		MessagingDeleteModal,
	},
	data: () => ({
		cilCheckCircle,
		cilPencil,
		cilPlus,
		cilTrash,
		cilXCircle,
		MessagingTypes,
	}),
	metaInfo: {
		title: 'config.daemon.messagings.mqtt.title',
	},
})

/**
 * List of Daemon MQTT messaging component instances
 */
export default class MqttMessagingTable extends Vue {
	/**
	 * @constant {string} componentName MQTT messaging component name
	 */
	private componentName = 'iqrf::MqttMessaging';

	/**
	 * @constant {Array<IField>} fields Array of CoreUI data table columns
	 */
	private fields: Array<IField> = [
		{
			key: 'instance',
			label: this.$t('forms.fields.instanceName'),
		},
		{
			key: 'BrokerAddr',
			label: this.$t('config.daemon.messagings.mqtt.form.BrokerAddr'),
		},
		{
			key: 'ClientId',
			label: this.$t('forms.fields.clientId'),
		},
		{
			key: 'TopicRequest',
			label: this.$t('forms.fields.requestTopic'),
		},
		{
			key: 'TopicResponse',
			label: this.$t('forms.fields.responseTopic'),
		},
		{
			key: 'EnabledSSL',
			label: this.$t('config.daemon.messagings.mqtt.form.EnabledSSL'),
			filter: false,
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
	 * @var {boolean} loading Indicates that request is in progress
	 */
	private loading = false;

	/**
	 * @var {Array<IMqttInstance>} instances Array of MQTT messaging component instances
	 */
	private instances: Array<IMqttInstance> = [];

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		this.getInstances();
	}

	/**
	 * Updates message accepting configuration of MQTT messaging component instance
	 * @param {IMqttInstance} instance MQTT messaging instance
	 * @param {boolean} acceptAsyncMsg Message accepting policy setting
	 */
	private changeAcceptAsyncMsg(instance: IMqttInstance, acceptAsyncMsg: boolean): void {
		if (instance.acceptAsyncMsg === acceptAsyncMsg) {
			return;
		}
		this.edit(instance, {acceptAsyncMsg: acceptAsyncMsg});
	}

	/**
	 * Checks if MQTT messaging instance uses TLS
	 * @param {IMqttInstance} instance MQTT messaging instance
	 * @return {boolean} True if instance uses TLS, otherwise false
	 */
	private hasTls(instance: IMqttInstance): boolean {
		return instance.BrokerAddr.startsWith('ssl://') ||
			instance.BrokerAddr.startsWith('mqtts://') ||
			instance.BrokerAddr.startsWith('wss://');
	}

	/**
	 * Saves changes in MQTT messaging instance configuration
	 * @param {IMqttInstance} instance MQTT messaging instance
	 * @param {Record<string, boolean>} newSettings Settings to update instance with
	 */
	private edit(instance: IMqttInstance, newSettings: Record<string, boolean>): void {
		this.loading = true;
		const settings = {
			...instance,
			...newSettings,
		};
		DaemonConfigurationService.updateInstance(this.componentName, settings.instance, settings)
			.then(() => {
				this.getInstances().then(() => {
					this.$toast.success(
						this.$t('config.daemon.messagings.mqtt.messages.editSuccess', {instance: settings.instance})
							.toString()
					);
				});
			})
			.catch((error: AxiosError) => {
				this.loading = false;
				extendedErrorToast(error, 'config.daemon.messagings.mqtt.messages.editFailed', {instance: settings.instance});
			});
	}

	/**
	 * Retrieves instances of MQTT messaging component
	 * @returns {Promise<void>} Empty promise for request chaining
	 */
	private getInstances(): Promise<void> {
		if (!this.loading) {
			this.loading = true;
		}
		return DaemonConfigurationService.getComponent(this.componentName)
			.then((response: AxiosResponse) => {
				this.instances = response.data.instances;
				this.loading = false;
			})
			.catch((error: AxiosError) => {
				this.loading = false;
				extendedErrorToast(error, 'config.daemon.messagings.mqtt.messages.listFailed');
			});
	}

	/**
	 * Removes component instance
	 * @param {string} instance Component instance
	 */
	private removeInstance(instance: string): void {
		(this.$refs.deleteModal as MessagingDeleteModal).showModal(MessagingTypes.MQTT, instance);
	}
}
</script>
