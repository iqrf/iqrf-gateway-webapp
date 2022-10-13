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
	<CModal
		:show.sync='show'
		color='primary'
		size='lg'
		:close-on-backdrop='false'
		:fade='false'
	>
		<template #header>
			<h5 class='modal-title'>
				{{ $t('iqrfnet.networkManager.maintenance.rfSignal.modal.title') }}
			</h5>
		</template>
		<CDataTable
			:fields='fields'
			:items='results'
			:column-filter='true'
			:items-per-page='10'
			:pagination='true'
			:sorter='{external: false, resetable: true}'
		>
			<template #online='{item}'>
				<td>
					<CIcon
						size='lg'
						:class='item.online ? "text-success" : "text-danger"'
						:content='item.online ? cilCheckCircle : cilXCircle'
					/>
				</td>
			</template>
			<template #counter='{item}'>
				<td>
					{{ item.counter === undefined ? 'N/A' : item.counter }}
				</td>
			</template>
		</CDataTable>
		<template #footer>
			<CButton
				color='secondary'
				@click='deactivateModal'
			>
				{{ $t('forms.close') }}
			</CButton>
		</template>
	</CModal>
</template>

<script lang='ts'>
import {Component} from 'vue-property-decorator';
import {CButton, CDataTable, CIcon, CModal} from '@coreui/vue/src';
import ModalBase from '@/components/ModalBase.vue';

import {cilCheckCircle, cilXCircle} from '@coreui/icons';

import {IField} from '@/interfaces/coreui';
import {IMaintenanceRfSignalTestResult} from '@/interfaces/iqmeshServices';

@Component({
	components: {
		CButton,
		CDataTable,
		CIcon,
		CModal,
	},
	data: () => ({
		cilCheckCircle,
		cilXCircle,
	})
})

/**
 * Maintenance RF Signal Test result component
 */
export default class RfSignalTestResult extends ModalBase {
	/**
	 * @var {IMaintenanceRfSignalTestResult} results RF Signal Test results
	 */
	private results: Array<IMaintenanceRfSignalTestResult> = [];

	/**
	 * @constant {Array<IField>} fields RF Signal Test results table fields
	 */
	private fields: Array<IField> = [
		{
			label: this.$t('iqrfnet.networkManager.maintenance.rfSignal.modal.address'),
			key: 'deviceAddr',
		},
		{
			label: this.$t('iqrfnet.networkManager.maintenance.rfSignal.modal.online'),
			key: 'online',
		},
		{
			label: this.$t('iqrfnet.networkManager.maintenance.rfSignal.modal.counter'),
			key: 'counter',
		},
	];

	/**
	 * Stores RF Signal Test results and renders the modal window
	 * @param {IMaintenanceRfSignalTestResult} results RF Signal Test results
	 */
	public activateModal(results: Array<IMaintenanceRfSignalTestResult>): void {
		this.results = results;
		this.openModal();
	}

	/**
	 * Clears RF Signal Test results and closes the modal window
	 */
	private deactivateModal(): void {
		this.closeModal();
		this.results = [];
	}
}
</script>
