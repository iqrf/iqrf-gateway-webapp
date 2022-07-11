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
			</v-btn>
			<v-dialog v-model='deleteModal' width='50%'>
				<template #activator='{ on, attrs }'>
					<v-btn
						color='error'
						small
						v-bind='attrs'
						@click='deleteInstance = item.instance'
						v-on='on'
					>
						<v-icon small>
							mdi-delete
						</v-icon>
						{{ $t('table.actions.delete') }}
					</v-btn>
				</template>
				<v-card>
					<v-card-title>
						{{ $t('config.daemon.misc.tracer.modal.title') }}
					</v-card-title>
					<v-card-text>
						{{
							$t('config.daemon.misc.tracer.modal.prompt', {instance: deleteInstance})
						}}
					</v-card-text>
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
							@click='deleteInstance = ""'
						>
							{{ $t('forms.cancel') }}
						</v-btn>
					</v-card-actions>
				</v-card>
			</v-dialog>
		</template>
	</v-data-table>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import DaemonConfigurationService	from '@/services/DaemonConfigurationService';
import {AxiosError, AxiosResponse} from 'axios';
import {extendedErrorToast} from '@/helpers/errorToast';
import {DataTableHeader} from 'vuetify';

@Component({})
/**
 * List of Daemon logging service component instances
 */
export default class TracerList extends Vue {
	/**
	 * @constant {string} componentName Logging service component name
	 */
	private componentName = 'shape::TraceFileService';

	/**
	 * @var {boolean} loading Flag for loading state
   */
	private loading = false;

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
	 * @var {string} deleteInstance Name of logging service component instance used in remove modal
	 */
	private deleteInstance = '';

	/**
	 * @var {boolean} deleteModal Delete modal visibility
	 */
	get deleteModal(): boolean {
		return this.deleteInstance !== '';
	}

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

	/**
	 * Removes instance of logging service component
	 */
	private removeInstance(): void {
		const instance = this.deleteInstance;
		this.deleteInstance = '';
		this.loading = true;
		DaemonConfigurationService.deleteInstance(this.componentName, instance)
			.then(() => {
				this.getConfig().then(() => {
					this.$toast.success(
						this.$t('config.daemon.misc.tracer.messages.deleteSuccess', {instance: instance})
							.toString()
					);
				});
			})
			.catch((error: AxiosError) => extendedErrorToast(
				error,
				'config.daemon.misc.tracer.messages.deleteFailed',
				{instance: instance}
			));
	}
}
</script>
