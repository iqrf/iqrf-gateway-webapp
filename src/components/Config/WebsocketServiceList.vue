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
			{{ $t('config.daemon.messagings.websocket.service.title') }}
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
						to='/config/daemon/messagings/websocket/add-service'
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
						<v-list-item @click='changeAccept(item, true)'>
							{{ $t('states.enabled') }}
						</v-list-item>
						<v-list-item @click='changeAccept(item, false)'>
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
						<v-list-item @click='changeTLS(item, true)'>
							{{ $t('states.enabled') }}
						</v-list-item>
						<v-list-item @click='changeTLS(item, false)'>
							{{ $t('states.disabled') }}
						</v-list-item>
					</v-list>
				</v-menu>
			</template>
			<template #[`item.actions`]='{item}'>
				<v-btn
					color='info'
					small
					:to='"/config/daemon/messagings/websocket/edit-service/" + item.instance'
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
							@click='deleteService = item.instance'
							v-on='on'
						>
							<v-icon small>
								mdi-delete
							</v-icon>
							{{ $t('table.actions.delete') }}
						</v-btn>
					</template>
					<v-card>
						<v-card-title>{{ $t('config.daemon.messagings.websocket.service.modal.title') }}</v-card-title>
						<v-card-text>{{ $t('config.daemon.messagings.websocket.service.modal.prompt', {service: deleteService}) }}</v-card-text>
						<v-card-actions>
							<v-btn
								color='error'
								@click='removeService'
							>
								{{ $t('forms.delete') }}
							</v-btn>
							<v-spacer />
							<v-btn
								color='secondary'
								@click='deleteService = null'
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
import {mapGetters} from 'vuex';
import {versionHigherEqual} from '@/helpers/versionChecker';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {IWsService} from '@/interfaces/messagingInterfaces';
import {DataTableHeader} from 'vuetify';

@Component({
	computed: {
		...mapGetters({
			daemonVersion: 'daemonClient/getVersion',
		}),
	},
})

/**
 * Websocket service list card for normal user
 */
export default class WebsocketServiceList extends Vue {
	/**
	 * @constant {string} componentName Websocket service component name
	 */
	private componentName = 'shape::WebsocketCppService';

	/**
	 * @var {string|null} deleteService Websocket service instance used in remove modal
	 */
	private deleteService: string|null = null;

	/**
	 * @var {Array<DataTableHeader>} header Data table header
	 */
	private header: Array<DataTableHeader> = [
		{
			value: 'instance',
			text: this.$t('forms.fields.instanceName').toString(),
		},
		{
			value: 'WebsocketPort',
			text: this.$t('config.daemon.messagings.websocket.form.WebsocketPort').toString(),
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
	 * @var {Array<WsService>} instances Array of Websocket service instances
	 */
	private instances: Array<IWsService> = [];

	/**
	 * @var {boolean} deleteModal Delete modal visibility
	 */
	get deleteModal(): boolean {
		return this.deleteService !== null;
	}

	/**
	 * Daemon version computed property watcher to re-render elements dependent on version
	 */
	@Watch('daemonVersion')
	private updateTable(): void {
		if (versionHigherEqual('2.3.0')) {
			this.header.splice(3, 0, {
				value: 'tlsEnabled',
				text: this.$t('config.daemon.messagings.websocket.form.tlsEnabled').toString(),
				filterable: false,
			});
		}
	}

	/**
	 * Vue lifecycle hook created
	 */
	mounted(): void {
		this.updateTable();
		this.getConfig();
	}

	/**
	 * Retrieves instances of Websocket service component
	 */
	private getConfig(): Promise<void> {
		this.$store.commit('spinner/SHOW');
		return DaemonConfigurationService.getComponent(this.componentName)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.instances = response.data.instances;
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.daemon.messagings.websocket.service.messages.getFailed');
			});
	}

	/**
	 * Updates accepted message source of Websocket service component instance
	 * @param {IWsService} service Websocket service instance
	 * @param {boolean} acceptOnlyLocalhost New setting
	 */
	private changeAccept(service: IWsService, acceptOnlyLocalhost: boolean): void {
		if (service.acceptOnlyLocalhost === acceptOnlyLocalhost) {
			return;
		}
		this.edit(service, {acceptOnlyLocalhost: acceptOnlyLocalhost});
	}

	/**
	 * Updates TLS enabled setting of Websocket service component instance
	 * @param {IWsService} service Websocket service instance
	 * @param {boolean} tlsEnabled New setting
	 */
	private changeTLS(service: IWsService, tlsEnabled: boolean): void {
		if (service.tlsEnabled === tlsEnabled) {
			return;
		}
		this.edit(service, {tlsEnabled: tlsEnabled});
	}

	/**
	 * Saves changes in Websocket service instance configuration
	 * @param {IWsService} service Websocket service instance
	 * @param {Record<string, boolean>} newSettings Settings to update instance with
	 */
	private edit(service: IWsService, newSettings: Record<string, boolean>): void {
		this.$store.commit('spinner/SHOW');
		const settings = {
			...service,
			...newSettings,
		};
		DaemonConfigurationService.updateInstance(this.componentName, settings.instance, settings)
			.then(() => {
				this.getConfig().then(() => {
					this.$toast.success(
						this.$t('config.daemon.messagings.websocket.service.messages.updateSuccess', {service: settings.instance})
							.toString()
					);
				});
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.daemon.messagings.websocket.service.messages.updateFailed', {service: settings.instance});
			});
	}

	/**
	 * Removes an existing instance of Websocket service component
	 */
	private removeService(): void {
		if (this.deleteService === null) {
			return;
		}
		this.$store.commit('spinner/SHOW');
		const service = this.deleteService;
		this.deleteService = null;
		DaemonConfigurationService.deleteInstance(this.componentName, service)
			.then(() => {
				this.getConfig().then(() => {
					this.$toast.success(
						this.$t('config.daemon.messagings.websocket.service.messages.deleteSuccess', {service: service})
							.toString()
					);
				});
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.daemon.messagings.websocket.service.messages.deleteFailed', {service: service});
			});
	}
}
</script>
