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
	<v-card flat tile>
		<v-card-text>
			<v-data-table
				:headers='header'
				:items='instances'
				:loading='loading'
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
				<template #[`item.instance`]='{item}'>
					{{ item.monitor.instance }}
				</template>
				<template #[`item.reportPeriod`]='{item}'>
					{{ item.monitor.reportPeriod }}
				</template>
				<template #[`item.port`]='{item}'>
					{{ item.webSocket.WebsocketPort }}
				</template>
				<template #[`item.acceptOnlyLocalhost`]='{item}'>
					<v-menu offset-y>
						<template #activator='{attrs, on}'>
							<v-btn
								:color='item.webSocket.acceptOnlyLocalhost ? "success": "error"'
								small
								v-bind='attrs'
								v-on='on'
							>
								{{ $t(`states.${item.webSocket.acceptOnlyLocalhost ? "enabled" : "disabled"}`) }}
								<v-icon>mdi-menu-down</v-icon>
							</v-btn>
						</template>
						<v-list dense>
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
					<v-menu offset-y>
						<template #activator='{attrs, on}'>
							<v-btn
								:color='item.webSocket.tlsEnabled ? "success": "error"'
								small
								v-bind='attrs'
								v-on='on'
							>
								{{ $t(`states.${item.webSocket.tlsEnabled ? "enabled" : "disabled"}`) }}
								<v-icon>mdi-menu-down</v-icon>
							</v-btn>
						</template>
						<v-list dense>
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
						:to='"/config/daemon/misc/monitor/edit/" + item.monitor.instance'
					>
						<v-icon small>
							mdi-pencil
						</v-icon>
						{{ $t('table.actions.edit') }}
					</v-btn> <MonitorDeleteDialog
						:instance='item'
						@deleted='getConfig'
					/>
				</template>
			</v-data-table>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import MonitorDeleteDialog from './MonitorDeleteDialog.vue';

import {extendedErrorToast} from '@/helpers/errorToast';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {DataTableHeader} from 'vuetify';
import {IMonitorWsInstance} from '@/interfaces/Config/Misc';
import {IWsService} from '@/interfaces/Config/Messaging';

/**
 * List of monitoring component instances
 */
@Component({
	components: {
		MonitorDeleteDialog,
	},
})
export default class MonitorList extends Vue {
	/**
	 * @var {boolean} loading Flag for loading state
	 */
	private loading = false;

	/**
	 * @constant {Record<string, string>} componentNames Names of monitor and websocket components
	 */
	private componentNames: Record<string, string> = {
		monitor: 'iqrf::MonitorService',
		webSocket: 'shape::WebsocketCppService'
	};

	/**
	 * @var {Array<IMonitorWsInstance>} instances Array of monitoring component instances
	 */
	private instances: Array<IMonitorWsInstance> = [];

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
		this.loading = true;
		return Promise.all([
			DaemonConfigurationService.getComponent(this.componentNames.monitor),
			DaemonConfigurationService.getComponent(this.componentNames.webSocket),
		])
			.then((responses: Array<AxiosResponse>) => {
				const monitors = responses[0].data.instances;
				const webSockets = responses[1].data.instances;
				const instances: Array<IMonitorWsInstance> = [];
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
						});
					}
				}
				this.instances = instances;
				this.loading = false;
			})
			.catch(() => {
				this.loading = false;
				this.$toast.error(
					this.$t('config.daemon.messages.configFetchFailed', {children: 'monitor'},)
						.toString()
				);
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
				this.loading = true;
				extendedErrorToast(error, 'config.daemon.misc.monitor.messages.editFailed', {instance: service.instance});
			});
	}
}
</script>
