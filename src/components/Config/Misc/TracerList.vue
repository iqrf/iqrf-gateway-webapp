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
		<CCard class='border-0 card-margin-bottom'>
			<CCardHeader class='border-0'>
				<CButton
					color='success'
					size='sm'
					to='/config/daemon/misc/tracer/add'
					class='float-right'
				>
					<CIcon :content='cilPlus' size='sm' />
					{{ $t('table.actions.add') }}
				</CButton>
			</CCardHeader>
			<CCardBody>
				<CDataTable
					:fields='fields'
					:items='instances'
					:column-filter='true'
					:items-per-page='20'
					:pagination='true'
					:striped='true'
					:sorter='{external: false, resetable: true}'
				>
					<template #no-items-view='{}'>
						{{ $t('table.messages.noRecords') }}
					</template>
					<template #actions='{item}'>
						<td class='col-actions'>
							<CButton
								class='mr-1'
								color='info'
								size='sm'
								:to='"/config/daemon/misc/tracer/edit/" + item.instance'
							>
								<CIcon :content='cilPencil' size='sm' />
								{{ $t('table.actions.edit') }}
							</CButton>
							<TracerDeleteModal
								:instance='item'
								@deleted='getConfig'
							/>
						</td>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CDataTable, CIcon, CModal} from '@coreui/vue/src';
import TracerDeleteModal from './TracerDeleteModal.vue';

import {cilPencil, cilPlus} from '@coreui/icons';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosResponse} from 'axios';
import {IField} from '@/interfaces/coreui';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CDataTable,
		CIcon,
		CModal,
		TracerDeleteModal,
	},
	data: () => ({
		cilPencil,
		cilPlus,
	}),
})

/**
 * List of Daemon logging service component instances
 */
export default class TracerList extends Vue {
	/**
	 * @constant {string} componentName Logging service component name
	 */
	private componentName = 'shape::TraceFileService';

	/**
	 * @constant {Array<IField>} fields Array of CoreUI data table columns
	 */
	private fields: Array<IField> = [
		{
			key: 'instance',
			label: this.$t('forms.fields.instanceName')
		},
		{
			key: 'path',
			label: this.$t('config.daemon.misc.tracer.form.path')
		},
		{
			key: 'filename',
			label: this.$t('config.daemon.misc.tracer.form.filename')
		},
		{
			key: 'actions',
			label: this.$t('table.actions.title'),
			filter: false,
			sorter: false,
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
		return DaemonConfigurationService.getComponent(this.componentName)
			.then((response: AxiosResponse) => {
				this.instances = response.data.instances;
				this.$emit('fetched', {name: 'tracer', success: true});
			})
			.catch(() => {
				this.$emit('fetched', {name: 'tracer', success: false});
			});
	}
}
</script>
