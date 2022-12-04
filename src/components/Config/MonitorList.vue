<!--
Copyright 2017-2022 IQRF Tech s.r.o.
Copyright 2019-2022 MICRORISC s.r.o.

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
					<template #acceptOnlyLocalhost='{item}'>
						<td>
							<CDropdown
								:color='item.acceptOnlyLocalhost ? "success": "danger"'
								:toggler-text='$t("states." + (item.acceptOnlyLocalhost ? "enabled" : "disabled"))'
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
								:color='item.tlsEnabled ? "success": "danger"'
								:toggler-text='$t("states." + (item.tlsEnabled !== undefined ? item.tlsEnabled ? "enabled" : "disabled" : "disabled"))'
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
								color='info'
								size='sm'
								:to='"/config/daemon/misc/monitor/edit/" + item.monitor.instance'
							>
								<CIcon :content='icons.edit' size='sm' />
								{{ $t('table.actions.edit') }}
							</CButton> <CButton
								color='danger'
								size='sm'
								@click='deleteInstance = {monitor: item.monitor.instance, webSocket: item.webSocket.instance}'
							>
								<CIcon :content='icons.remove' size='sm' />
								{{ $t('table.actions.delete') }}
							</CButton>
						</td>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
		<CModal
			color='danger'
			:show='deleteInstance !== null'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('config.daemon.misc.monitor.modal.title') }}
				</h5>
			</template>
			<div v-if='deleteInstance !== null'>
				{{ $t('config.daemon.misc.monitor.modal.prompt', {instance: deleteInstance.monitor}) }}
			</div>
			<template #footer>
				<CButton
					color='danger'
					@click='removeInterface()'
				>
					{{ $t('forms.delete') }}
				</CButton> <CButton
					color='secondary'
					@click='deleteInstance = null'
				>
					{{ $t('forms.cancel') }}
				</CButton>
			</template>
		</CModal>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CDataTable, CDropdown, CDropdownItem, CIcon, CModal} from '@coreui/vue/src';

import {cilPencil, cilPlus, cilTrash} from '@coreui/icons';
import {extendedErrorToast} from '@/helpers/errorToast';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {IField} from '@/interfaces/coreui';

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
		CModal,
	},
})

/**
 * List of monitoring component instances
 */
export default class MonitorList extends Vue {
	/**
	 * @constant {Record<string, string>} componentNames Names of monitor and websocket components
	 */
	private componentNames: Record<string, string> = {
		monitor: 'iqrf::MonitorService',
		webSocket: 'shape::WebsocketCppService'
	};

	/**
	 * @var {Record<string, string>|null} deleteInstance Monitor component instance object used in remove modal
	 */
	private deleteInstance: Record<string, string>|null = null;

	/**
	 * @constant {Array<IField>} fields Array of CoreUI data table columns
	 */
	private fields: Array<IField> =  [
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
	 * @constant {Record<string, Array<string>>} icons Dictionary of CoreUI Icons
	 */
	private icons: Record<string, Array<string>> = {
		add: cilPlus,
		edit: cilPencil,
		remove: cilTrash,
	};

	/**
	 * @var {Array<unknown>} instances Array of monitoring component instances
	 */
	private instances: Array<unknown> = [];

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
		return Promise.all([
			DaemonConfigurationService.getComponent(this.componentNames.monitor),
			DaemonConfigurationService.getComponent(this.componentNames.webSocket),
		])
			.then((responses: Array<AxiosResponse>) => {
				const monitors = responses[0].data.instances;
				const webSockets = responses[1].data.instances;
				let instances: Array<unknown> = [];
				for (const monitor of monitors) {
					if (monitor.RequiredInterfaces === undefined ||
							monitor.RequiredInterfaces.length === 0 ||
							monitor.RequiredInterfaces[0].name !== 'shape::IWebsocketService' ||
							monitor.RequiredInterfaces[0].target.instance === undefined) {
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
							instance: monitor.instance,
							reportPeriod: monitor.reportPeriod,
							acceptAsyncMsg: monitor.acceptAsyncMsg,
							port: webSocket.WebsocketPort,
							acceptOnlyLocalhost: webSocket.acceptOnlyLocalhost,
							tlsEnabled: webSocket.tlsEnabled
						});
					}
				}
				this.instances = instances;
				this.$emit('fetched', {name: 'monitor', success: true});
			})
			.catch(() => {
				this.$emit('fetched', {name: 'monitor', success: false});
			});
	}

	/**
	 * Updates websocket service message accepting setting
	 * @param service WebSocket service instance
	 * @param {boolean} setting Message accepting setting
	 */
	private changeAcceptOnlyLocalhost(service, setting: boolean): void {
		if (service.acceptOnlyLocalhost === setting) {
			return;
		}
		service.acceptOnlyLocalhost = setting;
		this.changeServiceSetting(service);
	}

	/**
	 * Updates websocket service TLS setting
	 * @param service WebSocket service instance
	 * @param {boolean} setting TLS setting
	 */
	private changeTls(service, setting: boolean): void {
		if (service.tlsEnabled === setting) {
			return;
		}
		service.tlsEnabled = setting;
		this.changeServiceSetting(service);
	}

	/**
	 * Updates configuration of websocket service
	 * @param service WebSocket service instance
	 */
	private changeServiceSetting(service): void {
		this.$store.commit('spinner/SHOW');
		DaemonConfigurationService.updateInstance(this.componentNames.webSocket, service.instance, service)
			.then(() => {
				this.getConfig().then(() => {
					this.$toast.success(
						this.$t('config.daemon.misc.monitor.messages.editSuccess', {instance: service.instance})
							.toString()
					);
				});
			})
			.catch((error: AxiosError) => extendedErrorToast(
				error,
				'config.daemon.misc.monitor.messages.editFailed',
				{instance: service.instance}
			));
	}

	/**
	 * Removes instance of the monitoring component
	 */
	private removeInterface(): void {
		if (this.deleteInstance === null) {
			return;
		}
		const deleteInstance = this.deleteInstance;
		this.deleteInstance = null;
		this.$store.commit('spinner/SHOW');
		Promise.all([
			DaemonConfigurationService.deleteInstance(this.componentNames.monitor, deleteInstance.monitor),
			DaemonConfigurationService.deleteInstance(this.componentNames.webSocket, deleteInstance.webSocket),
		])
			.then(() => {
				this.getConfig()
					.then(() => this.$toast.success(
						this.$t('config.daemon.misc.monitor.messages.deleteSuccess', {instance: deleteInstance.monitor})
							.toString())
					);
			})
			.catch((error: AxiosError) => extendedErrorToast(
				error,
				'config.daemon.misc.monitor.messages.deleteFailed',
				{instance: deleteInstance.monitor}
			));
	}
}
</script>

<style scoped>
.card-header {
	padding-bottom: 0;
}
</style>
