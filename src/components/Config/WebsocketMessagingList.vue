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
				</v-btn>
				<v-dialog v-model='deleteModal' width='50%'>
					<template #activator='{ on, attrs }'>
						<v-btn
							color='error'
							small
							v-bind='attrs'
							@click='deleteInstance = item'
							v-on='on'
						>
							<v-icon small>
								mdi-delete
							</v-icon>
							{{ $t('table.actions.delete') }}
						</v-btn>
					</template>
					<v-card v-if='deleteInstance !== null'>
						<v-card-title>{{ $t('config.daemon.messagings.websocket.messaging.modal.title') }}</v-card-title>
						<v-card-text>{{ $t('config.daemon.messagings.websocket.messaging.modal.prompt', {messaging: deleteInstance.instance}) }}</v-card-text>
						<v-card-actions>
							<v-btn
								color='error'
								@click='removeInstance'
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
import {Component, Vue} from 'vue-property-decorator';

import {extendedErrorToast} from '@/helpers/errorToast';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {WsMessaging} from '@/interfaces/messagingInterfaces';
import {DataTableHeader} from 'vuetify';

@Component({})

/**
 * Websocket messaging list card for normal user
 */
export default class WebsocketMessagingList extends Vue {
	/**
	 * @constant {string} componentName Websocket messaging component name
	 */
	private componentName = 'iqrf::WebsocketMessaging';

	/**
	 * @var {string|null} deleteInstance Websocket messaging instance used in remove modal
	 */
	private deleteInstance: WsMessaging|null = null;

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
	 * @var {Array<WsMessaging>} instances Array of Websocket messaging instances
	 */
	private instances: Array<WsMessaging> = [];

	/**
	 * @var {boolean} deleteModal Delete modal visibility
	 */
	get deleteModal(): boolean {
		return this.deleteInstance !== null;
	}

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
		this.$store.commit('spinner/SHOW');
		return DaemonConfigurationService.getComponent(this.componentName)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.instances = response.data.instances;
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.daemon.messagings.websocket.messaging.messages.listFailed');
			});
	}

	/**
	 * Updates accepting asynchronous messages setting of Websocket messaging component instance
	 * @param {WsMessaging} instance Websocket messaging instance
	 * @param {boolean} setting new setting
	 */
	private changeAccept(instance: WsMessaging, setting: boolean): void {
		if (instance.acceptAsyncMsg !== setting) {
			const conf = {
				...instance,
			};
			conf.acceptAsyncMsg = setting;
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
					extendedErrorToast(error, 'config.daemon.messagings.websocket.messaging.messages.updateFailed', {messaging: instance.instance});
				});
		}
	}

	/**
	 * Removes an existing instance of Websocket messaging component
	 */
	private removeInstance(): void {
		if (this.deleteInstance === null) {
			return;
		}
		this.$store.commit('spinner/SHOW');
		const instance = this.deleteInstance;
		this.deleteInstance = null;
		DaemonConfigurationService.deleteInstance(this.componentName, instance.instance)
			.then(() => {
				this.getConfig().then(() => {
					this.$toast.success(
						this.$t('config.daemon.messagings.websocket.messaging.messages.deleteSuccess', {messaging: instance})
							.toString()
					);
				});
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.daemon.messagings.websocket.messaging.messages.deleteFailed', {messaging: instance.instance});
			});
	}
}
</script>
