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
								to='/config/daemon/misc/tracer/add'
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
					<template #[`item.actions`]='{item}'>
						<v-btn
							class='mr-1'
							color='info'
							small
							:to='"/config/daemon/misc/tracer/edit/" + item.instance'
						>
							<v-icon small>
								mdi-pencil
							</v-icon>
							{{ $t('table.actions.edit') }}
						</v-btn>
						<v-btn
							color='error'
							small
							@click='instanceDeleteModel = item.instance'
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
		<TracerDeleteModal
			v-model='instanceDeleteModel'
			@deleted='getConfig'
		/>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import TracerDeleteModal from './TracerDeleteModal.vue';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosResponse} from 'axios';
import {DataTableHeader} from 'vuetify';

@Component({
	components: {
		TracerDeleteModal,
	},
})

/**
 * List of Daemon logging service component instances
 */
export default class TracerList extends Vue {
	/**
	 * @constant {string} componentName Logging service component name
	 */
	private readonly componentName = 'shape::TraceFileService';

	/**
	 * @var {boolean} loading Indicates that request is in progress
	 */
	private loading = false;

	/**
	 * @var {Array<unknown>} instances Array of logging service component instances
	 */
	private instances: Array<unknown> = [];

	/**
	 * @var {string|null} instanceDeleteModel Instance to delete
	 */
	private instanceDeleteModel: string|null = null;

	/**
	 * @constant {Array<DataTableHeader>} headers Data table columns
	 */
	private readonly headers: Array<DataTableHeader> = [
		{
			value: 'instance',
			text: this.$t('forms.fields.instanceName').toString(),
		},
		{
			value: 'path',
			text: this.$t('config.daemon.misc.tracer.form.path').toString(),
		},
		{
			value: 'filename',
			text: this.$t('config.daemon.misc.tracer.form.filename').toString(),
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
	 * Vue lifecycle hook created
	 */
	mounted(): void {
		this.getConfig();
	}

	/**
	 * Retrieves configuration of logging service component
	 */
	private getConfig(): Promise<void> {
		this.loading = true;
		return DaemonConfigurationService.getComponent(this.componentName)
			.then((response: AxiosResponse) => {
				this.instances = response.data.instances;
				this.loading = false;
			})
			.catch(() => {
				this.loading = false;
				this.$toast.error(
					this.$t('config.daemon.messages.configFetchFailed', {children: 'tracer'},)
						.toString()
				);
			});
	}
}
</script>
