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
	<v-data-table
		:headers='header'
		:items='instances'
	>
		<template #top>
			<v-toolbar dense flat>
				<v-spacer />
				<v-btn
					color='success'
					small
					to='/config/daemon/misc/monitor/add'
				>
					<v-icon small>
						mdi-plus
					</v-icon>
					{{ $t('table.actions.add') }}
				</v-btn>
			</v-toolbar>
		</template>
		<template #[`item.acceptOnlyLocalhost`]='{item}'>
			<v-menu>
				<template #activator='{ on, attrs }'>
					<v-btn
						:color='item.acceptOnlyLocalhost ? "success": "error"'
						small
						v-bind='attrs'
						v-on='on'
					>
						{{ $t(`states.${item.acceptOnlyLocalhost ? "enabled" : "disabled"}`) }}
						<v-icon>mdi-menu-down</v-icon>
					</v-btn>
				</template>
				<v-list>
					<v-list-item @click='changeAcceptOnlyLocalhost(item.webSocket, true)'>
						{{ $t('states.enabled') }}
					</v-list-item>
					<v-list-item @click='changeAcceptOnlyLocalhost(item.webSocket, false)'>
						{{ $t('states.disabled') }}
					</v-list-item>
				</v-list>
			</v-menu>
		</template>
		<template #[`item.tlsEnabled`]='{item}'>
			<v-menu>
				<template #activator='{ on, attrs }'>
					<v-btn
						:color='item.tlsEnabled ? "success": "error"'
						small
						v-bind='attrs'
						v-on='on'
					>
						{{ $t(`states.${item.tlsEnabled ? "enabled" : "disabled"}`) }}
						<v-icon>mdi-menu-down</v-icon>
					</v-btn>
				</template>
				<v-list>
					<v-list-item @click='changeTls(item.webSocket, true)'>
						{{ $t('states.enabled') }}
					</v-list-item>
					<v-list-item @click='changeTls(item.webSocket, false)'>
						{{ $t('states.disabled') }}
					</v-list-item>
				</v-list>
			</v-menu>
		</template>
		<template #[`item.actions`]='{item}'>
			<v-btn
				color='info'
				small
				:to='"/config/daemon/misc/monitor/edit/" + item.instance'
			>
				<v-icon small>
					mdi-pencil
				</v-icon>
				{{ $t('table.actions.edit') }}
			</v-btn>
			<v-dialog v-model='deleteModal' width='50%'>
				<template #activator='{ on, attrs }'>
					<v-btn
						color='error'
						small
						v-bind='attrs'
						@click='deleteInstance = {monitor: item.monitor.instance, webSocket: item.webSocket.instance}'
						v-on='on'
					>
						<v-icon small>
							mdi-delete
						</v-icon>
						{{ $t('table.actions.delete') }}
					</v-btn>
				</template>
				<v-card>
					<v-card-title>{{ $t('config.daemon.misc.monitor.modal.title') }}</v-card-title>
					<v-card-text>{{ $t('config.daemon.misc.monitor.modal.prompt', {instance: deleteInstance.monitor}) }}</v-card-text>
					<v-card-actions>
						<v-btn
							color='error'
							@click='removeInterface'
						>
							{{ $t('forms.delete') }}
						</v-btn>
						<v-spacer />
						<v-btn
							color='secondary'
							@click='deleteInstance = ""'
						>
							{{ $t('forms.cancel') }}
						</v-btn>
					</v-card-actions>
				</v-card>
			</v-dialog>
		</template>
	</v-data-table>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';

import {extendedErrorToast} from '@/helpers/errorToast';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {DataTableHeader} from 'vuetify';

@Component({})

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
	private deleteInstance: Record<string, string>|'' = '';

	/**
	 * @var {Array<DataTableHeader>} header Data table header
	 */
	private header: Array<DataTableHeader> = [
		{
			value: 'instance',
			text: this.$t('forms.fields.instanceName').toString(),
		},
		{
			value: 'reportPeriod',
			text: this.$t('config.daemon.misc.monitor.form.reportPeriod').toString(),
		},
		{
			value: 'port',
			text: this.$t('config.daemon.misc.monitor.form.WebsocketPort').toString(),
		},
		{
			value: 'acceptOnlyLocalhost',
			text: this.$t('config.daemon.misc.monitor.form.acceptOnlyLocalhost').toString(),
			filterable: false,
		},
		{
			value: 'tlsEnabled',
			text: this.$t('config.daemon.messagings.websocket.form.tlsEnabled').toString(),
			filterable: false,
		},
		{
			value: 'actions',
			text: this.$t('table.actions.title').toString(),
			sortable: false,
			filterable: false,
			align: 'end',
		},
	];

	/**
	 * @var {Array<unknown>} instances Array of monitoring component instances
	 */
	private instances: Array<unknown> = [];

	/**
	 * @var {boolean} deleteModal Delete modal visibility
	 */
	get deleteModal(): boolean {
		return this.deleteInstance !== '';
	}

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
				const instances: Array<unknown> = [];
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
		if (this.deleteInstance === '') {
			return;
		}
		const deleteInstance = this.deleteInstance;
		this.deleteInstance = '';
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
