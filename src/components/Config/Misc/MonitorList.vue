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
		<CCard class='border-0 card-margin-bottom'>
			<CCardHeader class='border-0'>
				<CButton
					color='success'
					size='sm'
					class='float-right'
					to='/config/daemon/misc/monitor/add'
				>
					<CIcon :content='cilPlus' size='sm' />
					{{ $t('table.actions.add') }}
				</CButton>
			</CCardHeader>
			<CCardBody>
				<CDataTable
					:loading='loading'
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
					<template #instance='{item}'>
						<td>
							{{ item.monitor.instance }}
						</td>
					</template>
					<template #reportPeriod='{item}'>
						<td>
							{{ item.monitor.reportPeriod }}
						</td>
					</template>
					<template #port='{item}'>
						<td>
							{{ item.webSocket.WebsocketPort }}
						</td>
					</template>
					<template #acceptOnlyLocalhost='{item}'>
						<td>
							<CDropdown
								:color='item.webSocket.acceptOnlyLocalhost ? "success": "danger"'
								:toggler-text='$t(`states.${item.webSocket.acceptOnlyLocalhost ? "enabled" : "disabled"}`)'
								size='sm'
							>
								<CDropdownItem @click='changeAcceptOnlyLocalhost(item.webSocket, true)'>
									{{ $t('states.enabled') }}
								</CDropdownItem>
								<CDropdownItem @click='changeAcceptOnlyLocalhost(item.webSocket, false)'>
									{{ $t('states.disabled') }}
								</CDropdownItem>
							</CDropdown>
						</td>
					</template>
					<template #tlsEnabled='{item}'>
						<td>
							<CDropdown
								:color='item.webSocket.tlsEnabled ? "success": "danger"'
								:toggler-text='$t(`states.${(item.webSocket.tlsEnabled ?? false) ? "enabled" : "disabled"}`)'
								size='sm'
							>
								<CDropdownItem @click='changeTls(item.webSocket, true)'>
									{{ $t('states.enabled') }}
								</CDropdownItem>
								<CDropdownItem @click='changeTls(item.webSocket, false)'>
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
								size='sm'
								:to='"/config/daemon/misc/monitor/edit/" + item.monitor.instance'
							>
								<CIcon :content='cilPencil' size='sm' />
								{{ $t('table.actions.edit') }}
							</CButton>
							<CButton
								color='danger'
								size='sm'
								@click='removeInstance(item.monitor.instance, item.webSocket.instance)'
							>
								<CIcon :content='cilTrash' size='sm' />
								{{ $t('table.actions.delete') }}
							</CButton>
						</td>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
		<MonitorDeleteModal ref='deleteModal' @deleted='getConfig' />
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CDataTable, CDropdown, CDropdownItem, CIcon} from '@coreui/vue/src';
import MonitorDeleteModal from '@/components/Config/Misc/MonitorDeleteModal.vue';

import {cilPencil, cilPlus, cilTrash} from '@coreui/icons';
import {extendedErrorToast} from '@/helpers/errorToast';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {IField} from '@/interfaces/Coreui';
import {IMonitorComponent} from '@/interfaces/Config/Misc';
import {IWsService} from '@/interfaces/Config/Messaging';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CDataTable,
		CDropdown,
		CDropdownItem,
		CIcon,
		MonitorDeleteModal,
	},
	data: () => ({
		cilPencil,
		cilPlus,
		cilTrash,
	}),
})

/**
 * List of monitoring component instances
 */
export default class MonitorList extends Vue {
	/**
	 * @constant {Record<string, string>} componentNames Names of monitor and websocket components
	 */
	private readonly componentNames: Record<string, string> = {
		monitor: 'iqrf::MonitorService',
		webSocket: 'shape::WebsocketCppService'
	};

	/**
	 * @constant {Array<IField>} fields Array of CoreUI data table columns
	 */
	private readonly fields: Array<IField> =  [
		{
			key: 'instance',
			label: this.$t('forms.fields.instanceName'),
		},
		{
			key: 'reportPeriod',
			label: this.$t('config.daemon.misc.monitor.form.reportPeriod'),
		},
		{
			key: 'port',
			label: this.$t('config.daemon.misc.monitor.form.WebsocketPort'),
		},
		{
			key: 'acceptOnlyLocalhost',
			label: this.$t('config.daemon.misc.monitor.form.acceptOnlyLocalhost'),
			filter: false,
		},
		{
			key: 'tlsEnabled',
			label: this.$t('config.daemon.messagings.websocket.form.tlsEnabled'),
			filter: false,
		},
		{
			key: 'actions',
			label: this.$t('table.actions.title'),
			filter: false,
			sorter: false,
		},
	];

	/**
	 * @var {boolean} loading Indicates that request is in progress
	 */
	private loading = false;

	/**
	 * @var {Array<IMonitorComponent>} instances Array of monitoring component instances
	 */
	private instances: Array<IMonitorComponent> = [];

	/**
	 * Vue lifecycle hook created
	 */
	mounted(): void {
		this.getConfig();
	}

	/**
	 * Retrieves configuration of the monitoring component and websocket instance
	 * @returns {Promise<void>} Empty promise for request chaining
	 */
	private getConfig(): Promise<void> {
		if (!this.loading) {
			this.loading = true;
		}
		return Promise.all([
			DaemonConfigurationService.getComponent(this.componentNames.monitor),
			DaemonConfigurationService.getComponent(this.componentNames.webSocket),
		])
			.then((responses: Array<AxiosResponse>) => {
				const monitors = responses[0].data.instances;
				const webSockets = responses[1].data.instances;
				const instances: Array<IMonitorComponent> = [];
				for (const monitor of monitors) {
					if (monitor.RequiredInterfaces === undefined ||
						monitor.RequiredInterfaces.length === 0 ||
						monitor.RequiredInterfaces[0].name !== 'shape::IWebsocketService' ||
						monitor.RequiredInterfaces[0].target.instance === undefined
					) {
						continue;
					}
					const webSocketInstance = monitor.RequiredInterfaces[0].target.instance;
					for (const webSocket of webSockets) {
						if (webSocket.instance !== webSocketInstance) {
							continue;
						}
						instances.push({
							monitor: monitor,
							webSocket: webSocket,
						});
					}
				}
				this.instances = instances;
				this.loading = false;
				this.$emit('fetched', {name: 'monitor', success: true});
			})
			.catch(() => {
				this.$emit('fetched', {name: 'monitor', success: false});
			});
	}

	/**
	 * Updates websocket service message accepting setting
	 * @param {IWsService} service WebSocket service instance
	 * @param {boolean} acceptOnlyLocalhost Message accepting setting
	 */
	private changeAcceptOnlyLocalhost(service: IWsService, acceptOnlyLocalhost: boolean): void {
		if (service.acceptOnlyLocalhost === acceptOnlyLocalhost) {
			return;
		}
		this.changeServiceSetting(service, {acceptOnlyLocalhost: acceptOnlyLocalhost});
	}

	/**
	 * Updates websocket service TLS setting
	 * @param {IWsService} service WebSocket service instance
	 * @param {boolean} tlsEnabled TLS setting
	 */
	private changeTls(service: IWsService, tlsEnabled: boolean): void {
		if (service.tlsEnabled === tlsEnabled) {
			return;
		}
		this.changeServiceSetting(service, {tlsEnabled: tlsEnabled});
	}

	/**
	 * Updates configuration of websocket service
	 * @param {IWsService} service WebSocket service instance
	 * @param {Record<string, boolean>} newSettings Settings to update instance with
	 */
	private changeServiceSetting(service: IWsService, newSettings: Record<string, boolean>): void {
		this.loading = true;
		const settings = {
			...service,
			...newSettings,
		};
		DaemonConfigurationService.updateInstance(this.componentNames.webSocket, service.instance, settings)
			.then(() => {
				this.getConfig().then(() => {
					this.$toast.success(
						this.$t('config.daemon.misc.monitor.messages.editSuccess', {instance: service.instance})
							.toString()
					);
				});
			})
			.catch((error: AxiosError) => {
				this.loading = false;
				extendedErrorToast(error, 'config.daemon.misc.monitor.messages.editFailed', {instance: service.instance});
			});

	}

	/**
	 * Removes monitor instance
	 * @param {string} monitor Monitor instance
	 * @param {string} service Websocket instance
	 */
	private removeInstance(monitor: string, websocket: string): void {
		(this.$refs.deleteModal as MonitorDeleteModal).showModal(monitor, websocket);
	}
}
</script>
