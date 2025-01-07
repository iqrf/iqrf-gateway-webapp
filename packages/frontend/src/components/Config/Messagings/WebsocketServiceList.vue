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
			{{ $t('config.daemon.messagings.websocket.service.title') }}
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
								to='/config/daemon/messagings/websocket/add-service'
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
							class='mr-1'
							color='info'
							small
							:to='"/config/daemon/messagings/websocket/edit-service/" + item.instance'
						>
							<v-icon small>
								mdi-pencil
							</v-icon>
							{{ $t('table.actions.edit') }}
						</v-btn>
						<v-btn
							color='error'
							small
							@click='serviceDeleteModel = item'
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
		<WebsocketDeleteModal
			v-model='serviceDeleteModel'
			:component-type='WebsocketTypes.SERVICE'
			@deleted='getConfig'
		/>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import WebsocketDeleteModal from '@/components/Config/Messagings/WebsocketDeleteModal.vue';

import {extendedErrorToast} from '@/helpers/errorToast';
import {WebsocketTypes} from '@/enums/Config/Messagings';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {DataTableHeader} from 'vuetify';
import {IWsService} from '@/interfaces/Config/Messaging';

@Component({
	components: {
		WebsocketDeleteModal
	},
	data: () => ({
		WebsocketTypes,
	}),
})

/**
 * Websocket service list card for normal user
 */
export default class WebsocketServiceList extends Vue {

	/**
	 * @var {boolean} loading Indicates that request is in progress
	 */
	private loading = false;

	/**
	 * @var {Array<WsService>} instances Array of Websocket service instances
	 */
	private instances: Array<IWsService> = [];

	/**
	 * @var {IWsService|null} serviceDeleteModel Service to delete
	 */
	private serviceDeleteModel: IWsService|null = null;

	/**
	 * @constant {string} componentName Websocket service component name
	 */
	private readonly componentName = 'shape::WebsocketCppService';

	/**
	 * @constant {Array<DataTableHeader>} headers Data table columns
	 */
	private readonly headers: Array<DataTableHeader> = [
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
		},
		{
			value: 'tlsEnabled',
			text: this.$t('config.daemon.messagings.websocket.form.tlsEnabled').toString(),
		},
		{
			value: 'actions',
			text: this.$t('table.actions.title').toString(),
			filterable: false,
			sortable: false,
			align: 'end',
		},
	];

	/**
	 * Loads component configuration
	 */
	mounted(): void {
		this.getConfig();
	}

	/**
	 * Retrieves instances of Websocket service component
	 */
	private getConfig(): Promise<void> {
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
