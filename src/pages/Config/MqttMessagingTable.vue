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
			{{ $t('config.daemon.messagings.mqtt.title') }}
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
								color='success'
								to='/config/daemon/messagings/mqtt/add'
								small
							>
								<v-icon small>
									mdi-plus
								</v-icon>
								{{ $t('table.actions.add') }}
							</v-btn>
						</v-toolbar>
					</template>
					<template #[`item.EnabledSSL`]='{item}'>
						<v-menu>
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
							<v-list>
								<v-list-item @click='changeEnabledSSL(item, true)'>
									{{ $t('states.enabled') }}
								</v-list-item>
								<v-list-item @click='changeEnabledSSL(item, false)'>
									{{ $t('states.disabled') }}
								</v-list-item>
							</v-list>
						</v-menu>
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
									{{ $t(`states.${item.acceptAsyncMsg ? "enabled" : "disabled"}`) }}
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
							:to='"/config/daemon/messagings/mqtt/edit/" + item.instance'
							small
						>
							<v-icon small>
								mdi-pencil
							</v-icon>
							{{ $t('table.actions.edit') }}
						</v-btn> <MessagingDeleteDialog
							:messaging-type='MessagingTypes.MQTT'
							:instance='item.instance'
							@deleted='getInstances'
						/>
					</template>
				</v-data-table>
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import MessagingDeleteDialog from '@/components/Config/Messagings/MessagingDeleteDialog.vue';

import {extendedErrorToast} from '@/helpers/errorToast';
import {MessagingTypes} from '@/enums/Config/Messagings';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {DataTableHeader} from 'vuetify';
import {IMqttInstance} from '@/interfaces/Config/Messaging';

@Component({
	components: {
		MessagingDeleteDialog,
	},
	data: () => ({
		MessagingTypes,
	}),
	metaInfo: {
		title: 'config.daemon.messagings.mqtt.title'
	}
})

/**
 * List of Daemon MQTT messaging component instances
 */
export default class MqttMessagingTable extends Vue {
	/**
	 * @var {boolean} loading Loading visibility
	 */
	private loading = false;

	/**
	 * @constant {string} componentName MQTT messaging component name
	 */
	private componentName = 'iqrf::MqttMessaging';

	/**
	 * @constant {Array<DataTableHeader>} headers Vuetify data table headers
	 */
	private headers: Array<DataTableHeader> = [
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
			value: 'TopicRequest',
			text: this.$t('forms.fields.requestTopic').toString(),
		},
		{
			value: 'TopicResponse',
			text: this.$t('forms.fields.responseTopic').toString(),
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
	];

	/**
	 * @var {Array<IMqttInstance>} instances Array of MQTT messaging component instances
	 */
	private instances: Array<IMqttInstance> = [];

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
		const settings = {
			...instance,
			...newSettings,
		};
		this.$store.commit('spinner/SHOW');
		DaemonConfigurationService.updateInstance(this.componentName, settings.instance, settings)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.getInstances().then(() => {
					this.$toast.success(
						this.$t('config.daemon.messagings.mqtt.messages.editSuccess', {instance: settings.instance})
							.toString()
					);
				});
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.messagings.mqtt.messages.editFailed', {instance: settings.instance}));
	}

	/**
	 * Retrieves instances of MQTT messaging component
	 * @returns {Promise<void>} Empty promise for request chaining
	 */
	private getInstances(): Promise<void> {
		this.loading = true;
		return DaemonConfigurationService.getComponent(this.componentName)
			.then((response: AxiosResponse) => {
				this.instances = response.data.instances;
				this.loading = false;
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.messagings.mqtt.messages.listFailed'));
	}
}
</script>
