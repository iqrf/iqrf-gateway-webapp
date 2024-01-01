<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
			{{ $t('config.daemon.messagings.mqtt.title') }}
		</h1>
		<v-card>
			<v-card-text>
				<v-data-table
					:loading='loading'
					:headers='headers'
					:items='instances'
					:no-data-text='$t("table.messages.noRecords")'
					show-expand
					single-expand
					:expanded.sync='expanded'
				>
					<template #top>
						<v-toolbar dense flat>
							<v-spacer />
							<v-btn
								class='mr-1'
								color='success'
								small
								to='/config/daemon/messagings/mqtt/add'
							>
								<v-icon small>
									mdi-plus
								</v-icon>
							</v-btn>
							<v-btn
								color='primary'
								small
								@click='getInstances'
							>
								<v-icon small>
									mdi-refresh
								</v-icon>
							</v-btn>
						</v-toolbar>
					</template>
					<template #[`item.EnabledSSL`]='{item}'>
						<v-menu offset-y>
							<template #activator='{on, attrs}'>
								<v-btn
									:color='item.EnabledSSL ? "success" : "error"'
									small
									v-bind='attrs'
									v-on='on'
								>
									{{ $t(`states.${item.EnabledSSL ? "enabled" : "disabled"}`) }}
									<v-icon>mdi-menu-down</v-icon>
								</v-btn>
							</template>
							<v-list dense>
								<v-list-item
									dense
									@click='changeEnabledSSL(item, true)'
								>
									{{ $t('states.enabled') }}
								</v-list-item>
								<v-list-item
									dense
									@click='changeEnabledSSL(item, false)'
								>
									{{ $t('states.disabled') }}
								</v-list-item>
							</v-list>
						</v-menu>
					</template>
					<template #[`item.acceptAsyncMsg`]='{item}'>
						<v-menu offset-y>
							<template #activator='{on, attrs}'>
								<v-btn
									:color='item.acceptAsyncMsg ? "success" : "error"'
									small
									v-bind='attrs'
									v-on='on'
								>
									{{ $t(`states.${item.acceptAsyncMsg ? "enabled" : "disabled"}`) }}
									<v-icon>mdi-menu-down</v-icon>
								</v-btn>
							</template>
							<v-list dense>
								<v-list-item
									dense
									@click='changeAcceptAsyncMsg(item, true)'
								>
									{{ $t('states.enabled') }}
								</v-list-item>
								<v-list-item
									dense
									@click='changeAcceptAsyncMsg(item, false)'
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
							:to='"/config/daemon/messagings/mqtt/edit/" + item.instance'
						>
							<v-icon small>
								mdi-pencil
							</v-icon>
							{{ $t('table.actions.edit') }}
						</v-btn>
						<v-btn
							color='error'
							small
							@click='messagingDeleteModel = item.instance'
						>
							<v-icon small>
								mdi-delete
							</v-icon>
							{{ $t('table.actions.delete') }}
						</v-btn>
					</template>
					<template #expanded-item='{headers, item}'>
						<td :colspan='headers.length'>
							<v-container fluid>
								<v-row>
									<v-col>
										<div class='datatable-expansion-table'>
											<table>
												<caption>
													<b>{{ $t('config.daemon.messagings.mqtt.details') }}</b>
												</caption>
												<tr>
													<th>{{ $t('forms.fields.requestTopic') }}</th>
													<td>
														{{ item.TopicRequest }}
													</td>
												</tr>
												<tr>
													<th>{{ $t('forms.fields.responseTopic') }}</th>
													<td>
														{{ item.TopicResponse }}
													</td>
												</tr>
												<tr>
													<th>{{ $t('config.daemon.messagings.mqtt.form.QoS') }}</th>
													<td>
														{{ item.Qos }}
													</td>
												</tr>
												<tr>
													<th>{{ $t('config.daemon.messagings.mqtt.form.Persistence') }}</th>
													<td>
														{{ $t(`config.daemon.messagings.mqtt.form.Persistences.${item.Persistence}`) }}
													</td>
												</tr>
											</table>
										</div>
									</v-col>
								</v-row>
							</v-container>
						</td>
					</template>
				</v-data-table>
			</v-card-text>
		</v-card>
		<MessagingDeleteModal
			v-model='messagingDeleteModel'
			:messaging-type='MessagingTypes.MQTT'
			@deleted='getInstances'
		/>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import MessagingDeleteModal from '@/components/Config/Messagings/MessagingDeleteModal.vue';

import {extendedErrorToast} from '@/helpers/errorToast';
import {MessagingTypes} from '@/enums/Config/Messagings';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {DataTableHeader} from 'vuetify';
import {IMqttInstance} from '@/interfaces/Config/Messaging';


@Component({
	components: {
		MessagingDeleteModal,
	},
	data: () => ({
		MessagingTypes,
	}),
	metaInfo: {
		title: 'config.daemon.messagings.mqtt.title',
	},
})

/**
 * List of Daemon MQTT messaging component instances
 */
export default class MqttMessagingTable extends Vue {
	/**
	 * @constant {string} componentName MQTT messaging component name
	 */
	private componentName = 'iqrf::MqttMessaging';

	/**
	 * @var {boolean} loading Indicates that request is in progress
	 */
	private loading = false;

	/**
	 * @var {Array<IMqttInstance>} instances Array of MQTT messaging component instances
	 */
	private instances: Array<IMqttInstance> = [];

	/**
	 * @var {Array<IMqttInstance>} expanded Array of expanded MQTT messaging component instances
	 */
	private expanded: Array<IMqttInstance> = [];

	/**
	 * @var {string|null} messagingDeleteModel Messaging to delete
	 */
	private messagingDeleteModel: string|null = null;

	/**
	 * @constant {Array<DataTableHeader>} headers Data table headers
	 */
	private readonly headers: Array<DataTableHeader> = [
		{
			value: 'instance',
			text: this.$t('forms.fields.instanceName').toString(),
		},
		{
			value: 'BrokerAddr',
			text: this.$t('config.daemon.messagings.mqtt.form.BrokerAddr').toString(),
		},
		{
			value: 'ClientId',
			text: this.$t('forms.fields.clientId').toString(),
		},
		{
			value: 'EnabledSSL',
			text: this.$t('config.daemon.messagings.mqtt.form.EnabledSSL').toString(),
			filterable: false,
		},
		{
			value: 'acceptAsyncMsg',
			text: this.$t('config.daemon.messagings.acceptAsyncMsg').toString(),
			filterable: false,
		},
		{
			value: 'actions',
			text: this.$t('table.actions.title').toString(),
			sortable: false,
			filterable: false,
			align: 'end',
		},
		{
			value: 'data-table-expand',
			text: '',
		},
	];

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		this.getInstances();
	}

	/**
	 * Updates message accepting configuration of MQTT messaging component instance
	 * @param {IMqttInstance} instance MQTT messaging instance
	 * @param {boolean} acceptAsyncMsg Message accepting policy setting
	 */
	private changeAcceptAsyncMsg(instance: IMqttInstance, acceptAsyncMsg: boolean): void {
		if (instance.acceptAsyncMsg === acceptAsyncMsg) {
			return;
		}
		this.edit(instance, {acceptAsyncMsg: acceptAsyncMsg});
	}

	/**
	 * Updates SSL configuration of MQTT messaging component instance
	 * @param {IMqttInstance} instance MQTT messaging instance
	 * @param {boolean} enabledSsl SSL setting
	 */
	private changeEnabledSSL(instance: IMqttInstance, enabledSsl: boolean) : void{
		if (instance.EnabledSSL === enabledSsl) {
			return;
		}
		this.edit(instance, {EnabledSSL: enabledSsl});
	}

	/**
	 * Saves changes in MQTT messaging instance configuration
	 * @param {IMqttInstance} instance MQTT messaging instance
	 * @param {Record<string, boolean>} newSettings Settings to update instance with
	 */
	private edit(instance: IMqttInstance, newSettings: Record<string, boolean>): void {
		this.loading = true;
		const settings = {
			...instance,
			...newSettings,
		};
		DaemonConfigurationService.updateInstance(this.componentName, settings.instance, settings)
			.then(() => {
				this.getInstances().then(() => {
					this.$toast.success(
						this.$t('config.daemon.messagings.mqtt.messages.editSuccess', {instance: settings.instance})
							.toString()
					);
				});
			})
			.catch((error: AxiosError) => {
				this.loading = false;
				extendedErrorToast(error, 'config.daemon.messagings.mqtt.messages.editFailed', {instance: settings.instance});
			});
	}

	/**
	 * Retrieves instances of MQTT messaging component
	 * @returns {Promise<void>} Empty promise for request chaining
	 */
	private getInstances(): Promise<void> {
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
				extendedErrorToast(error, 'config.daemon.messagings.mqtt.messages.listFailed');
			});
	}
}
</script>
