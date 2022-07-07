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
			{{ $t('config.daemon.messagings.websocket.interface.title') }}
		</h1>
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
						to='/config/daemon/messagings/websocket/add'
					>
						<v-icon small>
							mdi-plus
						</v-icon>
						{{ $t('table.actions.add') }}
					</v-btn>
				</v-toolbar>
			</template>
			<template #[`item.acceptAsyncMsg`]='{item}'>
				<v-menu>
					<template #activator='{ on, attrs }'>
						<v-btn
							:color='item.acceptAsyncMsg ? "success": "error"'
							small
							v-bind='attrs'
							v-on='on'
						>
							{{ $t(`states.${item.acceptAsyncMsg ? "enabled" : "disabled"}`) }}
							<v-icon>mdi-menu-down</v-icon>
						</v-btn>
					</template>
					<v-list>
						<v-list-item @click='changeAcceptAsyncMsg(item.messaging, true)'>
							{{ $t('states.enabled') }}
						</v-list-item>
						<v-list-item @click='changeAcceptAsyncMsg(item.messaging, false)'>
							{{ $t('states.disabled') }}
						</v-list-item>
					</v-list>
				</v-menu>
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
						<v-list-item @click='changeAcceptOnlyLocalhost(item.service, true)'>
							{{ $t('states.enabled') }}
						</v-list-item>
						<v-list-item @click='changeAcceptOnlyLocalhost(item.service, false)'>
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
						<v-list-item @click='changeTls(item.service, true)'>
							{{ $t('states.enabled') }}
						</v-list-item>
						<v-list-item @click='changeTls(item.service, false)'>
							{{ $t('states.disabled') }}
						</v-list-item>
					</v-list>
				</v-menu>
			</template>
			<template #[`item.actions`]='{item}'>
				<v-btn
					color='info'
					small
					:to='"/config/daemon/messagings/websocket/edit/" + item.instanceMessaging'
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
							@click='deleteInstance = {messaging: item.messaging.instance, service: item.service.instance}'
							v-on='on'
						>
							<v-icon small>
								mdi-delete
							</v-icon>
							{{ $t('table.actions.delete') }}
						</v-btn>
					</template>
					<v-card v-if='deleteInstance !== null'>
						<v-card-title>{{ $t('config.daemon.messagings.websocket.interface.modal.title') }}</v-card-title>
						<v-card-text>{{ $t('config.daemon.messagings.websocket.interface.modal.prompt', {instance: deleteInstance.messaging}) }}</v-card-text>
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
								@click='deleteInstance = null'
							>
								{{ $t('forms.cancel') }}
							</v-btn>
						</v-card-actions>
					</v-card>
				</v-dialog>
			</template>
		</v-data-table>
	</div>
</template>

<script lang='ts'>
import {Component, Vue, Watch} from 'vue-property-decorator';

import {extendedErrorToast} from '@/helpers/errorToast';
import {versionHigherEqual} from '@/helpers/versionChecker';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {WsInterface, ModalInstance, IWsService, WsMessaging} from '@/interfaces/messagingInterfaces';
import {DataTableHeader} from 'vuetify';
import {mapGetters} from 'vuex';

@Component({
	computed: {
		...mapGetters({
			daemonVersion: 'daemonClient/getVersion',
		}),
	},
})

/**
 * Websocket interface list card for normal user
 */
export default class WebsocketInterfaceList extends Vue {
	/**
	 * @constant {ModalInstance} componentNames Websocket messaging and service component names
	 */
	private componentNames: ModalInstance = {
		messaging: 'iqrf::WebsocketMessaging',
		service: 'shape::WebsocketCppService',
	};

	/**
	 * @var {ModalInstance|null} deleteInstance Websocket interface instance used in remove modal
	 */
	private deleteInstance: ModalInstance|null = null;

	/**
	 * @var {Array<DataTableHeader>} header Data table header
	 */
	private header: Array<DataTableHeader> = [
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
			value: 'actions',
			text: this.$t('table.actions.title').toString(),
			sortable: false,
			filterable: false,
			align: 'end',
		},
	];

	/**
	 * @var {Array<WsInterface>} instances Array of websocket interface instances
	 */
	private instances: Array<WsInterface> = [];

	/**
	 * @var {boolean} deleteModal Delete modal visibility
	 */
	get deleteModal(): boolean {
		return this.deleteInstance !== null;
	}

	/**
	 * Daemon version computed property watcher to re-render elements dependent on version
	 */
	@Watch('daemonVersion')
	private updateTable(): void {
		if (versionHigherEqual('2.3.0')) {
			this.header.splice(4, 0, {
				value: 'tlsEnabled',
				text: this.$t('config.daemon.messagings.websocket.form.tlsEnabled').toString(),
				filterable: false,
			});
		}
	}

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		this.updateTable();
		this.$store.commit('spinner/SHOW');
		this.getConfig();
	}

	/**
	 * Retrieves instances of Websocket daemon components
	 */
	private getConfig(): Promise<void> {
		return Promise.all([
			DaemonConfigurationService.getComponent(this.componentNames.messaging),
			DaemonConfigurationService.getComponent(this.componentNames.service),
		])
			.then((responses: Array<AxiosResponse>) => {
				const messagings = responses[0].data.instances;
				const services = responses[1].data.instances;
				const instances: Array<WsInterface> = [];
				for (const messaging of messagings) {
					if (messaging.RequiredInterfaces === undefined ||
							messaging.RequiredInterfaces === [] ||
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
							instanceMessaging: messaging.instance,
							instanceService: service.instance,
							acceptAsyncMsg: messaging.acceptAsyncMsg,
							port: service.WebsocketPort,
							acceptOnlyLocalhost: service.acceptOnlyLocalhost,
						});
					}
				}
				this.instances = instances;
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.messagings.websocket.interface.messages.listFailed'));
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
		this.$store.commit('spinner/SHOW');
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
			.catch((error: AxiosError) => extendedErrorToast(
				error,
				'config.daemon.messagings.websocket.interface.messages.updateFailed',
				{interface: settings.instance}
			));
	}

	/**
	 * Updates accepting asynchronous messages setting of Websocket messaging component instance
	 * @param {WsMessaging} instance Websocket messaging instance
	 * @param {boolean} setting new setting
	 */
	private changeAcceptAsyncMsg(instance: WsMessaging, setting: boolean): void {
		if (instance.acceptAsyncMsg === setting) {
			return;
		}
		this.$store.commit('spinner/SHOW');
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
			.catch((error: AxiosError) => extendedErrorToast(
				error,
				'config.daemon.messagings.websocket.interface.messages.updateFailed',
				{interface: instance.instance}
			));
	}

	/**
	 * Removes an existing instance of Websocket interface component
	 */
	private removeInterface(): void {
		if (this.deleteInstance === null) {
			return;
		}
		this.$store.commit('spinner/SHOW');
		Promise.all([
			DaemonConfigurationService.deleteInstance(this.componentNames.messaging, this.deleteInstance.messaging),
			DaemonConfigurationService.deleteInstance(this.componentNames.service, this.deleteInstance.service),
		])
			.then(() => {
				this.getConfig().then(() => {
					this.$toast.success(
						this.$t('config.daemon.messagings.websocket.interface.messages.deleteSuccess', {instance: this.deleteInstance?.messaging})
							.toString()
					);
				});
				this.deleteInstance = null;
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(
					error,
					'config.daemon.messagings.websocket.interface.messages.deleteFailed',
					{interface: this.deleteInstance!.messaging}
				);
				this.deleteInstance = null;
			});
	}
}
</script>
