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
			{{ $t('config.daemon.messagings.udp.title') }}
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
								to='/config/daemon/messagings/udp/add'
								small
							>
								<v-icon small>
									mdi-plus
								</v-icon>
								{{ $t('table.actions.add') }}
							</v-btn>
						</v-toolbar>
					</template>
					<template #[`item.actions`]='{item}'>
						<v-btn
							color='info'
							:to='"/config/daemon/messagings/udp/edit/" + item.instance'
							small
						>
							<v-icon small>
								mdi-pencil
							</v-icon>
							{{ $t('table.actions.edit') }}
						</v-btn> <MessagingDeleteDialog
							:messaging-type='MessagingTypes.UDP'
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
import {IUdpInstance} from '@/interfaces/Config/Messaging';

@Component({
	components: {
		MessagingDeleteDialog,
	},
	data: () => ({
		MessagingTypes,
	}),
	metaInfo: {
		title: 'config.daemon.messagings.udp.title',
	},
})

/**
 * List of Daemon UDP messaging component instances
 */
export default class UdpMessagingTable extends Vue {
	/**
	 * @var {boolean} loading Loading visibility
	 */
	private loading = false;

	/**
	 * @constant {string} componentName UDP messaging component name
	 */
	private componentName = 'iqrf::UdpMessaging';

	/**
	 * @constant {Array<DataTableHeader>} headers Vuetify data table headers
	 */
	private headers: Array<DataTableHeader> = [
		{
			value: 'instance',
			text: this.$t('forms.fields.instanceName').toString(),
		},
		{
			value: 'RemotePort',
			text: this.$t('config.daemon.messagings.udp.form.RemotePort').toString(),
		},
		{
			value: 'LocalPort',
			text: this.$t('config.daemon.messagings.udp.form.LocalPort').toString(),
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
	 * @var {Array<IUdpInstance>} instances Array of UDP messaging component instances
	 */
	private instances: Array<IUdpInstance> = [];

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		this.getInstances();
	}

	/**
	 * Retrieves instances of UDP messaging component
	 * @returns {Promise<void>} Empty promise for response chaining
	 */
	private getInstances(): Promise<void> {
		this.loading = true;
		return DaemonConfigurationService.getComponent(this.componentName)
			.then((response: AxiosResponse) => {
				this.instances = response.data.instances;
				this.loading = false;
			})
			.catch((error: AxiosError) => {
				this.loading = false;
				extendedErrorToast(error, 'config.daemon.messagings.udp.messages.listFailed');
			});
	}
}
</script>
