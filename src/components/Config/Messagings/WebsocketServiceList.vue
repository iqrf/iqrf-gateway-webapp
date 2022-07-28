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
		<v-card>
			<v-card-text>
				<v-data-table
					:loading='loading'
					:headers='headers'
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
						<v-menu offset-y>
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
							<v-list dense>
								<v-list-item
									dense
									@click='changeAccept(item, true)'
								>
									{{ $t('states.enabled') }}
								</v-list-item>
								<v-list-item
									dense
									@click='changeAccept(item, false)'
								>
									{{ $t('states.disabled') }}
								</v-list-item>
							</v-list>
						</v-menu>
					</template>
					<template #[`item.tlsEnabled`]='{item}'>
						<v-menu offset-y>
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
							<v-list dense>
								<v-list-item
									dense
									@click='changeTLS(item, true)'
								>
									{{ $t('states.enabled') }}
								</v-list-item>
								<v-list-item
									dense
									@click='changeTLS(item, false)'
								>
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
						</v-btn> <WebsocketDeleteDialog
							:component-type='WebsocketTypes.SERVICE'
							:instance='item'
							@deleted='getConfig'
						/>
					</template>
				</v-data-table>
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {Component, Vue, Watch} from 'vue-property-decorator';
import WebsocketDeleteDialog from './WebsocketDeleteDialog.vue';

import {extendedErrorToast} from '@/helpers/errorToast';
import {mapGetters} from 'vuex';
import {versionHigherEqual} from '@/helpers/versionChecker';
import {WebsocketTypes} from '@/enums/Config/Messagings';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {DataTableHeader} from 'vuetify';
import {IWsService} from '@/interfaces/Config/Messaging';

/**
 * Websocket service list card for normal user
 */
@Component({
	components: {
		WebsocketDeleteDialog,
	},
	computed: {
		...mapGetters({
			daemonVersion: 'daemonClient/getVersion',
		}),
	},
	data: () => ({
		WebsocketTypes,
	}),
})

export default class WebsocketServiceList extends Vue {
	/**
	 * @var {boolean} loading Loading visibility
	 */
	private loading = false;

	/**
	 * @constant {string} componentName Websocket service component name
	 */
	private componentName = 'shape::WebsocketCppService';

	/**
	 * @var {Array<DataTableHeader>} header Data table header
	 */
	private headers: Array<DataTableHeader> = [
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
	 * @var {Array<IWsService>} instances Array of Websocket service instances
	 */
	private instances: Array<IWsService> = [];

	/**
	 * Daemon version computed property watcher to re-render elements dependent on version
	 */
	@Watch('daemonVersion')
	private updateTable(): void {
		if (versionHigherEqual('2.3.0')) {
			this.headers.splice(3, 0, {
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
		this.loading = true;
		return DaemonConfigurationService.getComponent(this.componentName)
			.then((response: AxiosResponse) => {
				this.instances = response.data.instances;
				this.loading = false;
			})
			.catch((error: AxiosError) => {
				this.loading = false;
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
		this.loading = true;
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
				this.loading = false;
				extendedErrorToast(error, 'config.daemon.messagings.websocket.service.messages.updateFailed', {service: settings.instance});
			});
	}
}
</script>
