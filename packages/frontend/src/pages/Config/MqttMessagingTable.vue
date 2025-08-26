<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

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
					<template #tls='{item}'>
						<td>
							<BooleanCheckIcon
								:value='isTls(item)'
								size='xl'
							/>
						</td>
					</template>
					<template #acceptAsyncMsg='{item}'>
						<td>
							<BooleanCheckIcon
								:value='item.acceptAsyncMsg'
								size='xl'
							/>
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
					<template #show_details='{ item, index }'>
						<td class='py-2'>
							<CButton
								color='info'
								size='sm'
								@click='toggleDetails(item, index)'
							>
								<CIcon :content='cilInfo' />
							</CButton>
						</td>
					</template>
					<template #details='{ item }'>
						<CCollapse :show='item._toggled'>
							<CCardBody>
								<table class='table'>
									<tr>
										<th>
											{{ $t('forms.fields.requestTopic') }}
										</th>
										<td>
											{{ item.TopicRequest }}
										</td>
									</tr>
									<tr>
										<th>
											{{ $t('forms.fields.responseTopic') }}
										</th>
										<td>{{ item.TopicResponse }}</td>
									</tr>
									<tr>
										<th>
											{{ $t('config.daemon.messagings.mqtt.form.QoS') }}
										</th>
										<td>{{ $t(`config.daemon.messagings.mqtt.form.QoSes.${item.Qos}`) }}: {{ $t(`config.daemon.messagings.mqtt.messages.qos.${item.Qos}`) }}</td>
									</tr>
									<tr>
										<th>
											{{ $t('config.daemon.messagings.mqtt.form.Persistence') }}
										</th>
										<td>
											{{ $t(`config.daemon.messagings.mqtt.form.Persistences.${item.Persistence}`) }}: {{ $t(`config.daemon.messagings.mqtt.messages.persistence.${item.Persistence}`) }}
										</td>
									</tr>
								</table>
							</CCardBody>
						</CCollapse>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
		<MessagingDeleteModal ref='deleteModal' @deleted='getInstances()' />
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
	CCollapse,
	CDataTable,
	CDropdown,
	CDropdownItem,
	CIcon
} from '@coreui/vue/src';
import MessagingDeleteModal from '@/components/Config/Messagings/MessagingDeleteModal.vue';

import {
	cilCheckCircle,
	cilInfo,
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
import BooleanCheckIcon from '@/components/ui/BooleanCheckIcon.vue';

@Component({
	components: {
		BooleanCheckIcon,
		CButton,
		CButtonClose,
		CCard,
		CCardBody,
		CCardHeader,
		CCollapse,
		CDataTable,
		CDropdown,
		CDropdownItem,
		CIcon,
		MessagingDeleteModal,
	},
	data: () => ({
		cilCheckCircle,
		cilInfo,
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
			key: 'tls',
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
		{ 
			key: 'show_details', 
			label: '', 
			_style: 'width:1%', 
			sorter: false, 
			filter: false
		}
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

	/**
	 * Determines whether connection is over TLS
	 * @param {IMqttInstance} instance MQTT instance
	 */
	private isTls(instance: IMqttInstance): boolean {
		return instance.BrokerAddr.startsWith('ssl://') ||
			instance.BrokerAddr.startsWith('mqtts://') ||
			instance.BrokerAddr.startsWith('wss://');
	}

	/**
	 * Toggles details display
	 * @param {IMqttInstance} instance MQTT instance
	 * @param {number} index Instance index
	 */
	private toggleDetails(instance: IMqttInstance, index: number): void {
		this.$set(this.instances[index], '_toggled', !instance._toggled);
	}
}
</script>
