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
			{{ $t('config.daemon.messagings.mq.title') }}
		</h1>
		<v-card>
			<v-card-text>
				<v-data-table
					:headers='headers'
					:items='instances'
					:no-data-text='$t("table.messages.noRecords")'
				>
					<template #top>
						<v-toolbar dense flat>
							<v-spacer />
							<v-btn
								color='success'
								to='/config/daemon/messagings/mq/add'
								small
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
							<template #activator='{on, attrs}'>
								<v-btn
									:color='item.acceptAsyncMsg ? "success" : "error"'
									small
									v-bind='attrs'
									v-on='on'
								>
									{{ $t(`states.${item.acceptAsyncMsg ? "enabled": "disabled"}`) }}
									<v-icon>mdi-menu-down</v-icon>
								</v-btn>
							</template>
							<v-list>
								<v-list-item @click='changeAcceptAsyncMsg(item, true)'>
									{{ $t('states.enabled') }}
								</v-list-item>
								<v-list-item @click='changeAcceptAsyncMsg(item, false)'>
									{{ $t('states.disabled') }}
								</v-list-item>
							</v-list>
						</v-menu>
					</template>
					<template #[`item.actions`]='{item}'>
						<v-btn
							color='info'
							:to='"/config/daemon/messagings/mq/edit/" + item.instance'
							small
						>
							<v-icon small>
								mdi-pencil
							</v-icon>
							{{ $t('table.actions.edit') }}
						</v-btn>
						<v-dialog
							v-model='deleteModal'
							width='50%'
							persistent
							no-click-animation
						>
							<template #activator='{on, attrs}'>
								<v-btn
									color='error'
									small
									v-bind='attrs'
									v-on='on'
									@click='confirmDelete(item)'
								>
									<v-icon small>
										mdi-delete
									</v-icon>
									{{ $t('table.actions.delete') }}
								</v-btn>
							</template>
							<v-card>
								<v-card-title class='text-h5 error'>
									{{ $t('config.daemon.messagings.mq.modal.title') }}
								</v-card-title>
								<v-card-text>
									{{ $t('config.daemon.messagings.mq.modal.prompt', {instance: deleteInstance}) }}
								</v-card-text>
								<v-card-actions>
									<v-spacer />
									<v-btn
										color='error'
										@click='performDelete'
									>
										{{ $t('forms.delete') }}
									</v-btn> <v-btn
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
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';

import {extendedErrorToast} from '@/helpers/errorToast';
import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {DataTableHeader} from 'vuetify';
import {IMqInstance} from '@/interfaces/messagingInterfaces';

@Component({
	metaInfo: {
		title: 'config.daemon.messagings.mq.title',
	},
})

/**
 * List of Daemon MQ messaging component instances
 */
export default class MqMessagingTable extends Vue {
	/**
	 * @constant {string} componentName MQ messaging component name
	 */
	private componentName = 'iqrf::MqMessaging';

	/**
	 * @var {string} deleteInstance MQ messaging instance name used in remove modal
	 */
	private deleteInstance = '';

	/**
	 * @constant {Array<DataTableHeader>} headers Vuetify data table headers
	 */
	private headers: Array<DataTableHeader> = [
		{
			value: 'instance',
			text: this.$t('forms.fields.instanceName').toString(),
		},
		{
			value: 'LocalMqName',
			text: this.$t('config.daemon.messagings.mq.form.LocalMqName').toString(),
		},
		{
			value: 'RemoteMqName',
			text: this.$t('config.daemon.messagings.mq.form.RemoteMqName').toString(),
		},
		{
			value: 'acceptAsyncMsg',
			text: this.$t('config.daemon.messagings.acceptAsyncMsg').toString(),
			filterable: false,
		},
		{
			value: 'actions',
			text: this.$t('table.actions.title').toString(),
			filterable: false,
			sortable: false,
		},
	];

	/**
	 * @var {Array<IMqInstance>} instances Array of MQ messaging component instances
	 */
	private instances: Array<IMqInstance> = [];

	/**
	 * @var {boolean} deleteModal Delete modal visibility
	 */
	get deleteModal(): boolean {
		return this.deleteInstance !== '';
	}

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		this.$store.commit('spinner/SHOW');
		this.getInstances();
	}

	/**
	 * Assigns name of MQ messaging instance selected to remove to the remove modal
	 * @param {IMqInstance} instance MQ messaging instance
	 */
	private confirmDelete(instance: IMqInstance): void {
		this.deleteInstance = instance.instance;
	}

	/**
	 * Updates configuration of MQ messaging component instance
	 * @param {IMqInstance} instance MQ messaging instance
	 * @param {boolean} acceptAsyncMsg Message accepting policy setting
	 */
	private changeAcceptAsyncMsg(instance: IMqInstance, acceptAsyncMsg: boolean): void {
		if (instance.acceptAsyncMsg === acceptAsyncMsg) {
			return;
		}
		const settings = {
			...instance
		};
		settings.acceptAsyncMsg = acceptAsyncMsg;
		this.$store.commit('spinner/SHOW');
		DaemonConfigurationService.updateInstance(this.componentName, settings.instance, settings)
			.then(() => {
				this.getInstances().then(() => {
					this.$toast.success(
						this.$t('config.daemon.messagings.mq.messages.editSuccess', {instance: settings.instance})
							.toString()
					);
				});
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.messagings.mq.messages.editFailed', {instance: settings.instance}));
	}

	/**
	 * Retrieves instances of MQ messaging component
	 * @returns {Promise<void>} Empty promise for request chaining
	 */
	private getInstances(): Promise<void> {
		return DaemonConfigurationService.getComponent(this.componentName)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.instances = response.data.instances;
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.messagings.mq.messages.listFailed'));
	}

	/**
	 * Removes instance of MQ messaging component
	 */
	private performDelete(): void {
		this.$store.commit('spinner/SHOW');
		const instance = this.deleteInstance;
		this.deleteInstance = '';
		DaemonConfigurationService.deleteInstance(this.componentName, instance)
			.then(() => {
				this.getInstances().then(() => {
					this.$toast.success(
						this.$t('config.daemon.messagings.mq.messages.deleteSuccess', {instance: instance})
							.toString()
					);
				});
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.messagings.mq.messages.deleteFailed', {instance: instance}));
	}
}
</script>
