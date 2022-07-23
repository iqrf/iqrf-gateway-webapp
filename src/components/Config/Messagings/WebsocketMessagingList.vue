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
			{{ $t('config.daemon.messagings.websocket.messaging.title') }}
		</h1>
		<v-card>
			<v-card-text>
				<v-data-table
					:loading='loading'
					:headers='header'
					:items='instances'
				>
					<template #top>
						<v-toolbar dense flat>
							<v-spacer />
							<v-btn
								color='success'
								small
								to='/config/daemon/messagings/websocket/add-messaging'
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
								<v-list-item @click='changeAccept(item, true)'>
									{{ $t('states.enabled') }}
								</v-list-item>
								<v-list-item @click='changeAccept(item, false)'>
									{{ $t('states.disabled') }}
								</v-list-item>
							</v-list>
						</v-menu>
					</template>
					<template #[`item.RequiredInterfaces`]='{item}'>
						{{ item.RequiredInterfaces.map(wsInterface => wsInterface.target.instance).join(', ') }}
					</template>
					<template #[`item.actions`]='{item}'>
						<v-btn
							color='info'
							small
							:to='"/config/daemon/messagings/websocket/edit-messaging/" + item.instance'
						>
							<v-icon small>
								mdi-pencil
							</v-icon>
							{{ $t('table.actions.edit') }}
						</v-btn> <WebsocketDeleteDialog
							:component-type='WebsocketTypes.MESSAGING'
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
import {Component, Vue} from 'vue-property-decorator';
import WebsocketDeleteDialog from './WebsocketDeleteDialog.vue';

import {extendedErrorToast} from '@/helpers/errorToast';
import {WebsocketTypes} from '@/enums/Config/Messagings';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {DataTableHeader} from 'vuetify';
import {IWsMessaging} from '@/interfaces/Config/Messaging';

/**
 * Websocket messaging list card for normal user
 */
@Component({
	components: {
		WebsocketDeleteDialog,
	},
	data: () => ({
		WebsocketTypes,
	}),
})
export default class WebsocketMessagingList extends Vue {
	/**
	 * @var {boolean} loading Loading visibility
	 */
	private loading = false;

	/**
	 * @constant {string} componentName Websocket messaging component name
	 */
	private componentName = 'iqrf::WebsocketMessaging';

	/**
	 * @var {Array<DataTableHeader>} header Data table header
	 */
	private header: Array<DataTableHeader> = [
		{
			value: 'instance',
			text: this.$t('forms.fields.instanceName').toString(),
		},
		{
			value: 'acceptAsyncMsg',
			text: this.$t('config.daemon.messagings.acceptAsyncMsg').toString(),
			filterable: false,
		},
		{
			value: 'RequiredInterfaces',
			text: this.$t('config.daemon.messagings.websocket.form.requiredInterface.instance').toString(),
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
	 * @var {Array<IWsMessaging>} instances Array of Websocket messaging instances
	 */
	private instances: Array<IWsMessaging> = [];

	/**
	 * Vue lifecycle hook created
	 */
	mounted(): void {
		this.getConfig();
	}

	/**
	 * Retrieves instances of Websocket messaging component
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
				extendedErrorToast(error, 'config.daemon.messagings.websocket.messaging.messages.listFailed');
			});
	}

	/**
	 * Updates accepting asynchronous messages setting of Websocket messaging component instance
	 * @param {IWsMessaging} instance Websocket messaging instance
	 * @param {boolean} setting new setting
	 */
	private changeAccept(instance: IWsMessaging, setting: boolean): void {
		if (instance.acceptAsyncMsg !== setting) {
			const conf = {
				...instance,
			};
			conf.acceptAsyncMsg = setting;
			this.loading = true;
			DaemonConfigurationService.updateInstance(this.componentName, instance.instance, conf)
				.then(() => {
					this.getConfig().then(() => {
						this.$toast.success(
							this.$t('config.daemon.messagings.websocket.messaging.messages.updateSuccess', {messaging: instance.instance})
								.toString()
						);
					});
				})
				.catch((error: AxiosError) => {
					this.loading = false;
					extendedErrorToast(error, 'config.daemon.messagings.websocket.messaging.messages.updateFailed', {messaging: instance.instance});
				});
		}
	}
}
</script>
