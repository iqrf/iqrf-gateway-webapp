<!--
Copyright 2017-2023 IQRF Tech s.r.o.
Copyright 2019-2023 MICRORISC s.r.o.

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
	<v-dialog
		v-model='show'
		width='50%'
		persistent
		no-click-animation
	>
		<v-card>
			<v-card-title>{{ $t('iqrfnet.networkManager.maintenance.rfSignal.modal.title') }}</v-card-title>
			<v-card-text>
				<v-data-table
					:headers='headers'
					:items='results'
				>
					<template #[`item.online`]='{item}'>
						<v-icon :color='item.online ? "success" : "error"'>
							{{ item.online ? 'mdi-check-circle-outline' : 'mdi-close-circle-outline' }}
						</v-icon>
					</template>
					<template #[`item.counter`]='{item}'>
						{{ item.counter ?? 'N/A' }}
					</template>
				</v-data-table>
			</v-card-text>
			<v-card-actions>
				<v-spacer />
				<v-btn
					@click='hideModal'
				>
					{{ $t('forms.close') }}
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>

<script lang='ts'>
import {Component} from 'vue-property-decorator';
import ModalBase from '@/components/ModalBase.vue';

import {DataTableHeader} from 'vuetify';
import {IRfSignalTestResult} from '@/interfaces/DaemonApi/Iqmesh/Maintenance';


/**
 * Maintenance RF Signal Test result component
 */
@Component
export default class RfSignalTestResult extends ModalBase {
	/**
	 * @var {IRfSignalTestResult} results RF Signal Test results
	 */
	private results: Array<IRfSignalTestResult> = [];

	/**
	 * @constant {Array<DataTableHeader>} headers RF Signal Test results table headers
	 */
	private readonly headers: Array<DataTableHeader> = [
		{
			value: 'deviceAddr',
			text: this.$t('iqrfnet.networkManager.maintenance.rfSignal.modal.address').toString(),
			width: '33%',
		},
		{
			value: 'online',
			text: this.$t('iqrfnet.networkManager.maintenance.rfSignal.modal.online').toString(),
			width: '33%',
		},
		{
			value: 'counter',
			text: this.$t('iqrfnet.networkManager.maintenance.rfSignal.modal.counter').toString(),
			width: '33%',
		},
	];

	/**
	 * Stores RF Signal Test results and renders the modal window
	 * @param {IRfSignalTestResult} results RF Signal Test results
	 */
	public showModal(results: Array<IRfSignalTestResult>): void {
		this.results = results;
		this.openModal();
	}

	/**
	 * Clears RF Signal Test results and closes the modal window
	 */
	private hideModal(): void {
		this.closeModal();
		this.results = [];
	}
}
</script>
