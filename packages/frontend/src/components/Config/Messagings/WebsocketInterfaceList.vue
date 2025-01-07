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
			{{ $t('config.daemon.messagings.websocket.interface.title') }}
		</h1>
		<v-card>
			<v-card-text>
				<v-data-table
					:loading='loading'
					:headers='headers'
					:items='instances'
					:no-data-text='$t("table.messages.noRecords")'
				>
					<template #top>
						<v-toolbar dense flat>
							<v-spacer />
							<v-btn
								class='mr-1'
								color='success'
								small
								to='/config/daemon/messagings/websocket/add'
							>
								<v-icon small>
									mdi-plus
								</v-icon>
							</v-btn>
							<v-btn
								color='primary'
								small
								@click='getConfig'
							>
								<v-icon small>
									mdi-refresh
								</v-icon>
							</v-btn>
						</v-toolbar>
					</template>
					<template #[`item.instanceMessaging`]='{item}'>
						{{ item.messaging.instance }}
					</template>
					<template #[`item.port`]='{item}'>
						{{ item.service.WebsocketPort }}
					</template>
					<template #[`item.acceptAsyncMsg`]='{item}'>
						<v-menu offset-y>
							<template #activator='{attrs, on}'>
								<v-btn
									:color='item.messaging.acceptAsyncMsg ? "success": "error"'
									small
									v-bind='attrs'
									v-on='on'
								>
									{{ $t(`states.${item.messaging.acceptAsyncMsg ? "enabled" : "disabled"}`) }}
									<v-icon>mdi-menu-down</v-icon>
								</v-btn>
							</template>
							<v-list dense>
								<v-list-item
									dense
									@click='changeAcceptAsyncMsg(item.messaging, true)'
								>
									{{ $t('states.enabled') }}
								</v-list-item>
								<v-list-item
									dense
									@click='changeAcceptAsyncMsg(item.messaging, false)'
								>
									{{ $t('states.disabled') }}
								</v-list-item>
							</v-list>
						</v-menu>
					</template>
					<template #[`item.acceptOnlyLocalhost`]='{item}'>
						<v-menu offset-y>
							<template #activator='{attrs, on}'>
								<v-btn
									:color='item.service.acceptOnlyLocalhost ? "success": "error"'
									small
									v-bind='attrs'
									v-on='on'
								>
									{{ $t(`states.${item.service.acceptOnlyLocalhost ? "enabled" : "disabled"}`) }}
									<v-icon>mdi-menu-down</v-icon>
								</v-btn>
							</template>
							<v-list dense>
								<v-list-item
									dense
									@click='changeAcceptOnlyLocalhost(item.service, true)'
								>
									{{ $t('states.enabled') }}
								</v-list-item>
								<v-list-item
									dense
									@click='changeAcceptOnlyLocalhost(item.service, false)'
								>
									{{ $t('states.disabled') }}
								</v-list-item>
							</v-list>
						</v-menu>
					</template>
					<template #[`item.tlsEnabled`]='{item}'>
						<v-menu offset-y>
							<template #activator='{attrs, on}'>
								<v-btn
									:color='item.service.tlsEnabled ? "success": "error"'
									small
									v-bind='attrs'
									v-on='on'
								>
									{{ $t(`states.${item.service.tlsEnabled ? "enabled" : "disabled"}`) }}
									<v-icon>mdi-menu-down</v-icon>
								</v-btn>
							</template>
							<v-list dense>
								<v-list-item
									dense
									@click='changeTls(item.service, true)'
								>
									{{ $t('states.enabled') }}
								</v-list-item>
								<v-list-item
									dense
									@click='changeTls(item.service, false)'
								>
									{{ $t('states.disabled') }}
								</v-list-item>
							</v-list>
						</v-menu>
					</template>
					<template #[`item.actions`]='{item}'>
						<v-btn
							class='mr-1'
							color='info'
							small
							:to='"/config/daemon/messagings/websocket/edit/" + item.messaging.instance'
						>
							<v-icon small>
								mdi-pencil
							</v-icon>
							{{ $t('table.actions.edit') }}
						</v-btn>
						<v-btn
							color='error'
							small
							@click='removeInstance(item.messaging.instance, item.service.instance)'
						>
							<v-icon small>
								mdi-delete
							</v-icon>
							{{ $t('table.actions.delete') }}
						</v-btn>
					</template>
				</v-data-table>
			</v-card-text>
		</v-card>
		<WebsocketInterfaceDeleteModal
			v-model='showDeleteModal'
			:messaging-instance='messagingDeleteModel'
			:service-instance='serviceDeleteModal'
			@deleted='getConfig'
		/>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import WebsocketInterfaceDeleteModal from './WebsocketInterfaceDeleteModal.vue';

import {extendedErrorToast} from '@/helpers/errorToast';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {IWsInterface, IWsMessaging, IWsService, ModalInstance} from '@/interfaces/Config/Messaging';
import {DataTableHeader} from 'vuetify';

@Component({
	components: {
		WebsocketInterfaceDeleteModal,
	},
})

/**
 * Websocket interface list card for normal user
 */
export default class WebsocketInterfaceList extends Vue {
	/**
	 * @var {boolean} loading Loading visibility
	 */
	private loading = false;

	/**
	 * @constant {ModalInstance} componentNames Websocket messaging and service component names
	 */
	private readonly componentNames: ModalInstance = {
		messaging: 'iqrf::WebsocketMessaging',
		service: 'shape::WebsocketCppService',
	};

	/**
	 * @var {Array<IWsInterface>} instances Array of websocket interface instances
	 */
	private instances: Array<IWsInterface> = [];

	/**
	 * @var {boolean} showDeleteModal Controls delete modal visiblity
	 */
	private showDeleteModal = false;

	/**
	 * @var {string} messagingDeleteModel Messaging to delete
	 */
	private messagingDeleteModel = '';

	/**
	 * @var {string} serviceDeleteModal Websocket service to delete
	 */
	private serviceDeleteModal = '';

	/**
	 * @var {Array<DataTableHeader>} headers Data table header
	 */
	private readonly headers: Array<DataTableHeader> = [
		{
			value: 'instanceMessaging',
			text: this.$t('forms.fields.instanceName').toString(),
		},
		{
			value: 'port',
			text: this.$t('config.daemon.messagings.websocket.form.WebsocketPort').toString(),
		},
		{
			value: 'acceptAsyncMsg',
			text: this.$t('config.daemon.messagings.acceptAsyncMsg').toString(),
			filterable: false,
		},
		{
			value: 'acceptOnlyLocalhost',
			text: this.$t('config.daemon.messagings.websocket.form.acceptOnlyLocalhost').toString(),
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
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		this.getConfig();
	}

	/**
	 * Retrieves instances of Websocket daemon components
	 */
	private getConfig(): Promise<void> {
		this.loading = true;
		return Promise.all([
			DaemonConfigurationService.getComponent(this.componentNames.messaging),
			DaemonConfigurationService.getComponent(this.componentNames.service),
		])
			.then((responses: Array<AxiosResponse>) => {
				const messagings = responses[0].data.instances;
				const services = responses[1].data.instances;
				const instances: Array<IWsInterface> = [];
				for (const messaging of messagings) {
					if (messaging.RequiredInterfaces === undefined ||
							messaging.RequiredInterfaces.length === 0 ||
							messaging.RequiredInterfaces[0].name !== 'shape::IWebsocketService' ||
							messaging.RequiredInterfaces[0].target.instance === undefined) {
						continue;
					}
					const serviceInstance = messaging.RequiredInterfaces[0].target.instance;
					for (const service of services) {
						if (service.instance !== serviceInstance) {
							continue;
						}
						instances.push({
							messaging: messaging,
							service: service,
						});
					}
				}
				this.instances = instances;
				this.loading = false;
			})
			.catch((error: AxiosError) => {
				this.loading = false;
				extendedErrorToast(error, 'config.daemon.messagings.websocket.interface.messages.listFailed');
			});
	}

	/**
	 * Updates accepted message source of Websocket service component instance
	 * @param {IWsService} service Websocket service instance
	 * @param {boolean} acceptOnlyLocalhost New setting
	 */
	private changeAcceptOnlyLocalhost(service: IWsService, acceptOnlyLocalhost: boolean): void {
		if (service.acceptOnlyLocalhost === acceptOnlyLocalhost) {
			return;
		}
		this.editService(service, {acceptOnlyLocalhost: acceptOnlyLocalhost});
	}

	/**
	 * Updates TLS enabled setting of Websocket service component instance
	 * @param {IWsService} service Websocket service instance
	 * @param {boolean} tlsEnabled New setting
	 */
	private changeTls(service: IWsService, tlsEnabled: boolean): void {
		if (service.tlsEnabled === tlsEnabled) {
			return;
		}
		this.editService(service, {tlsEnabled: tlsEnabled});
	}

	/**
	 * Saves changes in Websocket service instance configuration
	 * @param {IWsService} service Websocket service instance
	 * @param {Record<string, boolean>} newSettings Settings to update instance with
	 */
	private editService(service: IWsService, newSettings: Record<string, boolean>): void {
		this.loading = true;
		const settings = {
			...service,
			...newSettings,
		};
		DaemonConfigurationService.updateInstance(this.componentNames.service, settings.instance, settings)
			.then(() => {
				this.getConfig().then(() => {
					this.$toast.success(
						this.$t('config.daemon.messagings.websocket.service.messages.updateSuccess', {service: settings.instance})
							.toString()
					);
				});
			})
			.catch((error: AxiosError) => {
				this.loading = false;
				extendedErrorToast(error, 'config.daemon.messagings.websocket.interface.messages.updateFailed', {interface: settings.instance});
			});
	}

	/**
	 * Updates accepting asynchronous messages setting of Websocket messaging component instance
	 * @param {IWsMessaging} instance Websocket messaging instance
	 * @param {boolean} setting new setting
	 */
	private changeAcceptAsyncMsg(instance: IWsMessaging, setting: boolean): void {
		if (instance.acceptAsyncMsg === setting) {
			return;
		}
		this.loading = true;
		instance.acceptAsyncMsg = setting;
		DaemonConfigurationService.updateInstance(this.componentNames.messaging, instance.instance, instance)
			.then(() => {
				this.getConfig().then(() => {
					this.$toast.success(
						this.$t('config.daemon.messagings.websocket.interface.messages.updateSuccess', {interface: instance.instance})
							.toString()
					);
				});
			})
			.catch((error: AxiosError) => {
				this.loading = false;
				extendedErrorToast(error, 'config.daemon.messagings.websocket.interface.messages.updateFailed', {interface: instance.instance});
			});
	}

	/**
	 * Removes websocket interface instance
	 */
	private removeInstance(messaging: string, service: string): void {
		this.messagingDeleteModel = messaging;
		this.serviceDeleteModal = service;
		this.showDeleteModal = true;
	}
}
</script>
