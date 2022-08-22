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
	<v-card flat tile>
		<v-card-text>
			<v-data-table
				:headers='header'
				:items='instances'
				:loading='loading'
			>
				<template #top>
					<v-toolbar dense flat>
						<v-spacer />
						<v-btn
							color='success'
							small
							to='/config/daemon/misc/tracer/add'
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
						small
						:to='"/config/daemon/misc/tracer/edit/" + item.instance'
					>
						<v-icon small>
							mdi-pencil
						</v-icon>
						{{ $t('table.actions.edit') }}
					</v-btn> <TracerDeleteDialog
						:instance='item'
						@deleted='getConfig'
					/>
				</template>
			</v-data-table>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import TracerDeleteDialog from './TracerDeleteDialog.vue';

import DaemonConfigurationService	from '@/services/DaemonConfigurationService';

import {AxiosResponse} from 'axios';
import {DataTableHeader} from 'vuetify';

/**
 * List of Daemon logging service component instances
 */
@Component({
	components: {
		TracerDeleteDialog,
	},
})
export default class TracerList extends Vue {
	/**
	 * @var {boolean} loading Flag for loading state
	 */
	private loading = false;

	/**
	 * @constant {string} componentName Logging service component name
	 */
	private componentName = 'shape::TraceFileService';

	/**
	 * @var {Array<DataTableHeader>} header Data table header
	 */
	private header: Array<DataTableHeader> = [
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
			sortable: false,
			filterable: false,
			align: 'end',
		},
	];

	/**
	 * @var {Array<unknown>} instances Array of logging service component instances
	 */
	private instances: Array<unknown> = [];

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
		this.instances = [];
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
